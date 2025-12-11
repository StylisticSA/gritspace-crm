<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import NoteTrail from '@/Components/NoteTrail.vue';
import { ref } from 'vue';
import GlobalNoteModal from '@/Components/Modals/NoteModal.vue';
import PlanModal from '@/Components/Modals/PlanModal.vue';
import ClientModal from '@/Components/Modals/ClientModal.vue';
import CoffeeModal from '@/Components/Modals/CoffeeModal.vue';
import PrintingModal from '@/Components/Modals/PrintingModal.vue';
import BoardroomModal from '@/Components/Modals/BoardroomModal.vue';
import VirtualModal from '@/Components/Modals/VirtualModal.vue';
import AgreementModal from '@/Components/Modals/AgreementModal.vue';

const props = defineProps({
    notes: Object,
    users: Object,
    user: Number,
    can: Object,
    location: Number,
    locations: Object,
    coffee: Object,
    coffeeMonthly: Number,
    printing: Object,
    printBlack: Object,
    printColor: Object,
    printColorTotal: Number,
    printBlackTotal: Number,
    officebookings: Object,
    boardroomPlan: Array,
    hotDeskPlan: Array,
    dedicatedOfficePlan: Object,
    closedOfficePlan: Object,
    clientRatePlan: Object,
    virtualPlan: Object,
    agreement: Object,
    clientAvail: Boolean,
    can: Object,
});

