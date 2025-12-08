<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import { format } from 'date-fns';

const props = defineProps({
    users: Array,
    show: Boolean,
    hotdeskPlan: Array,
    dedicatedPlan: Array,
    closedPlan: Array,
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
        <div class="w-full max-w-6xl bg-white rounded shadow-lg flex flex-col max-h-[70vh] sm:p-0">
            <!-- Sticky Header -->
            <div
                class="sticky top-0 z-10 flex items-center justify-between px-6 py-4 bg-white border-b border-gray-300">
                <h2 class="text-2xl">My Contracts</h2>
                <button
                    @click="props.onClose"
                    class="text-sm text-green-800 underline">
                    Close
                </button>
            </div>

            <!-- Scrollable Content Wrapper -->
            <div class="flex-grow px-4 overflow-y-auto">
                <div class="grid grid-cols-1 gap-2 md:grid-cols-3">
                    <!-- Closed Offices -->

                    <div class="px-6 py-4">
                        <h3 class="mb-5 text-xl">Closed Offices</h3>
                        <div v-if="props.closedPlan && props.closedPlan.length > 0">
                            <div
                                v-for="plan in props.closedPlan"
                                :key="plan.id"
                                class="p-4 mb-6 border border-gray-300 rounded-lg shadow-sm">
                                <div class="grid grid-cols-[140px_1fr] gap-x-2 items-center">
                                    <div class="mb-2 font-semibold text-gray-600">Office Name:</div>
                                    <div class="mb-2 text-gray-700">{{ plan.office?.office_name }}</div>

                                    <div class="mb-2 font-semibold text-gray-600">Location:</div>
                                    <div class="mb-2 text-gray-700">{{ plan.office.location?.name }}</div>

                                    <div class="mb-2 font-semibold text-gray-600">Plan:</div>
                                    <div class="mb-2 text-gray-700">{{ plan.plan }}</div>

                                    <div class="mb-2 font-semibold text-gray-600">Total Price:</div>
                                    <div class="mb-2 text-gray-700">R {{ plan.total_price }}</div>

                                    <div class="mb-2 font-semibold text-gray-600">Start Date:</div>
                                    <div class="mb-2 text-gray-700">{{ formatDate(plan.start_date) }}</div>

                                    <div class="mb-2 font-semibold text-gray-600">End Date:</div>
                                    <div class="mb-2 text-gray-700">{{ formatDate(plan.end_date) }}</div>

                                    <div class="mb-2 font-semibold text-gray-600">Status:</div>
                                    <div class="mb-2 text-gray-700">
                                        <span
                                            :class="{
                                                'px-2 py-1 rounded text-xs font-semibold capitalize': true,
                                                'bg-yellow-100 text-yellow-800': plan.status === 'pending',
                                                'bg-green-100 text-green-800': plan.status === 'approved',
                                                'bg-gray-200 text-gray-700': plan.status === 'cancelled',
                                                'bg-red-100 text-red-700': plan.status === 'rejected',
                                                'bg-red-100 text-primary': plan.status === 'paid',
                                            }">
                                            {{ plan.status ?? 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="flex w-full mx-auto my-10">
                            <p>You do not have any plan...</p>
                        </div>
                    </div>

                    <!-- Dedicated Desks -->
                    <div class="px-6 py-4">
                        <h3 class="mb-5 text-xl">Dedicated Desks</h3>

                        <div v-if="props.dedicatedPlan && props.dedicatedPlan.length > 0">
                            <div
                                v-for="plan in props.dedicatedPlan"
                                :key="plan.id"
                                class="p-4 mb-6 border border-gray-300 rounded-lg shadow-sm">
                                <div class="grid grid-cols-[140px_1fr] gap-x-2 items-center">
                                    <div class="mb-2 font-semibold text-gray-600">Office Name:</div>
                                    <div class="mb-2 text-gray-700">{{ plan.office?.office_name }}</div>

                                    <div class="mb-2 font-semibold text-gray-600">Location:</div>
                                    <div class="mb-2 text-gray-700">{{ plan.office.location?.name }}</div>

                                    <div class="mb-2 font-semibold text-gray-600">Plan:</div>
                                    <div class="mb-2 text-gray-700">{{ plan.plan }}</div>

                                    <div class="mb-2 font-semibold text-gray-600">Total Price:</div>
                                    <div class="mb-2 text-gray-700">R {{ plan.total_price }}</div>

                                    <div class="mb-2 font-semibold text-gray-600">Start Date:</div>
                                    <div class="mb-2 text-gray-700">{{ formatDate(plan.start_date) }}</div>

                                    <div class="mb-2 font-semibold text-gray-600">End Date:</div>
                                    <div class="mb-2 text-gray-700">{{ formatDate(plan.end_date) }}</div>

                                    <div class="mb-2 font-semibold text-gray-600">Status:</div>
                                    <div class="mb-2 text-gray-700">
                                        <span
                                            :class="{
                                                'px-2 py-1 rounded text-xs font-semibold capitalize': true,
                                                'bg-yellow-100 text-yellow-800': plan.status === 'pending',
                                                'bg-green-100 text-green-800': plan.status === 'approved',
                                                'bg-gray-200 text-gray-700': plan.status === 'cancelled',
                                                'bg-red-100 text-red-700': plan.status === 'rejected',
                                                'bg-red-100 text-primary': plan.status === 'paid',
                                            }">
                                            {{ plan.status ?? 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="flex w-full mx-auto my-10">
                            <p>You do not have any plan...</p>
                        </div>
                    </div>

                    <!-- Hot Desk -->
                    <div class="px-6 py-4">
                        <h3 class="mb-5 text-xl">Hot Desk</h3>

                        <!-- If hotdeskPlan has items -->
                        <div v-if="props.hotdeskPlan && props.hotdeskPlan.length > 0">
                            <div
                                v-for="hot in props.hotdeskPlan"
                                :key="hot.id"
                                class="p-4 mb-6 border border-gray-300 rounded-lg shadow-sm">
                                <div class="grid grid-cols-[140px_1fr] gap-x-2 items-center">
                                    <div class="mb-2 font-semibold text-gray-600">Office Name:</div>
                                    <div class="mb-2 text-gray-700">{{ hot.help_desk?.help_desk_name }}</div>

                                    <div class="mb-2 font-semibold text-gray-600">Location:</div>
                                    <div class="mb-2 text-gray-700">{{ hot.help_desk.location?.name }}</div>

                                    <div class="mb-2 font-semibold text-gray-600">Plan:</div>
                                    <div class="mb-2 text-gray-700">{{ hot.plan }}</div>

                                    <div class="mb-2 font-semibold text-gray-600">Total Price:</div>
                                    <div class="mb-2 text-gray-700">R {{ hot.selected_price }}</div>

                                    <div class="mb-2 font-semibold text-gray-600">Days</div>
                                    <div class="mb-2 text-gray-700">{{ hot.days_count }}</div>

                                    <div class="mb-2 font-semibold text-gray-600">Status:</div>
                                    <div class="mb-2 text-gray-700">
                                        <span
                                            :class="{
                                                'px-2 py-1 rounded text-xs font-semibold capitalize': true,
                                                'bg-yellow-100 text-yellow-800': hot.status === 'pending',
                                                'bg-green-100 text-green-800': hot.status === 'approved',
                                                'bg-gray-200 text-gray-700': hot.status === 'cancelled',
                                                'bg-red-100 text-red-700': hot.status === 'rejected',
                                                'bg-red-100 text-primary': hot.status === 'paid',
                                            }">
                                            {{ hot.status ?? 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- If hotdeskPlan is empty -->
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
