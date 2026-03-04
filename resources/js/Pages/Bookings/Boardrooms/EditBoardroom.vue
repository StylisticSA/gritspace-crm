<script setup lang="ts">
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import BookingBoardroom from '@/Components/BookingBoardroom.vue';

interface Location {
    id: number;
    name: string;
    address?: string;
    city?: string;
}

interface Amenity {
    id: number;
    amenity_name: string;
}

interface Boardroom {
    id: number;
    boardroom_name: string;
    location?: Location;
    seats?: number;
    hourly_price?: number;
    daily_price?: number;
    amenities?: Amenity[];
}

interface discount {
    name: string;
    discount: number;
}

const props = defineProps<{
    boardroom: Boardroom;
    locations: Location[];
    amenities: Amenity[];
    closedDiscount: discount;
    dedicatedDiscount: discount;
    hotdeskDiscount: discount;
    virtualDiscount: discount;
}>();

const pricingOptions = {
    hourly: props.boardroom.hourly_price,
    daily: props.boardroom.daily_price,
};

const availablePlans = Object.keys(pricingOptions).filter(key => pricingOptions[key] != null);

const selectedPlan = ref<string | null>(null);
</script>

<template>
    <Head title="View Boardroom" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">View Boardroom</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-4 lg:px-8">
                <div class="flex justify-center">
                    <div class="w-full max-w-2xl p-6 space-y-6 bg-white border rounded-lg shadow-md">
                        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                            <h3 class="text-xl font-semibold">{{ boardroom.boardroom_name || 'Boardroom' }}</h3>
                            <Link
                                :href="route('booking.boardrooms')"
                                class="inline-block w-full px-4 py-2 text-sm font-medium text-center text-white rounded bg-primary md:w-auto hover:bg-bluemain">
                                Back
                            </Link>
                        </div>

                        <!-- Details -->
                        <div class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 text-sm text-gray-700 md:grid-cols-2">
                                <div class="space-y-2">
                                    <p><strong>Location:</strong> {{ boardroom.location?.name || 'N/A' }}</p>
                                    <p><strong>Seats:</strong> {{ boardroom.seats ?? 'N/A' }}</p>
                                </div>

                                <div class="md:col-span-1">
                                    <h4 class="font-semibold text-gray-800 mb-2">Pricing Options</h4>
                                    <ul class="space-y-1 text-sm text-gray-700">
                                        <li>
                                            Hourly Rate: <strong>R{{ boardroom.hourly_price ?? '0.00' }}</strong>
                                        </li>
                                        <li>
                                            Full Day Rate: <strong>R{{ boardroom.daily_price ?? '0.00' }}</strong>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-gray-200">
                                <div class="grid grid-cols-1 md:grid-cols-1 gap-4 mt-2">
                                    <div class="md:col-span-2">
                                        <h4 class="font-semibold text-gray-800 mb-2">Discounts</h4>
                                        <ul class="space-y-1 sm:pl-4 text-sm text-gray-700 lg:list-disc">
                                            <li>
                                                Includes 15 free boardroom hours per month (For
                                                <strong>Closed Office</strong> monthly bookings)
                                            </li>
                                            <li v-if="props.closedDiscount && props.closedDiscount?.discount">
                                                You Booked Closed Office, It has
                                                <strong>{{ props.closedDiscount?.discount }}%</strong> discount on
                                                boardrooms.
                                            </li>
                                            <li v-if="props.hotdeskDiscount && props.hotdeskDiscount?.discount">
                                                You Booked Hot Desk Office, It has
                                                <strong>{{ props.hotdeskDiscount?.discount }}%</strong> discount on
                                                boardrooms.
                                            </li>
                                            <li v-if="props.virtualDiscount && props.virtualDiscount?.discount">
                                                You Booked a Virtual Office, It has
                                                <strong>{{ props.virtualDiscount?.discount }}%</strong> discount on
                                                boardrooms.
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6 pt-4 border-t border-gray-200">
                            <div class="grid grid-cols-1 gap-6 text-sm text-gray-700 md:grid-cols-2">
                                <div v-if="boardroom.amenities?.length">
                                    <h4 class="font-semibold text-gray-800 mb-2">Amenities</h4>

                                    <div class="flex flex-wrap gap-2 mt-1">
                                        <span
                                            v-for="(a, index) in boardroom.amenities"
                                            :key="a.id"
                                            :style="{
                                                backgroundColor: [
                                                    '#b99456',
                                                    '#8a920e',
                                                    '#8895a6',
                                                    '#5c732b',
                                                    '#323c44',
                                                    '#c56641',
                                                ][index % 6],
                                            }"
                                            class="px-2 py-1 text-xs text-white rounded">
                                            {{ a.amenity_name }}
                                        </span>
                                    </div>
                                </div>
                                <div v-else>
                                    <p class="text-primary">It has no amenities</p>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Form -->
                        <div class="pt-6 mt-6 border-t border-gray-200">
                            <BookingBoardroom
                                :buttonName="boardroom.boardroom_name"
                                :boardroom-id="boardroom.id"
                                :discount-closed="props.closedDiscount?.discount"
                                :pricing-options="pricingOptions"
                                :available-plans="availablePlans"
                                :selected-plan="selectedPlan" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
