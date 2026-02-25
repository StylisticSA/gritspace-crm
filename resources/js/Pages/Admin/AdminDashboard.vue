<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import NoteTrail from '../../Components/NoteTrail.vue';
import { ref } from 'vue';
import GlobalNoteModal from '../../Components/Modals/NoteModal.vue';
import PlanModal from '../../Components/Modals/PlanModal.vue';
import CoffeeModal from '../../Components/Modals/CoffeeModal.vue';
import PrintingModal from '../../Components/Modals/PrintingModal.vue';
import BoardroomModal from '../../Components/Modals/BoardroomModal.vue';
import HotDeskModal from '../../Components/Modals/VirtualModal.vue';
import { format } from 'date-fns';

const props = defineProps({
    notes: Object,
    users: Object,
    user: Number,
    can: Object,
    officebookings: Object,

    location: Number,
    locations: Object,
    locationsWithStats: Object,

    coffee: Object,
    coffeeMonthly: Number,

    printing: Object,
    printBlack: Object,
    printColor: Object,
    printColorTotal: Number,
    printBlackTotal: Number,
    invoiceCounts: Array,
});

const showNoteModal = ref(false);
const showPlanModal = ref(false);
const showCofeModal = ref(false);
const showPrintModal = ref(false);
const showBoardModal = ref(false);
const showHotDeskModal = ref(false);

