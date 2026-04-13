<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import BookingHotDesk from '@/Components/BookingHotDesk.vue';
import { ref } from 'vue';

interface Location {
    name: string;
}

interface Amenity {
    id: number;
    amenity_name: string;
}

interface HelpDesk {
    id?: number;
    help_desk_name?: string;
    location?: Location;
    price?: number;
    duration?: number | string;
    discount?: number;
    desks?: number;
    amenities?: Amenity[];
}

interface Discount {
    package: string;
    discount: number;
}

const props = defineProps<{
    helpDesks: HelpDesk;
    locations: Location;
    discount: Discount;
}>();

const viewMode = ref<'form' | 'calendar' | null>(null);

const pricingOptions = {
    price: props.helpDesks.price,
};
</script>

<template>
    <Head title="View Hot Desk" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">View Hot Desk</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-4 lg:px-8">
                <div class="flex justify-center">
                    <div class="w-full max-w-2xl p-6 space-y-6 bg-white border rounded-lg shadow-md">
                        <!-- Header -->
                        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                            <h3 class="text-xl font-semibold">{{ helpDesks.help_desk_name || 'Hot Desk' }}</h3>
                            <Link
                                :href="route('booking.offices')"
                                class="inline-block w-full px-4 py-2 text-sm font-medium text-center text-white rounded md:w-auto bg-primary hover:bg-bluemain">
                                Back
                            </Link>
                        </div>

                        <!-- Details -->
                        <div class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 text-sm text-gray-700 md:grid-cols-2">
                                <div class="space-y-2">
                                    <p><strong>Location:</strong> {{ helpDesks.location?.name || 'N/A' }}</p>
                                    <p>
                                        <strong>Duration:</strong>
                                        {{
                                            helpDesks.duration === '0.5'
                                                ? 'Half-Day'
                                                : helpDesks.duration
                                                  ? helpDesks.duration + ' Days'
                                                  : 'N/A'
                                        }}
                                    </p>
                                </div>

                                <div v-if="helpDesks.amenities?.length">
                                    <p><strong>Amenities:</strong></p>
                                    <div class="flex flex-wrap gap-2 mt-1">
                                        <span
                                            v-for="(a, index) in helpDesks.amenities"
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
                            </div>
                            <div class="pt-4 border-t border-gray-200">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                                    <!-- First column: Pricing Options -->
                                    <div class="md:col-span-1">
                                        <h4 class="font-semibold text-gray-800 mb-2">Pricing Options</h4>
                                        <ul class="space-y-1 text-sm text-gray-700">
                                            <li v-if="helpDesks.price">
                                                Price: <strong>R{{ helpDesks.price ?? '0.00' }}</strong>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Second column: Discounts -->
                                    <div class="md:col-span-2">
                                        <h4 class="font-semibold text-gray-800 mb-2">Discounts</h4>
                                        <ul class="space-y-1 sm:pl-4 text-sm text-gray-700 lg:list-disc">
                                            <li v-if="props.discount && props.discount?.discount">
                                                It has <strong>{{ props.discount?.discount }}%</strong> discount on
                                                boardrooms.
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Form -->
                        <div class="pt-6 mt-6 border-t border-gray-200">
                            <BookingHotDesk
                                :buttonName="helpDesks.help_desk_name"
                                :hotdesk-id="helpDesks.id"
                                :pricing-options="helpDesks.price"
                                :available-plans="helpDesks.help_desk_name"
                                :selected-duration="Number(helpDesks.duration)" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
