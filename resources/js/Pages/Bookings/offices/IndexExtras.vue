<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { reactive, computed } from 'vue';

interface Amenity {
    id: number;
    amenity_name: string;
    price: number | string;
}

const props = defineProps<{
    amenities: Amenity[];
}>();

// Reactive quantity tracker
const quantities = reactive<Record<number, number>>({});

props.amenities.forEach(amenity => {
    quantities[amenity.id] = 0;
});

function increment(id: number) {
    quantities[id]++;
}

function decrement(id: number) {
    if (quantities[id] > 0) {
        quantities[id]--;
    }
}

function getLineTotal(id: number, price: number | string): number {
    return quantities[id] * Number(price);
}

const totalCost = computed(() => {
    return props.amenities
        .reduce((sum, amenity) => {
            return sum + getLineTotal(amenity.id, amenity.price);
        }, 0)
        .toFixed(2);
});

const totalItems = computed(() => {
    return props.amenities.reduce((sum, amenity) => {
        return sum + quantities[amenity.id];
    }, 0);
});

function payAll() {
    alert(`Paying R${totalCost.value} for ${totalItems.value} item(s)`);
}
</script>

<template>
    <Head title="Extras Admin" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Extras</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="px-6 py-10 mx-auto max-w-7xl">
                    <div class="w-full mx-auto space-y-4 md:w-2/3">
                        <!-- Table Header (visible on md and up) -->
                        <div class="justify-between hidden pb-2 font-semibold text-gray-700 border-b md:flex">
                            <span class="w-1/4">Amenity</span>
                            <span class="w-1/6 text-center">Unit Price</span>
                            <span class="w-1/3 text-center">Quantity</span>
                            <span class="w-1/6 text-right">Total</span>
                        </div>

                        <!-- Amenity Rows -->
                        <div
                            v-for="amenity in amenities"
                            :key="amenity.id"
                            class="flex flex-col justify-between w-full p-4 space-y-2 border rounded-md md:flex-row md:items-center md:space-y-0 md:space-x-2">
                            <!-- Amenity Name -->
                            <div class="font-semibold md:w-1/4">
                                <span class="block text-sm text-gray-500 md:hidden">Amenity</span>
                                {{ amenity.amenity_name }}
                            </div>

                            <!-- Unit Price -->
                            <div class="text-left md:w-1/6 md:text-center">
                                <span class="block text-sm text-gray-500 md:hidden">Unit Price</span>
                                R{{ Number(amenity.price).toFixed(2) }}
                            </div>

                            <!-- Quantity Controls -->
                            <div
                                class="flex items-center justify-between px-4 py-2 text-sm text-gray-800 border border-gray-300 rounded md:w-1/3 hover:bg-gray-100">
                                <span
                                    @click.stop="decrement(amenity.id)"
                                    class="text-lg font-bold cursor-pointer"
                                    >−</span
                                >

                                <span>Qty: {{ quantities[amenity.id] }}</span>

                                <span
                                    @click.stop="increment(amenity.id)"
                                    class="text-lg font-bold cursor-pointer"
                                    >+</span
                                >
                            </div>

                            <!-- Line Total -->
                            <div class="text-right md:w-1/6">
                                <span class="block text-sm text-gray-500 md:hidden">Total</span>
                                R{{ getLineTotal(amenity.id, amenity.price).toFixed(2) }}
                            </div>
                        </div>

                        <!-- Totals Section -->
                        <div class="flex justify-between pt-4 mt-6 text-lg font-semibold border-t">
                            <span>Total Cost:</span>
                            <span>R{{ totalCost }}</span>
                        </div>

                        <!-- Pay Button -->
                        <div class="flex justify-end">
                            <button
                                @click="payAll"
                                :disabled="totalItems === 0"
                                class="px-6 py-2 mt-4 text-sm font-semibold text-white bg-green-600 rounded hover:bg-green-700 disabled:opacity-50">
                                Pay for {{ totalItems }} item<span v-if="totalItems !== 1">s</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
