<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Office;
use App\Models\Invoice;
use App\Models\HelpDesk;
use App\Models\Boardroom;
use Illuminate\Http\Request;
use App\Models\VirtualOffice;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {   
        $invoices = Invoice::with('invoiceItems')->get();

        

        return Inertia::render('Invoice/Admins/IndexInvoice',[
            'invoices' => $invoices
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function user_index()
    {
        

        return Inertia::render('Invoice/User/IndexInvoice');
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::has('companyDetails')->with('companyDetails')->get();

        $virtuals = VirtualOffice::with('location')
            ->select('id', 'location_id','virtualoffice_name as name')
            ->get()
            ->map(fn($v) => [
                'id' => $v->id,
                'name' => $v->name,
                'location' => optional($v->location)->name ?? 'N/A',
                'type' => 'Virtual Office',
            ]);

        $hotdesk = HelpDesk::with('location')
            ->select('id','location_id', 'help_desk_name as name')
            ->get()
            ->map(fn($h) => [
                'id' => $h->id,
                'name' => $h->name,
                'location' => optional($h->location)->name ?? 'N/A',
                'type' => 'Help Desk',
            ]);

         
        $boardrooms = Boardroom::with('location')
            ->select('id', 'location_id', 'boardroom_name')
            ->get()
            ->map(fn($b) => [
                'id' => $b->id,
                'name' => $b->boardroom_name,
                'location' => optional($b->location)->name ?? 'N/A',
                'type' => 'Boardroom',
            ]);
    
            
        $closed = Office::with(['location', 'category'])
            ->whereHas('category', fn($q) =>
                $q->whereIn(DB::raw('LOWER(name)'), ['closed office','closed offices'])
            )
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->office_name,
                'location' => optional($c->location)->name ?? 'N/A',
                'type' => 'Closed Office',
            ]);

        $dedicated = Office::with(['location', 'category'])
            ->whereHas('category', fn($q) =>
                $q->whereIn(DB::raw('LOWER(name)'), ['dedicated desk','dedicated desks'])
            )
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->office_name,
                'location' => optional($d->location)->name ?? 'N/A',
                'type' => 'Dedicated Desk',
            ]);

        $allOptions = $virtuals
            ->concat($hotdesk)
            ->concat($boardrooms)
            ->concat($closed)
            ->concat($dedicated);

            // dd($allOptions);
        
        return Inertia::render('Invoice/Admins/CreateInvoice',[
            'users' => $users,
            'allOptions' => $allOptions
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request);

        $validated = $request->validate([
            'user_id'           => 'required',
            'user_name'        => 'required|string',
            'customer_name'    => 'required|string',
            'customer_email'   => 'required|email',
            'customer_phone'   => 'required|string',
            'customer_address' => 'required|string',
            'customer_city'    => 'nullable|string',

            'issued_date'      => 'required|date',
            'issued_due_date'  => 'required|date|after_or_equal:issued_date',

            'invoice_notes'    => 'nullable|string',

            'subtotal'         => 'required|numeric|min:0',
            'tax_amount'       => 'required|numeric|min:0',
            'total_amount'     => 'required|numeric|min:0',

            'currency'         => 'required|string|in:USD,ZAR,EUR,GBP',

            'items'                 => 'required|array|min:1',
            'items.*.item_name'     => 'required|string|max:255',
            'items.*.item_quantity' => 'required|numeric|min:1',
            'items.*.item_rate'     => 'required|numeric|min:0',
            'items.*.item_amount'   => 'required|numeric|min:0',

        ]);

        // Find the last invoice
        $lastInvoice = Invoice::orderBy('id', 'desc')->first();

        $nextNumber = $lastInvoice
            ? intval(substr($lastInvoice->invoice_number, 5)) + 1
            : 1;

        $invoiceNumber = 'GRIT-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        $invoiceData = collect($validated)->except('items')->toArray();

        $invoiceData['invoice_number'] = $invoiceNumber;

        $invoice = Invoice::create($invoiceData);

        foreach ($validated['items'] as $item) {
            $invoice->invoiceItems()->create([
                'item_name'     => $item['item_name'],
                'item_quantity' => $item['item_quantity'],
                'item_rate'     => $item['item_rate'],
                'item_amount'   => $item['item_amount'],
            ]);
        }

        return redirect()->back()->with('success', 'Invoice Created successfully.');


    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function user_show(Invoice $invoice)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        //
    }
}
