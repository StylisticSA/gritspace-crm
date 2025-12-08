<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    boardrooms: Array,
});
console.log('board', props.boardrooms);
</script>

<template>
    <Head title="View Boardroom" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">View Boardroom</h2>
        </template>

        <div class="px-5 py-12">
            <div class="mx-auto max-w-7xl sm:px-4 lg:px-8">
                <div class="flex justify-center">
                    <div class="w-full max-w-2xl p-6 space-y-6 bg-white border rounded-lg shadow-md">
                        <!-- Header -->
                        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                            <h3 class="text-xl font-semibold">{{ boardrooms?.boardroom?.boardroom_name }}</h3>
                            <Link
                                :href="route('bookingboardroom.show')"
                                class="inline-block w-full px-4 py-2 text-sm font-medium text-center text-white rounded md:w-auto bg-primary hover:bg-bluemain">
                                View Booked
                            </Link>
                        </div>

                        <div class="grid grid-cols-1 gap-6 pt-5 text-sm text-gray-700 md:grid-cols-2">
                            <div class="space-y-2">
                                <div class="grid grid-cols-[140px_1fr] gap-x-2 items-start">
                                    <div class="mb-3 font-medium text-gray-600"><strong>User: </strong></div>
                                    <div>{{ boardrooms?.user?.name }}</div>

                                    <div class="mb-3 font-medium text-gray-600">
                                        <strong>Location: </strong>
                                    </div>
                                    <div class="mb-2">{{ boardrooms?.boardroom?.location?.name }}</div>

                                    <div class="mb-3 font-medium text-gray-600">
                                        <strong>Plan: </strong>
                                    </div>
                                    <div class="mb-2">{{ boardrooms.plan }}</div>

                                    <div class="mb-3 font-medium text-gray-600">
                                        <strong>Duration: </strong>
                                    </div>
                                    <div class="mb-2">{{ boardrooms.months + ' Months' }}</div>
                                </div>
                            </div>

                            <div>
                                <div class="grid grid-cols-[140px_1fr] gap-x-2 items-start">
                                    <div class="mb-3 font-medium text-gray-600"><strong>Status: </strong></div>
                                    <div class="mb-2">
                                        <span
                                            :class="{
                                                'px-2 py-1 rounded text-xs font-semibold capitalize': true,
                                                'bg-yellow-100 text-yellow-800': boardrooms?.status === 'pending',
                                                'bg-green-100 text-green-800': boardrooms?.status === 'approved',
                                                'bg-gray-200 text-gray-700': boardrooms?.status === 'cancelled',
                                                'bg-red-100 text-red-700': boardrooms?.status === 'rejected',
                                            }">
                                            {{ boardrooms?.status ?? 'N/A' }}
                                        </span>
                                    </div>

                                    <div class="mb-3 font-medium text-gray-600"><strong>Price:</strong></div>
                                    <div class="mb-2">R{{ boardrooms?.selected_price }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
