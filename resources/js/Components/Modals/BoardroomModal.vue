<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const props = defineProps({
    users: Array,
    show: Boolean,
    location: Object,
    board: Array,
    onClose: Function,
    can: Object,
});
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black/60">
        <div class="w-full max-w-4xl bg-white rounded shadow-lg flex flex-col max-h-[80vh] sm:p-0">
            <!-- Sticky Header -->
            <div
                class="sticky top-0 z-10 flex items-center justify-between px-6 py-4 bg-white border-b border-gray-300">
                <h2 class="text-2xl">Boardroom Bookings</h2>
                <button
                    @click="props.onClose"
                    class="text-sm text-green-800 underline">
                    Close
                </button>
            </div>

            <!-- Scrollable Content -->

            <div class="px-6 py-4">
                <div
                    v-if="props.board && props.board.length > 0"
                    class="max-h-[60vh] overflow-y-auto">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div
                            v-for="plan in props.board"
                            :key="plan.id"
                            class="p-4 border border-gray-300 rounded-lg shadow-sm">
                            <div class="grid grid-cols-[140px_1fr] md:grid-cols-[200px_1fr] gap-x-2 items-center">
                                <div class="mb-2 font-semibold text-gray-600">Boardroom:</div>
                                <div class="mb-2 text-gray-700">{{ plan.boardroom?.boardroom_name }}</div>

                                <div class="mb-2 font-semibold text-gray-600">Duration:</div>
                                <div class="mb-2 text-gray-700">
                                    {{ plan.months }} {{ plan.plan === 'hourly' ? 'Hour(s)' : 'Daily' }}
                                </div>

                                <div class="mb-2 font-semibold text-gray-600">Plan:</div>
                                <div class="mb-2 text-gray-700">{{ plan.plan }}</div>

                                <div class="mb-2 font-semibold text-gray-600">Total Price:</div>
                                <div class="mb-2 text-gray-700">R {{ plan.selected_price }}</div>
                            </div>
                            <div class="flex justify-center pt-5">
                                <Link
                                    :href="route('bookingboardroom.show')"
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
                    <p>You do not have any Boardrooms...</p>
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