function viewInvoices() {
    if (props.can['manage settings']) {
        router.visit(`/admin/invoices`);
    }
}
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between space-x-5">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>
                <button
                    v-if="can['manage settings']"
                    @click="showNoteModal = true"
                    class="px-2 py-2 text-lg text-white rounded bg-bluemain hover:bluemain/60">
                    Add Note
                </button>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="overflow-hidden bg-white rounded-md shadow">
                    <div class="p-4 text-base text-gray-900 sm:p-6 sm:text-lg">
                        Welcome to the Administration Portal
                    </div>
                </div>

                <!-- 3-column grid src="../../../../public/files_grits/planning.png"-->
                <div v-if="locationsWithStats && locationsWithStats.length > 0">
                    <div class="grid grid-cols-1 gap-4 py-5 lg:grid-cols-3">
                        <div
                            v-for="location in locationsWithStats"
                            :key="location.id"
                            class="rounded-2xl border border-gray-200 bg-white px-6 pb-5 pt-6 dark:border-gray-800 dark:bg-white/[0.03]">
                            <!-- Header -->
                            <div class="flex items-center gap-3 mb-6 justify-items-center">
                                <div class="w-10 h-10">
                                    <img
                                        src="../../../../public/files_grits/location-pin.png"
                                        alt="Plan Icon" />
                                </div>
                                <div>
                                    <h3 class="mb-4 text-lg font-semibold">{{ location.name }}</h3>
                                </div>
                            </div>

                            <!-- Location Name -->

                            <!-- Aggregated Counts -->
                            <div class="pl-5 mb-4 space-y-1 text-sm text-gray-700 dark:text-gray-300">
                                <p>{{ location.closedCount }} - Closed Offices</p>
                                <p>{{ location.dedicatedCount }} - Dedicated Desks</p>
                                <p>{{ location.boardroomCount }} - Boardrooms</p>
                                <p>{{ location.hotdeskCount }} - Hot Desks</p>
                                <p>{{ location.virtualCount }} - Virtuals</p>
                            </div>

                            <!-- Price & Status -->
                            <div class="flex items-end justify-between pl-5">
                                <button
                                    class="px-3 py-1 text-sm font-semibold text-white rounded bg-bluemain hover:bg-bluemain/60">
                                    Office Plans
                                </button>

                                <span
                                    class="flex items-center gap-1 rounded-full bg-success-50 py-0.5 pl-2 pr-2.5 text-sm font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">
                                    <svg
                                        class="fill-current"
                                        width="13"
                                        height="12"
                                        viewBox="0 0 13 12"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            fill-rule="evenodd"
                                            clip-rule="evenodd"
                                            d="M6.06462 1.62393C6.20193 1.47072 6.40135 1.37432 6.62329 1.37432C6.6236 1.37432 6.62391 1.37432 6.62422 1.37432C6.81631 1.37415 7.00845 1.44731 7.15505 1.5938L10.1551 4.5918C10.4481 4.88459 10.4483 5.35946 10.1555 5.65246C9.86273 5.94546 9.38785 5.94562 9.09486 5.65283L7.37329 3.93247V10.125C7.37329 10.5392 7.03751 10.875 6.62329 10.875C6.20908 10.875 5.87329 10.5392 5.87329 10.125V3.93578L4.15516 5.65281C3.86218 5.94561 3.3873 5.94546 3.0945 5.65248C2.8017 5.35949 2.80185 4.88462 3.09484 4.59182L6.06462 1.62393Z"
                                            fill="" />
                                    </svg>
                                    Approved / Paid
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <div class="bg-white">
                        <p class="flex items-center my-5 p-5 text-red-500">There is no locations, Please add..</p>
                    </div>
                </div>

                <!-- 2-column grid -->
                <div class="grid grid-cols-1 gap-4 py-5 lg:grid-cols-3">
                    <!-- LEFT COLUMN: Two stacked Extras blocks -->
                    <div class="flex flex-col gap-4">
                        <!-- Extras Block 1 -->
                        <div class="p-4 text-lg font-semibold text-gray-800 bg-white">
                            <h3 class="flex items-center justify-between mb-5 text-lg font-semibold text-gray-800">
                                Extras
                                <span class="text-sm">Usage from: 23rd - Today</span>
                            </h3>
                            <div class="grid items-center grid-cols-1 gap-4 pt-5 sm:grid-cols-2">
                                <!-- Coffee -->
                                <div
                                    class="border-r border-gray-200 bg-white px-6 pb-6 pt-6 dark:border-gray-800 dark:bg-white/[0.03]">
                                    <div class="text-center">
                                        <h3 class="text-base font-semibold text-gray-600">Coffee</h3>
                                        <p class="mt-2 text-5xl font-bold text-gray-900">{{ coffee }}</p>
                                        <span class="block mt-1 text-sm text-gray-500">cups</span>
                                    </div>
                                    <div class="flex justify-center mt-6">
                                        <button
                                            @click="showCofeModal = true"
                                            class="px-3 py-1 text-sm font-semibold text-white rounded bg-bluemain hover:bg-bluemain/60">
                                            View
                                        </button>
                                    </div>
                                </div>

                                <!-- Printed -->
                                <div class="px-6 pt-5 pb-5 bg-white border-l border-black md:border-l-0">
                                    <div class="text-center">
                                        <h3 class="text-xl font-semibold text-gray-600">Printed</h3>
                                        <p class="pt-5 text-gray-600 text-md">Color - {{ printColor }}</p>
                                        <p class="pt-2 text-gray-600 text-md">Black - {{ printBlack }}</p>
                                    </div>

                                    <div class="flex justify-center pt-5">
                                        <button
                                            @click="showPrintModal = true"
                                            class="px-3 py-1 text-sm font-semibold text-white rounded bg-bluemain hover:bg-bluemain/60">
                                            View
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoices -->
                    <div class="p-4 mb-3 text-lg font-semibold text-gray-800 bg-white md:max-h-[40vh]">
                        <h3 class="flex items-center justify-between mb-5 text-lg font-semibold text-gray-800">
                            Invoices
                            <span class="text-sm"></span>
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
                            <!-- Pending -->
                            <div class="bg-yellow-50 p-4 rounded shadow">
                                <h4 class="text-sm font-semibold text-yellow-800">Pending</h4>
                                <p class="text-3xl font-bold text-yellow-900">{{ invoiceCounts.pending ?? 0 }}</p>
                            </div>

                            <!-- Paid -->
                            <div class="bg-green-50 p-4 rounded shadow">
                                <h4 class="text-sm font-semibold text-green-800">Paid</h4>
                                <p class="text-3xl font-bold text-green-900">{{ invoiceCounts.paid ?? 0 }}</p>
                            </div>

                            <!-- Cancelled -->
                            <div class="bg-gray-100 p-4 rounded shadow">
                                <h4 class="text-sm font-semibold text-gray-700">Cancelled</h4>
                                <p class="text-3xl font-bold text-gray-900">{{ invoiceCounts.cancelled ?? 0 }}</p>
                            </div>
                        </div>
                        <div>
                            <button
                                @click="viewInvoices()"
                                class="block w-full px-3 py-1 mt-5 text-sm font-semibold text-white rounded bg-bluemain hover:bg-bluemain/60">
                                View All Invoices
                            </button>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Recent Notes spans full height on desktop -->
                    <div class="p-4 overflow-y-auto bg-white rounded shadow lg:row-span-2">
                        <h3 class="flex justify-between mb-3 text-lg font-semibold text-gray-800">
                            Recent Notes
                            <span>{{ notes.length >= 1 ? '' : 'None' }}</span>
                        </h3>
                        <NoteTrail
                            :notes="notes"
                            :can="can" />
                    </div>
                </div>
            </div>

            <GlobalNoteModal
                :users="users"
                :show="showNoteModal"
                :onClose="() => (showNoteModal = false)" />

            <PlanModal
                :users="users"
                :currentPlan="currentPlan"
                :show="showPlanModal"
                :can="can"
                :onClose="() => (showPlanModal = false)" />

            <CoffeeModal
                :users="users"
                :user="user"
                :coffee="coffee"
                :location="location"
                :locations="locations"
                :monthly="coffeeMonthly"
                :show="showCofeModal"
                :can="can"
                :onClose="() => (showCofeModal = false)" />

            <PrintingModal
                :users="users"
                :user="user"
                :print="printing"
                :printBlack="printBlack"
                :printColor="printColor"
                :printColorTotal="printColorTotal"
                :printBlackTotal="printBlackTotal"
                :location="location"
                :show="showPrintModal"
                :onClose="() => (showPrintModal = false)"
                :can="can" />

            <BoardroomModal
                :user="user"
                :board="boardroomPlan"
                :location="location"
                :show="showBoardModal"
                :onClose="() => (showBoardModal = false)"
                :can="can" />

            <HotDeskModal
                :user="user"
                :hotdesk="hotDeskPlan"
                :location="location"
                :show="showHotDeskModal"
                :onClose="() => (showHotDeskModal = false)"
                :can="can" />
        </div>
    </AuthenticatedLayout>
</template>
