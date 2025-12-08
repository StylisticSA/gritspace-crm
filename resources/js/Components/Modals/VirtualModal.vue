<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const props = defineProps({
    users: Array,
    show: Boolean,
    location: Object,
    virtualPlan: Array,
    onClose: Function,
    can: Object,
});
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/60">
        <div class="w-full max-w-5xl bg-white rounded shadow-lg sm:p-0 flex flex-col max-h-[80vh]">
            <!-- Sticky Header -->
            <div
                class="sticky top-0 z-10 flex items-center justify-between px-6 py-4 bg-white border-b border-gray-300">
                <h2 class="text-2xl">Virtual Office</h2>
                <button
                    @click="props.onClose"
                    class="text-sm text-green-800 underline">
                    Close
                </button>
            </div>

            <!-- Scrollable Content -->
            <div class="flex-grow px-6 py-4 overflow-y-auto">
                <div v-if="props.virtualPlan && props.virtualPlan.length > 0">
                    <!-- Two cards per row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div
                            v-for="plan in props.virtualPlan"
                            :key="plan.id"
                            class="p-4 border border-gray-300 rounded-lg shadow-sm">
                            <div class="grid grid-cols-[140px_1fr] md:grid-cols-[200px_1fr] gap-x-2 items-center">
                                <div class="mb-2 font-semibold text-gray-600">Office Name:</div>
                                <div class="mb-2 text-gray-700">
                                    {{ plan.virtual_office?.virtualoffice_name }}
                                </div>

                                <div class="mb-2 font-semibold text-gray-600">Duration:</div>
                                <div class="mb-2 text-gray-700">{{ plan.months }} Months</div>

                                <div class="mb-2 font-semibold text-gray-600">Total Price:</div>
                                <div class="mb-2 text-gray-700">R {{ plan.selected_price }}</div>

                                <div class="mb-2 font-semibold text-gray-600">Start Date:</div>
                                <div class="mb-2 text-gray-700">{{ plan.start_date }}</div>

                                <div class="mb-2 font-semibold text-gray-600">End Date:</div>
                                <div class="mb-2 text-gray-700">{{ plan.end_date }}</div>

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
                            <div class="flex justify-center pt-5">
                                <Link
                                    :href="route('bookingvirtual.show')"
                                    class="w-full px-3 py-1 text-sm font-semibold text-center text-white rounded bg-bluemain hover:bg-bluemain/60">
                                    View
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="flex justify-center w-full mx-auto my-5">
                    <p>You do not have any Virtual Offices...</p>
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
