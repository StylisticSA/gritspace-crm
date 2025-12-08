<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import { format } from 'date-fns';

const props = defineProps({
    users: Array,
    show: Boolean,
    closedPlan: Array,
    clientPlan: Array,
    onClose: Function,
    can: Object,
});

const formatDate = dateStr => {
    return dateStr ? format(new Date(dateStr), 'dd MMM yyyy') : '';
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/60">
        <div class="w-full max-w-xl bg-white rounded shadow-lg flex flex-col max-h-[70vh] sm:p-0">
            <!-- Sticky Header -->
            <div
                class="sticky top-0 z-10 flex items-center justify-between px-6 py-4 bg-white border-b border-gray-300">
                <h2 class="text-2xl">Running Contracts</h2>
                <button
                    @click="props.onClose"
                    class="text-sm text-green-800 underline">
                    Close
                </button>
            </div>

            <!-- Scrollable Content Wrapper -->
            <div class="flex-grow px-4 overflow-y-auto">
                <div class="grid grid-cols-1 gap-2 md:grid-cols-1">
                    <!-- Closed Offices -->

                    <div class="px-6 py-4">
                        <div v-if="props.clientPlan && props.clientPlan.length > 0">
                            <div
                                v-for="plan in props.clientPlan"
                                :key="plan.id"
                                class="p-4 mb-6 border border-gray-300 rounded-lg shadow-sm">
                                <div class="grid grid-cols-[140px_1fr] gap-x-2 items-center">
                                    <div class="mb-2 font-semibold text-gray-600">Office Name:</div>
                                    <div class="mb-2 text-gray-700">{{ plan.office_name }}</div>

                                    <div class="mb-2 font-semibold text-gray-600">Plan:</div>
                                    <div class="mb-2 text-gray-700">{{ plan.space_id }}</div>

                                    <div class="mb-2 font-semibold text-gray-600">Total Price:</div>
                                    <div class="mb-2 text-gray-700">R {{ plan.price }}</div>

                                    <div class="mb-2 font-semibold text-gray-600">Start Date:</div>
                                    <div class="mb-2 text-gray-700">{{ formatDate(plan.start_date) }}</div>

                                    <div class="mb-2 font-semibold text-gray-600">End Date:</div>
                                    <div class="mb-2 text-gray-700">{{ formatDate(plan.end_date) }}</div>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="flex justify-center w-full mx-auto my-10">
                            <p>You do not have any plan...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Footer -->
            <div class="sticky bottom-0 z-10 px-6 py-4 bg-white border-t border-gray-300">
                <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        @click="props.onClose"
                        class="w-full px-4 py-2 text-sm text-white rounded sm:w-auto bg-muted">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
