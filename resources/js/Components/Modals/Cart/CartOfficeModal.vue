<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { useBodyScrollLock } from '../../../Composables/useBodyScrollLock';

const props = defineProps<{
    cart: {
        id: number;
        name: string;
        type: string;
        price: number;
        plan: string;
        months?: number;
        monthly_rate?: number;
        daily_rate?: number;
    }[];
    show: Boolean;
    onClose: () => void;
    can: object;
    routeName: string;
}>();

function proceedToCheckout() {
    router.post(props.routeName, {
        cart: props.cart,
    });
}

function formatCurrency(value: number) {
    return new Intl.NumberFormat('en-ZA', {
        style: 'currency',
        currency: 'ZAR',
    }).format(value);
}

function capitalize(word: string) {
    return word.charAt(0).toUpperCase() + word.slice(1);
}

useBodyScrollLock(() => props.show);
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/70">
        <div class="w-full max-w-4xl bg-white rounded shadow-lg sm:p-6 flex flex-col max-h-[90vh]">
            <!-- Sticky Header -->
            <div class="sticky top-0 z-10 pt-4 pb-2 bg-white sm:pt-6 sm:pb-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl">Shopping Cart</h2>
                    <button
                        @click="onClose"
                        class="text-sm text-black">
                        Close
                    </button>
                </div>
                <hr class="my-2 border-gray-300" />
            </div>

            <!-- Scrollable Cart Items -->
            <div class="flex-1 px-4 overflow-y-auto sm:px-0">
                <div class="divide-y">
                    <div class="mt-4">
                        <!-- Table Header -->
                        <div class="hidden pb-2 text-sm font-semibold border-b sm:grid sm:grid-cols-4 sm:gap-3">
                            <div>Office Name</div>
                            <div>Office Type</div>
                            <div>Plan</div>
                            <div>Total Cost</div>
                        </div>

                        <!-- Table Rows -->
                        <div
                            v-for="item in cart"
                            :key="item.id"
                            class="py-3 text-sm border-b">
                            <!-- Mobile layout -->
                            <div class="flex flex-col gap-1 sm:hidden">
                                <div class="flex justify-between">
                                    <span class="font-semibold">Office Name:</span><span>{{ item.name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-semibold">Office Type:</span><span>{{ item.type }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-semibold">Plan:</span><span>{{ item.plan }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-semibold">Total Cost:</span
                                    ><span>{{ formatCurrency(item.price) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-semibold">Monthly Cost:</span
                                    ><span>{{ formatCurrency(item.monthly_rate) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-semibold">Payable:</span
                                    ><span>{{ formatCurrency(item.price) }}</span>
                                </div>
                            </div>

                            <!-- Desktop layout -->
                            <div class="hidden sm:grid sm:grid-cols-4">
                                <div class="pr-2 font-medium">{{ item.name }}</div>
                                <div class="pr-2 text-gray-500">{{ item.type }}</div>
                                <div class="pl-3">{{ capitalize(item.plan) }}</div>
                                <div class="pl-3">{{ formatCurrency(item.price) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Actions -->
            <div class="sticky bottom-0 z-10 px-4 pt-4 pb-6 bg-white sm:px-0">
                <hr class="mb-4 border-gray-300" />
                <div class="flex flex-col gap-3 sm:flex-row sm:justify-between">
                    <button
                        class="w-full px-4 py-2 text-white rounded sm:w-auto bg-primary hover:bg-bluemain/80"
                        @click="proceedToCheckout">
                        Proceed to Checkout
                    </button>
                    <button
                        class="w-full px-4 py-2 bg-gray-200 rounded sm:w-auto hover:bg-gray-300"
                        @click="onClose">
                        Continue Shopping
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
