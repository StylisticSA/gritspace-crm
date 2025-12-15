<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Booking;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\HotDeskBooking;
use App\Models\PaymentGateway;
use App\Models\VirtualBooking;
use App\Models\BoardroomBooking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PaymentGatewayController extends Controller
{
    public function initiateTransaction(Request $request)
    {

        $reference   = str_pad((string) now()->timestamp, 12, '0', STR_PAD_RIGHT);

        $referenceEx = substr((string) round(microtime(true) * 1000), 0, 13);

        $sessionId   = strtolower((string) Str::uuid());

        // Transaction details
        $transactionAmount = (int) round($request->total * 100);
        $merchantId        = config('services.travelbuy.merchant_id');
        $privateKey        = config('services.travelbuy.private_key');
        $currencyCode      = '710'; // ZAR

        // Generate Assurance Data
        $assuranceData = $this->generateAssuranceData([
            'Merchant ID'                         => $merchantId,
            'Transaction Amount'                  => $transactionAmount,
            'Currency Code'                       => $currencyCode,
            'Retrieval Reference Number'          => $reference,
            'Retrieval Reference Number Extended' => $referenceEx,
            'Session ID'                          => $sessionId,
            "Merchant's Private Key"              => $privateKey,
        ]);

        $cart = $request->cart;

        foreach ($cart as $item) {
            PaymentGateway::updateOrCreate(
                [
                    'payable_type' => $this->mapType($item['type']),
                    'payable_id'   => $item['id'],
                    'plan'         => $item['plan'] ?? null,
                    'status'       => 'pending',
                ],
                [
                    'amount'         => $item['price'],
                    'currency'       => 'ZAR',
                    'assurance_data' => json_encode([
                        'sessionId'   => $sessionId,
                        'reference'   => $reference,
                        'referenceEx' => $referenceEx,
                    ]),
                ]
            );
        }

        $method = $request->input('method', '3d');

      

        // Build request payload
        $payload = [
            'assuranceData'                    => $assuranceData,
            'currencyCode'                     => $currencyCode,
            'merchantId'                       => $merchantId,
            'retrievalReferenceNumber'         => $reference,
            'retrievalReferenceNumberExtended' => $referenceEx,
            'sessionId'                        => $sessionId,
            'transactionAmount'                => $transactionAmount,
            'echoData'                         => '123',
            'transmissionDateTime'             => now()->utc()->format('Y-m-d\TH:i:sO'),

            'peripheryData' => [
                'callbackUrl'    => route('payment.callback'),

            ],

        ];

        // Add transactionInfo explicitly
        if ($method === '3d') {
            $payload['transactionInfo'] = [
                'transactionCategory' => '95',
            ];
        } elseif ($method === 'amex') {
            $payload['transactionInfo'] = [
                'transactionCategory' => '94',
            ];
        }

        // dd($payload);

        $endpoint = rtrim(config('services.travelbuy.base_url'), '/') . '/initiateimmediatepayment';
        $response = Http::post($endpoint, $payload);

        \Log::info('Traderoot response', [
            'status' => $response->status(),
            // 'body'   => $response->body(),
            // 'json'   => $response->json(),
        ]);



        if ($response->successful() && data_get($response->json(), 'responseCode') === '00') {
            $initiationUrl = data_get($response->json(), 'peripheryData.initiationUrl');

            return Inertia::render('Checkout/RedirectToTraderoot', [
                'initiationUrl' => $initiationUrl,
            ]);
        }

        return back()->with('error', data_get($response->json(), 'responseMessage', 'Payment initiation failed.'));


    }

    private function generateAssuranceData(array $params)
    {
        $digitalWalletId = (string) Str::uuid();

        $merchantId   = (string) $params['Merchant ID'];
        $amount       = (string) $params['Transaction Amount'];
        $currencyCode = (string) $params['Currency Code'];
        $rrn          = (string) $params['Retrieval Reference Number'];
        $rrnExt       = (string) $params['Retrieval Reference Number Extended'];
        $sessionId    = strtolower((string) $params['Session ID']);
        $privateKey   = (string) $params["Merchant's Private Key"]; // UUID WITH dashes

        // Build signature string in exact order, no delimiters; include rrnExt because you send it
        $concat = strtolower(
            $merchantId .
            $amount .
            $currencyCode .
            $rrn .
            $rrnExt .
            $sessionId .
            $privateKey
        );

        // \Log::info('AssuranceData concat string', ['concat' => $concat]);


        $signature = strtoupper(hash('sha512', $concat));

        // \Log::info('AssuranceData signature (hex)', ['hex' => $signature]);

        $payload = json_encode([
            'timestamp'       => (int) round(microtime(true) * 1000),
            'digitalWalletId' => $digitalWalletId,
            'signature'       => $signature,
            'sessionId'       => $sessionId,
        ], JSON_UNESCAPED_SLASHES);

        \Log::info('AssuranceData payload before encryption', ['payload' => $payload]);

        // For encryption, remove dashes to get a 256-bit key
        $keyStr = str_replace('-', '', $privateKey);
        if (strlen($keyStr) !== 32) {
            throw new \RuntimeException('Merchant private key must be a UUID; after removing dashes it must be 32 characters.');
        }

        $iv = random_bytes(16);

        $encrypted = openssl_encrypt(
            $payload,
            'AES-256-CBC',
            $keyStr,
            OPENSSL_RAW_DATA,
            $iv
        );

        return base64_encode($iv . $encrypted);
    }

    public function paymentCallback(Request $request)
    {
        \Log::info('Payment callback raw', [
            'body'  => $request->getContent(),
            'query' => $request->all(),
        ]);

        $decoded = json_decode(base64_decode($request->input('data', '')), true)
                 ?: $request->json()->all()
                 ?: $request->all();

        $responseCode    = data_get($decoded, 'responseCode');
        $responseMessage = data_get($decoded, 'responseMessage');
        $transactionId   = data_get($decoded, 'transactionId');
        $sessionId       = data_get($decoded, 'sessionId');

        if ($responseCode === '00') {
            // Get all payments tied to this session
            $payments = PaymentGateway::whereJsonContains('assurance_data->sessionId', $sessionId)->get();


            foreach ($payments as $payment) {
                // Finalize payment row
                $payment->update([
                    'status'         => 'success',
                    'transaction_id' => $transactionId,
                    'assurance_data' => json_encode($decoded),
                ]);

                // Decide which booking table to update
                if (in_array($payment->plan, ['monthly', 'daily', 'standard', 'premium'])) {
                    // Office bookings
                    DB::table('office_bookings')
                        ->where('office_id', $payment->payable_id)
                        ->where('user_id', auth()->id())
                        ->where('plan', $payment->plan)
                        ->where('status', 'approved')
                        ->update(['status' => 'paid']);
                } elseif (in_array($payment->plan, ['Half Day', '1 Day', '5 Days', '10 Days', '20 Days'])) {
                    // Hotdesk bookings
                    DB::table('hot_desk_bookings')
                        ->where('helpdesk_id', $payment->payable_id)
                        ->where('user_id', auth()->id())
                        ->where('plan', $payment->plan)
                        ->where('status', 'approved')
                        ->update(['status' => 'paid']);
                } elseif (in_array($payment->plan, ['hourly', 'daily'])) {
                    // Boardroom bookings
                    DB::table('boardroom_bookings')
                        ->where('boardroom_id', $payment->payable_id)
                        ->where('user_id', auth()->id())
                        ->where('plan', $payment->plan)
                        ->where('status', 'approved')
                        ->update(['status' => 'paid']);
                } elseif ($payment->plan === 'monthly' && $payment->payable_type === 'virtual') {
                    // Virtual office bookings
                    DB::table('virtual_office_bookings')
                        ->where('virtual_office_id', $payment->payable_id)
                        ->where('user_id', auth()->id())
                        ->where('plan', $payment->plan)
                        ->where('status', 'approved')
                        ->update(['status' => 'paid']);
                }
            }


            return Inertia::render('Checkout/Success', [
                'message'       => $responseMessage,
                'transactionId' => $transactionId,
                'sessionId'     => $sessionId,
            ]);
        }

        return Inertia::render('Checkout/Failed', [
            'error'         => $responseMessage ?? 'Payment failed',
            'transactionId' => $transactionId,
            'sessionId'     => $sessionId,
        ]);
    }

    /**
     * Map cart item type to model class for polymorphic relation
     */
    private function mapType(string $type): string
    {
        return match ($type) {
            'Closed Office', 'Dedicated Desk' => Booking::class,
            'Hot Desk'                        => HotDeskBooking::class,
            'Boardroom'                       => BoardroomBooking::class,
            'Virtual'                         => VirtualBooking::class,
            default                           => '',
        };
    }

    public function receiveCart(Request $request)
    {
        if ($request->isMethod('post')) {
            // Save cart to session on first submit
            session(['cart' => $request->input('cart', [])]);
        }

        $cart = session('cart', []);

        return Inertia::render('Cart/OfficesCart', [
            'cart' => $cart,
        ]);
    }



}
