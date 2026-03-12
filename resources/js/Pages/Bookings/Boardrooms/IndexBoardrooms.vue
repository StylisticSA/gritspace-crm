<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { format } from 'date-fns';
import cartOfficeModal from '../../../Components/Modals/Cart/CartOfficeModal.vue';

interface Boardroom {
    id: number;
    boardroom_name: string;
    location?: { name: string };
    seats?: number;
    hourly_price?: number;
    daily_price?: number;
    is_available: boolean;
    available_dates: Date;
}

interface Location {
    name: string;
    address?: string;
    city?: string;
}

interface approvedBoardrooms {
    id: number;
    user_id: number;
    boardroom_id: number;
    plan: string;
    months: Date;
    status: string;
    selected_price: string;
    selected_dates: string[];
    selected_times: string[];
    is_available: boolean;
    available_dates: Date;
    discount_percentage: number;
    discounted_price: number;

    boardroom: Boardroom;
}

const props = defineProps<{
    boardrooms: Boardroom[];
    locations: Location[];
    approvedBoardrooms: approvedBoardrooms[];
    can: Object;
}>();

const tabs = ['All', 'Boardrooms'];
const activeTab = ref('All');
const selectedLocation = ref('All');

const filteredBoardrooms = computed(() =>
    props.boardrooms.filter(br => selectedLocation.value === 'All' || br.location?.name === selectedLocation.value)
);

function goToBoardroom(boardroomId: number) {
    router.visit(`/booking-boardrooms/${boardroomId}`);
}

const formatDate = dateStr => {
    return dateStr ? format(new Date(dateStr), 'dd MMM yyyy') : '';
};

const showAvailModal = ref(false);

const pendingCount = computed(() => props.approvedBoardrooms?.length ?? 0);

const showOfficeModal = ref(false);
const boardroomAvail = ref<Boardroom | null>(null);

const showAvail = boardroom => {
    showOfficeModal.value = true;
    boardroomAvail.value = boardroom;
};

function canShowButton(entity) {
    if (!entity) return false;

    const today = new Date();
    const startDate = new Date(entity.available_dates);

    // normalize both to midnight local time
    today.setHours(0, 0, 0, 0);
    startDate.setHours(0, 0, 0, 0);

    return entity.is_available && today >= startDate;
}

const allBookings = computed(() => {
    const boardroom = props.approvedBoardrooms.map(b => ({
        name: b.boardroom.boardroom_name,
        type: 'Boardrooms',
        price: Number(b.discount_percentage) > 0 ? Number(b.discounted_price) : Number(b.selected_price),
        plan: b.plan,
        id: b.boardroom_id,
        months: Number(b.months),
    }));

    return [...boardroom];
});
</script>

