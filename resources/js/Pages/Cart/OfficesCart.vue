<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    cart: { type: Array, default: () => [] },
});
console.log('c', props.cart);
const vatRate = ref(0);
const selectedMethod = ref('3d');

// Subtotal logic
const pricePlans = ['daily', 'hourly', 'standard', 'premium', 'Half Day', '1 Day', '5 Days', '10 Days', '20 Days'];

const subtotal = computed(() =>
    props.cart.reduce((sum, item) => {
        if (item.plan === 'monthly') {
            return sum + (Number(item.monthly_rate) || 0);
        }
        if (pricePlans.includes(item.plan.toLowerCase())) {
            return sum + (Number(item.price) || 0);
        }
        return sum;
    }, 0)
);

const tax = computed(() => (subtotal.value * vatRate.value) / 100);
const totalWithVat = computed(() => subtotal.value + tax.value);

function proceedToCheckout() {
    router.post(route('payment.initiate'), {
        total: totalWithVat.value,
        cart: props.cart,
        method: selectedMethod.value,
    });
}

function continueShopping() {
    if (!props.cart.length) {
        router.visit(route('booking.offices'));
        return;
    }

    const firstType = props.cart[0].type.toLowerCase();

    switch (firstType) {
        case 'boardrooms':
            router.visit(route('booking.boardrooms'));
            break;
        case 'Virtuals':
            router.visit(route('virtual.home'));
            break;
        default:
            router.visit(route('booking.offices'));
            break;
    }
}

function formatCurrency(value) {
    return new Intl.NumberFormat('en-ZA', {
        style: 'currency',
        currency: 'ZAR',
    }).format(value);
}

function capitalize(word) {
    return word.charAt(0).toUpperCase() + word.slice(1);
}
</script>

<template>
    <Head title="Offices Cart" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Offices Cart</h2>
        </template>

        <div class="py-12">
            <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="p-4 rounded sm:p-6">
                    <!-- Two columns: left wider, right narrower -->
                    <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                        <!-- Left: Details (wider) -->
                        <div class="p-5 bg-white lg:col-span-8">
                            <div class="overflow-x-auto">
                                <div class="divide-y">
                                    <div>
                                        <div
                                            class="rounded-md bg-primary/60"
                                            v-if="props.cart[0]?.plan === 'monthly'">
                                            <p class="p-3 text-center text-black text-md">
                                                Please Note, you are paying for the first month cost on Monthly Plan...
                                            </p>
                                        </div>
                                        <div
                                            class="hidden py-5 font-semibold border-b text-md sm:grid sm:grid-cols-5 sm:gap-3">
                                            <div>Office Name</div>
                                            <div>Office Type</div>
                                            <div>Plan</div>
                                            <div>Total Cost</div>
                                            <div>Monthly/Daily</div>
                                        </div>

                                        <div
                                            v-for="item in cart"
                                            :key="item.id"
                                            class="py-4 text-sm border-b">
                                            <!-- Mobile -->
                                            <div class="flex flex-col gap-2 sm:hidden">
                                                <div class="flex justify-between">
                                                    <span class="font-semibold">Office Name:</span>
                                                    <span>{{ item.name }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="font-semibold">Office Type:</span>
                                                    <span>{{ item.type }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="font-semibold">Plan:</span>
                                                    <span>{{
                                                        capitalize(
                                                            pricePlans.find(
                                                                p => p.toLowerCase() === item.plan.toLowerCase()
                                                            ) || item.plan
                                                        )
                                                    }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="font-semibold">Total Cost:</span>
                                                    <span>{{ formatCurrency(item.price) }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="font-semibold">Monthly Cost:</span>
                                                    <span>
                                                        {{
                                                            item.plan === 'monthly'
                                                                ? formatCurrency(item.monthly_rate ?? 0)
                                                                : formatCurrency(item.price ?? 0)
                                                        }}</span
                                                    >
                                                </div>
                                            </div>

                                            <!-- Desktop -->
                                            <div class="hidden sm:grid sm:grid-cols-5 sm:gap-3">
                                                <div class="pr-2 font-medium">{{ item.name }}</div>
                                                <div class="pr-2 text-gray-500">{{ item.type }}</div>

                                                <div class="pl-1">
                                                    {{
                                                        capitalize(
                                                            pricePlans.find(
                                                                p => p.toLowerCase() === item.plan.toLowerCase()
                                                            ) || item.plan
                                                        )
                                                    }}
                                                </div>

                                                <div class="pl-1">{{ formatCurrency(item.price) }}</div>
                                                <div class="pl-2">
                                                    {{
                                                        item.plan === 'monthly'
                                                            ? formatCurrency(item.monthly_rate ?? 0)
                                                            : formatCurrency(item.price ?? 0)
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Summary (narrower) -->
                        <div class="lg:col-span-4">
                            <div class="p-4 rounded shadow bg-gray-50">
                                <h3 class="mb-3 text-lg font-semibold">Summary</h3>
                                <div class="flex justify-between mb-2 text-sm">
                                    <span>Subtotal</span>
                                    <span>{{ formatCurrency(subtotal) }}</span>
                                </div>
                                <div class="flex justify-between mb-2 text-sm">
                                    <span>VAT </span>
                                    <!--({{ vatRate }}%)-->
                                    <!-- <span>R{{ tax }}</span> -->
                                    <span>Already Included</span>
                                </div>
                                <div class="flex justify-between pt-2 text-base font-bold border-t">
                                    <span>Total Payable</span>
                                    <span>{{ formatCurrency(totalWithVat) }}</span>
                                </div>

                                <!-- Payment method choice -->
                                <div class="mt-4">
                                    <label class="block mb-2 font-bold text-md">Choose Payment Method:</label>
                                    <div class="flex flex-col gap-2">
                                        <label>
                                            <input
                                                type="radio"
                                                value="3d"
                                                class="mr-3 text-primary border-primary checked:border-primary checked:bg-primary focus:ring-primary"
                                                v-model="selectedMethod" />
                                            3‑D Secure (Visa/Mastercard)
                                        </label>
                                        <label>
                                            <input
                                                type="radio"
                                                value="amex"
                                                class="mr-3 text-primary checked:border-primary checked:bg-primary focus:ring-primary"
                                                v-model="selectedMethod" />
                                            Amex (Non 3-D)
                                        </label>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-3 mt-6">
                                    <button
                                        class="w-full px-4 py-2 text-white rounded bg-primary hover:bg-bluemain/80"
                                        @click="proceedToCheckout">
                                        Pay
                                    </button>
                                    <button
                                        class="w-full px-4 py-2 bg-gray-200 rounded hover:bg-gray-300"
                                        @click="continueShopping">
                                        Continue Shopping
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
