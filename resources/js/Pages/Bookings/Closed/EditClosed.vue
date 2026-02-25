<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import BookingClosed from '@/Components/BookingClosed.vue';

interface Location {
    name: string;
}

interface Amenity {
    id: number;
    amenity_name: string;
}

interface Category {
    id: number;
    name: string;
}

interface Parking {
    id: number;
    name: string;
    price: number;
    code: string;
}

interface Discount {
    name: string;
    discount: number;
}

interface Office {
    id?: number;
    office_name?: string;
    location?: Location;
    category_id?: number;
    seats?: number;
    monthly_rate?: number;
    daily_rate?: number;
    price_premium?: number;
    price_standard?: number;
    free_boardroom_hours?: number;
    amenities?: Amenity[];
}

const props = defineProps<{
    office: Office;
    categories: Category[];
    bookedDates: string[];
    parking: Parking;
    discount: Discount;
}>();

console.log('d', props.discount);

const { props: pageProps } = usePage();
const category = props.categories.find(cat => cat.id === props.office.category_id);
const categoryName = category?.name ?? '';

const viewMode = ref<'form' | 'calendar' | null>(null);

const normalizedCategory = categoryName?.toLowerCase().trim();

const pricingOptions = {
    ...(normalizedCategory === 'closed office' || normalizedCategory === 'closed offices'
        ? {
              monthly: props.office.monthly_rate,
              daily: props.office.daily_rate,
          }
        : {}),
};

const availablePlans = Object.keys(pricingOptions).filter(k => pricingOptions[k] != null);

// Flash success notification
const flash = (pageProps.flash ?? {}) as { success?: string };
const flashMessage = flash.success ?? null;

const showSuccess = ref(!!flashMessage);

if (showSuccess.value) {
    setTimeout(() => {
        showSuccess.value = false;
    }, 1500);
}

if (flashMessage) {
    viewMode.value = 'form';
}
</script>

<template>
    <Head title="Booking/Enquiry" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Booking/Enquiry</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-4 lg:px-8">
                <div class="flex justify-center">
                    <div class="w-full max-w-2xl p-6 space-y-6 bg-white border rounded-lg shadow-md">
                        <!-- Header -->
                        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                            <h3 class="text-xl font-semibold">{{ office.office_name || 'Office' }}</h3>
                            <Link
                                :href="route('booking.offices')"
                                class="inline-block w-full px-4 py-2 text-sm font-medium text-center text-white rounded md:w-auto bg-primary hover:bg-bluemain">
                                Back
                            </Link>
                        </div>

                        <!-- Info -->
                        <div class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <p><strong>Location:</strong> {{ office.location?.name || 'N/A' }}</p>

                                    <p><strong>Category:</strong> {{ categoryName || 'N/A' }}</p>
                                </div>

                                <div v-if="office.amenities?.length">
                                    <p><strong>Amenities:</strong></p>
                                    <div class="flex flex-wrap gap-2 mt-1">
                                        <span
                                            v-for="(a, index) in office.amenities"
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

                            <!-- Pricing Summary -->
                            <div class="pt-4 border-t border-gray-200">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                                    <!-- First column: Pricing Options -->
                                    <div class="md:col-span-1">
                                        <h4 class="font-semibold text-gray-800 mb-2">Pricing Options</h4>
                                        <ul class="space-y-1 text-sm text-gray-700">
                                            <li v-if="office.monthly_rate">
                                                Monthly: <strong>R{{ office.monthly_rate }}</strong>
                                            </li>
                                            <li v-if="office.daily_rate">
                                                Daily: <strong>R{{ office.daily_rate }}</strong>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Second column: Discounts -->
                                    <div class="md:col-span-2">
                                        <h4 class="font-semibold text-gray-800 mb-2">Discounts</h4>
                                        <ul class="space-y-1 sm:pl-4 text-sm text-gray-700 lg:list-disc">
                                            <li v-if="office.free_boardroom_hours">
                                                Includes <strong>{{ office.free_boardroom_hours }}</strong> free
                                                boardroom hours per month (For monthly bookings, not daily.)
                                            </li>
                                            <li v-if="props.discount && props.discount.discount">
                                                It has <strong>{{ props.discount.discount }}%</strong> discount on
                                                boardrooms, after free has expired.
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Form -->
                        <div class="pt-6 mt-6 border-t border-gray-200">
                            <!-- Success Flash Message -->
                            <div
                                v-if="showSuccess && flashMessage"
                                class="px-4 py-3 mb-4 text-sm text-green-800 bg-green-100 border border-green-300 rounded">
                                {{ flashMessage }}
                            </div>

                            <!-- Booking Conflict Error (Optional fallback if still present in this page) -->
                            <div
                                v-if="pageProps.errors?.booking_conflict"
                                class="px-4 py-3 mb-4 text-sm text-red-700 bg-red-100 border border-red-300 rounded">
                                {{ pageProps.errors.booking_conflict }}
                            </div>

                            <BookingClosed
                                :buttonName="office.office_name"
                                :office-id="office.id"
                                :pricing-options="pricingOptions"
                                :available-plans="availablePlans"
                                :category-id="category.id"
                                :category-name="category.name"
                                :parking="parking"
                                :location="office.location?.name"
                                :booked-dates="props.bookedDates"
                                :selected-plan="availablePlans[0]" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