<template>
    <Head title="Booadrooms Admin" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Boardrooms</h2>

                <div
                    class="flex gap-3"
                    v-if="pendingCount > 0">
                    <button
                        @click="showAvailModal = true"
                        type="button"
                        class="px-4 py-2 text-sm sm:text-lg text-white border border-solid rounded bg-primary hover:bg-bluemain/60 focus:outline-none">
                        Payment Pending ({{ pendingCount }})
                    </button>
                </div>
            </div>
        </template>

        <div class="px-5 py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                    <div class="col-span-1">
                        <!-- <select
                            v-model="activeTab"
                            class="w-full px-3 py-1 mb-4 text-sm border border-gray-300 rounded md:hidden">
                            <option
                                v-for="tab in tabs"
                                :key="tab"
                                :value="tab">
                                {{ tab }}
                            </option>
                        </select> -->

                        <!-- <div class="hidden space-y-2 md:block">
                            <button
                                v-for="tab in tabs"
                                :key="tab"
                                @click="activeTab = tab"
                                :class="[
                                    'w-full text-left px-4 py-1 rounded-md transition',
                                    activeTab === tab
                                        ? 'bg-primary text-white'
                                        : 'bg-gray-100 text-gray-800 hover:bg-gray-200',
                                ]">
                                {{ tab }}
                            </button>
                        </div> -->

                        <div class="mt-0">
                            <label class="block mb-1 text-sm font-medium text-gray-700">Filter by Location</label>
                            <select
                                v-model="selectedLocation"
                                class="w-full px-3 py-1 text-sm border border-gray-300 rounded">
                                <option value="All">All Locations</option>
                                <option
                                    v-for="loc in props.locations"
                                    :key="loc.name"
                                    :value="loc.name">
                                    {{ loc.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <!-- content -->
                    <div class="col-span-1 space-y-12 md:col-span-3">
                        <div
                            v-if="activeTab === 'All'"
                            class="space-y-12">
                            <div v-if="filteredBoardrooms.length">
                                <h3 class="mb-3 text-xl font-semibold">Boardrooms</h3>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    <div
                                        v-for="room in filteredBoardrooms"
                                        :key="room.id"
                                        class="p-4 bg-white rounded shadow">
                                        <h4 class="text-xl text-primary">{{ room.boardroom_name }}</h4>
                                        <p class="text-sm text-gray-500">
                                            <strong>Location:</strong> {{ room.location?.name }}
                                        </p>

                                        <hr class="my-2" />
                                        <div class="flex items-center justify-between space-x-1">
                                            <button
                                                v-if="canShowButton(room)"
                                                @click="goToBoardroom(room.id)"
                                                class="w-full px-4 py-2 mt-2 text-sm text-white transition rounded bg-bluemain hover:bg-bluemain/90">
                                                Select
                                            </button>

                                            <button
                                                v-else
                                                @click="showAvail(room)"
                                                class="w-full px-4 py-2 mt-2 text-sm text-white transition bg-gray-500 rounded hover:bg-gray-600">
                                                View Availability
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="activeTab === 'Boardrooms'">
                            <h3 class="mb-3 text-xl font-semibold">Boardrooms</h3>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div
                                    v-for="room in filteredBoardrooms"
                                    :key="room.id"
                                    class="p-4 bg-white rounded shadow">
                                    <h4 class="font-semibold">{{ room.boardroom_name }}</h4>
                                    <p class="text-sm text-gray-500">Location: {{ room.location?.name }}</p>
                                    <p class="mb-3 text-sm text-gray-500">Seats: {{ room.seats }}</p>
                                    <hr />
                                    <div class="flex items-center justify-between space-x-1">
                                        <button
                                            @click="goToBoardroom(room.id)"
                                            class="px-4 py-2 mt-2 text-sm text-white rounded bg-bluemain hover:bg-bluemain/60">
                                            Select
                                        </button>
                                        <button
                                            class="flex items-center justify-center px-4 py-2 mt-2 text-sm text-white rounded">
                                            <img
                                                src="../../../../../public/files_grits/eye.svg"
                                                alt="Eye Badge"
                                                class="w-6 h-6" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <template v-if="showOfficeModal">
                    <div class="p-5 fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                        <div class="w-full max-w-md p-6 bg-white rounded shadow">
                            <h2 class="mb-4 text-lg font-semibold">View Availability</h2>
                            <hr class="my-2 border-gray-300" />
                            <div class="grid grid-cols-1 gap-6 mb-5">
                                <div
                                    v-if="boardroomAvail"
                                    class="space-y-2 text-sm">
                                    <div class="flex">
                                        <div class="w-[120px] font-medium text-gray-600">Available:</div>
                                        <div :class="boardroomAvail.is_available ? 'text-green-600' : 'text-red-600'">
                                            {{
                                                boardroomAvail.is_available
                                                    ? 'Yes (Based on the below Date.)'
                                                    : 'Unavailable until'
                                            }}
                                        </div>
                                    </div>

                                    <div class="flex">
                                        <div class="w-[120px] font-medium text-gray-600">Availability:</div>
                                        <div>{{ formatDate(boardroomAvail.available_dates) }}</div>
                                    </div>
                                </div>
                            </div>
                            <hr class="my-2 border-gray-300" />
                            <div class="flex justify-end space-x-3">
                                <button
                                    @click="showOfficeModal = false"
                                    class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded hover:bg-gray-200">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <cartOfficeModal
                :show="showAvailModal"
                :can="can"
                :cart="allBookings"
                route-name="/receive/cart"
                :onClose="() => (showAvailModal = false)" />
        </div>
    </AuthenticatedLayout>
</template>
