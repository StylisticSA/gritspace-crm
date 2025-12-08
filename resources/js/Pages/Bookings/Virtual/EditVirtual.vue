<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import BookingVirtual from '@/Components/BookingVirtual.vue';

interface Location {
    name: string;
}

interface Virtual {
    id?: number;
    location?: Location;
    virtualoffice_name?: string;
    address?: string;
    discount: number;
    price: number;
    price_premium: number;
    price_standard: number;
}

interface BookedVirtual {
    plan: string;
    selected_dates: string[];
}

const props = defineProps<{
    virtual: Virtual;
    locations: Location;
    bookedRanges: BookedVirtual[];
}>();

const { props: pageProps } = usePage();
const flash = (pageProps.flash ?? {}) as { success?: string };
</script>

<template>
    <Head title="View Virtual Office" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Booking Virtual Office</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-4 lg:px-8">
                <div class="flex justify-center">
                    <div class="w-full max-w-2xl p-6 space-y-6 bg-white border rounded-lg shadow-md">
                        <!-- Header -->
                        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                            <h3 class="text-xl font-semibold">
                                {{ virtual.virtualoffice_name || 'Virtual Office' }}
                            </h3>
                            <Link
                                :href="route('virtual.home')"
                                class="inline-block w-full px-4 py-2 text-sm font-medium text-center text-white rounded md:w-auto bg-primary hover:bg-bluemain">
                                Back
                            </Link>
                        </div>

                        <!-- Details -->
                        <div class="grid grid-cols-1 gap-6 text-sm text-gray-700 md:grid-cols-2">
                            <div class="space-y-2">
                                <p><strong>Location:</strong> {{ virtual.location?.name || 'N/A' }}</p>
                            </div>

                            <div class="space-y-2">
                                <p v-if="virtual.price">
                                    Price: <strong>R{{ virtual.price || '0.00' }}</strong>
                                </p>
                            </div>
                        </div>

                        <!-- Booking Form -->
                        <div class="pt-6 mt-6 border-t border-gray-200">
                            <BookingVirtual
                                :virtual-Id="virtual.id"
                                :buttonName="virtual.virtualoffice_name"
                                :price="virtual.price"
                                :available-plans="[virtual.virtualoffice_name]"
                                :selected-plan="virtual.virtualoffice_name"
                                :booked-ranges="bookedRanges" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