const showNoteModal = ref(false);
const showAgreementModal = ref(false);
const showPlanModal = ref(false);
const showClientModal = ref(false);
const showCofeModal = ref(false);
const showPrintModal = ref(false);
const showBoardModal = ref(false);
const showVirtuals = ref(false);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between space-x-5">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                <div class="flex items-center justify-between overflow-hidden bg-white rounded-md shadow">
                    <div class="p-4 text-base text-gray-900 sm:p-6 sm:text-lg">Welcome at Grit Space CRM</div>
                    <div
                        class="p-4"
                        v-if="!clientAvail">
                        <span>Please fill in your Company Details </span>

                        <Link
                            :href="route('companydetail.create')"
                            class="inline-block px-3 py-2 text-lg font-medium text-primary rounded">
                            + here
                        </Link>
                    </div>
                </div>

                <!-- 3-column grid -->
                <div v-if="can['view dashboard']">
                    <div class="grid grid-cols-1 gap-4 py-5 lg:grid-cols-3">
                        <div
                            class="rounded-2xl border border-gray-200 bg-white px-6 pb-5 pt-6 dark:border-gray-800 dark:bg-white/[0.03]">
                            <!-- Header -->
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10">
                                    <img
                                        src="../../../public/files_grits/planning.png"
                                        alt="Plan Icon" />
                                </div>

                                <div>
                                    <h3 class="text-base font-semibold">My Contracts</h3>
                                </div>
                            </div>

                            <!-- Price & Status -->
                            <div class="flex items-end justify-between">
                                <div class="flex items-start gap-2">
                                    <div>
                                        <button
                                            @click="showPlanModal = true"
                                            class="px-3 py-1 text-sm font-semibold text-white rounded bg-bluemain hover:bg-bluemain/60">
                                            View
                                        </button>
                                    </div>
                                    <div v-if="props.clientRatePlan.length > 0">
                                        <button
                                            @click="showClientModal = true"
                                            class="px-3 py-1 text-sm font-semibold text-white rounded bg-bluemain hover:bg-bluemain/60">
                                            Previous
                                        </button>
                                    </div>
                                </div>

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
                                            d="M6.06462 1.62393C6.20193 1.47072 6.40135 1.37432 6.62329 1.37432C6.6236 1.37432 6.62391 1.37432 6.62422 1.37432C6.81631 1.37415 7.00845 1.44731 7.15505 1.5938L10.1551 4.5918C10.4481 4.88459 10.4483 5.35946 10.1555 5.65246C9.86273 5.94546 9.38785 5.94562 9.09486 5.65283L7.37329 3.93247L7.37329 10.125C7.37329 10.5392 7.03751 10.875 6.62329 10.875C6.20908 10.875 5.87329 10.5392 5.87329 10.125L5.87329 3.93578L4.15516 5.65281C3.86218 5.94561 3.3873 5.94546 3.0945 5.65248C2.8017 5.35949 2.80185 4.88462 3.09484 4.59182L6.06462 1.62393Z"
                                            fill=""></path>
                                    </svg>
                                    Active
                                </span>
                            </div>
                        </div>
                        <div
                            class="rounded-2xl border border-gray-200 bg-white px-6 pb-5 pt-6 dark:border-gray-800 dark:bg-white/[0.03]">
                            <!-- Header -->
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10">
                                    <img
                                        src="../../../public/files_grits/board-meeting.png"
                                        alt="Plan Icon" />
                                </div>

                                <div><h3 class="text-base font-semibold">Boardrooms Bookings</h3></div>
                            </div>

                            <!-- Price & Status -->
                            <div class="flex items-end justify-between">
                                <div>
                                    <button
                                        @click="showBoardModal = true"
                                        class="px-3 py-1 text-sm font-semibold text-white rounded bg-bluemain hover:bg-bluemain/60">
                                        View
                                    </button>
                                </div>

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
                                            d="M6.06462 1.62393C6.20193 1.47072 6.40135 1.37432 6.62329 1.37432C6.6236 1.37432 6.62391 1.37432 6.62422 1.37432C6.81631 1.37415 7.00845 1.44731 7.15505 1.5938L10.1551 4.5918C10.4481 4.88459 10.4483 5.35946 10.1555 5.65246C9.86273 5.94546 9.38785 5.94562 9.09486 5.65283L7.37329 3.93247L7.37329 10.125C7.37329 10.5392 7.03751 10.875 6.62329 10.875C6.20908 10.875 5.87329 10.5392 5.87329 10.125L5.87329 3.93578L4.15516 5.65281C3.86218 5.94561 3.3873 5.94546 3.0945 5.65248C2.8017 5.35949 2.80185 4.88462 3.09484 4.59182L6.06462 1.62393Z"
                                            fill=""></path>
                                    </svg>
                                    Active
                                </span>
                            </div>
                        </div>
                        <div
                            class="rounded-2xl border border-gray-200 bg-white px-6 pb-5 pt-6 dark:border-gray-800 dark:bg-white/[0.03]">
                            <!-- Header -->
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10">
                                    <img
                                        src="../../../public/files_grits/desk.png"
                                        alt="Plan Icon" />
                                </div>

                                <div><h3 class="text-base font-semibold">Virtual Offices</h3></div>
                            </div>

                            <!-- Price & Status -->
                            <div class="flex items-end justify-between">
                                <div>
                                    <button
                                        @click="showVirtuals = true"
                                        class="px-3 py-1 text-sm font-semibold text-white rounded bg-bluemain hover:bg-bluemain/60">
                                        View
                                    </button>
                                </div>

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
                                            d="M6.06462 1.62393C6.20193 1.47072 6.40135 1.37432 6.62329 1.37432C6.6236 1.37432 6.62391 1.37432 6.62422 1.37432C6.81631 1.37415 7.00845 1.44731 7.15505 1.5938L10.1551 4.5918C10.4481 4.88459 10.4483 5.35946 10.1555 5.65246C9.86273 5.94546 9.38785 5.94562 9.09486 5.65283L7.37329 3.93247L7.37329 10.125C7.37329 10.5392 7.03751 10.875 6.62329 10.875C6.20908 10.875 5.87329 10.5392 5.87329 10.125L5.87329 3.93578L4.15516 5.65281C3.86218 5.94561 3.3873 5.94546 3.0945 5.65248C2.8017 5.35949 2.80185 4.88462 3.09484 4.59182L6.06462 1.62393Z"
                                            fill=""></path>
                                    </svg>
                                    Active
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2-column grid -->
                <div v-if="can['view dashboard']">
                    <div class="grid grid-cols-1 gap-4 py-5 lg:grid-cols-3">
                        <!-- Extras -->
                        <div class="p-4 mb-3 text-lg font-semibold text-gray-800 bg-white md:max-h-[40vh]">
                            <h3 class="flex items-center justify-between mb-5 text-lg font-semibold text-gray-800">
                                Extras
                                <span class="text-sm">Usage from: 23rd - Today</span>
                            </h3>
                            <div
                                class="grid items-center grid-cols-1 gap-4 pt-5 sm:grid-cols-2"
                                v-show="user">
                                <!-- Coffee -->
                                <div
                                    class="border-r border-gray-200 bg-white px-6 pb-6 pt-6 dark:border-gray-800 dark:bg-white/[0.03]">
                                    <div class="text-center">
                                        <h3 class="text-xl font-semibold text-gray-600">Coffee</h3>
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

                                <!-- Printing -->
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

                        <div class="p-4 mb-3 text-lg font-semibold text-gray-800 bg-white md:max-h-[40vh]">
                            <h3 class="flex items-center justify-between mb-5 text-lg font-semibold text-gray-800">
                                Invoices
                                <span class="text-sm"></span>
                            </h3>
                            <div class="grid items-center grid-cols-1 gap-4 pt-5 sm:grid-cols-2">
                                <!-- Coffee -->
                                <div></div>
                            </div>
                        </div>

                        <!-- Column 2: Notes Trail -->
                        <div class="p-4 overflow-y-auto bg-white rounded shadow">
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
            </div>

            <GlobalNoteModal
                :users="users"
                :show="showNoteModal"
                :onClose="() => (showNoteModal = false)" />

            <AgreementModal
                :locations="locations"
                :agreement="agreement"
                :show="showAgreementModal"
                :can="can"
                :onClose="() => (showAgreementModal = false)" />

            <PlanModal
                :users="users"
                :dedicatedPlan="dedicatedOfficePlan"
                :closedPlan="closedOfficePlan"
                :hotdeskPlan="hotDeskPlan"
                :show="showPlanModal"
                :can="can"
                :onClose="() => (showPlanModal = false)" />

            <ClientModal
                :users="users"
                :closedPlan="closedOfficePlan"
                :clientPlan="clientRatePlan"
                :show="showClientModal"
                :can="can"
                :onClose="() => (showClientModal = false)" />

            <CoffeeModal
                :user="user"
                :coffee="coffee"
                :location="location"
                :monthly="coffeeMonthly"
                :show="showCofeModal"
                :can="can"
                :onClose="() => (showCofeModal = false)" />

            <PrintingModal
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

            <VirtualModal
                :user="user"
                :virtualPlan="virtualPlan"
                :location="location"
                :show="showVirtuals"
                :onClose="() => (showVirtuals = false)"
                :can="can" />
        </div>
    </AuthenticatedLayout>
</template>
