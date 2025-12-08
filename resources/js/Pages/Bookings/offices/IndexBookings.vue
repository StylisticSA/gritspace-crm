<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { format } from 'date-fns';
import cartOfficeModal from '../../../Components/Modals/Cart/CartOfficeModal.vue';

interface Office {
    id: number;
    office_name: string;
    category?: { name: string };
    location?: { name: string };
    plan: string;
    pricing?: { premium_price?: number; standard_price?: number };
    seats?: number;
    monthly_rate?: number;
    daily_rate?: number;
    is_available: boolean;
    available_dates: Date;
}

interface HotDesk {
    id: number;
    help_desk_name: string;
    location?: { name: string };
    price?: number;
    duration?: string;
    desks?: number;
    discount?: number;
    is_available: boolean;
    available_dates: Date;
}

interface Location {
    name: string;
    address?: string;
    city?: string;
}

interface ApprovedClosed {
    user_id: number;
    office_id: number;
    category_id: number;
    plan: string;
    selected_dates: string[];
    weekdays_count: number;
    months: number;
    start_date: string;
    end_date: string;
    total_price: number;
    parking_price: number;
    status: string;

    office: Office;
}

interface ApprovedDedicated {
    user_id: number;
    office_id: number;
    category_id: number;
    plan: string;
    selected_dates: string[];
    weekdays_count: number;
    months: number;
    start_date: string;
    end_date: string;
    total_price: number;
    parking_price: number;
    status: string;

    office: Office;
}

interface ApprovedHotDeks {
    user_id: number;
    helpdesk_id: number;
    plan: string;
    selected_dates: string[];
    is_half_day: string;
    time_slots: string[];
    selected_price: number;
    days_count: number;
    status: string;

    helpdesk: HotDesk;
}

const props = defineProps<{
    offices: Office[];
    hotDesks: HotDesk[];
    locations: Location[];
    approvedClosed: ApprovedClosed[];
    approvedDedicated: ApprovedDedicated[];
    approvedHotDesk: ApprovedHotDeks[];
    can: Object;
}>();

const pendingCount = computed(
    () =>
        (props.approvedClosed?.length ?? 0) +
        (props.approvedDedicated?.length ?? 0) +
        (props.approvedHotDesk?.length ?? 0)
);

const tabs = ['All', 'Closed Offices', 'Dedicated Desks', 'Hot Desks'];
const activeTab = ref('All');
const selectedLocation = ref('All');

// Filters
const closedOffices = computed(() =>
    props.offices.filter(o => {
        const categoryName = o.category?.name?.toLowerCase().trim();
        const isClosedOffice = categoryName === 'closed office' || categoryName === 'closed offices';

        const locationMatch = selectedLocation.value === 'All' || o.location?.name === selectedLocation.value;

        return isClosedOffice && locationMatch;
    })
);

const dedicatedDesks = computed(() =>
    props.offices.filter(o => {
        const categoryName = o.category?.name?.toLowerCase().trim();
        const isDedicatedDesk = categoryName === 'dedicated desk' || categoryName === 'dedicated desks';

        const locationMatch = selectedLocation.value === 'All' || o.location?.name === selectedLocation.value;

        return isDedicatedDesk && locationMatch;
    })
);

const filteredHotDesks = computed(() =>
    props.hotDesks.filter(help => {
        const locationName = help.location?.name?.toLowerCase().trim();
        const selected = selectedLocation.value.toLowerCase().trim();

        return selected === 'all' || locationName === selected;
    })
);

function goToOffice(officeId: number) {
    router.visit(`/booking-dedicated/${officeId}`);
}

function goToClosed(officeId: number) {
    router.visit(`/booking-closed/${officeId}`);
}

function goToHotDesk(Id: number) {
    router.visit(`/booking-hotdesk/${Id}`);
}

const formatDate = dateStr => {
    return dateStr ? format(new Date(dateStr), 'dd MMM yyyy') : '';
};

const showOfficeModal = ref(false);
const closedAvail = ref<Office | null>(null);

const showAvailModal = ref(false);

const showAvail = office => {
    showOfficeModal.value = true;
    closedAvail.value = office;
};

function canShowButton(entity) {
    if (!entity) return false;

    const today = new Date();
    const startDate = new Date(entity.available_dates);

    return entity.is_available && today >= startDate;
}

const allBookings = computed(() => {
    const closed = props.approvedClosed.map(b => ({
        name: b.office.office_name,
        type: 'Closed Office',
        price: b.total_price,
        plan: b.plan,
        id: b.office_id,
        months: b.months,
        monthly_rate: Number(b.office.monthly_rate),
        daily_rate: Number(b.office.daily_rate),
    }));

    const dedicated = props.approvedDedicated.map(b => ({
        name: b.office.office_name,
        type: 'Dedicated Desk',
        plan: b.plan,
        price: b.total_price,
        id: b.office_id,
    }));

    const hot = props.approvedHotDesk.map(b => ({
        name: b.helpdesk?.help_desk_name,
        type: 'Hot Desk',
        plan: b.plan,
        price: b.selected_price,
        id: b.helpdesk_id,
    }));

    return [...closed, ...dedicated, ...hot];
});
</script>

<template>
    <Head title="Grit Spaces Admin" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Grit Spaces</h2>

                <div
                    class="flex gap-3"
                    v-if="pendingCount > 0">
                    <button
                        @click="showAvailModal = true"
                        type="button"
                        class="px-4 py-2 text-sm font-medium text-white border border-solid rounded-sm bg-primary hover:bg-bluemain/60 focus:outline-none">
                        Payment Pending ({{ pendingCount }})
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="px-6 py-10 mx-auto max-w-7xl">
                    <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                        <!-- LEFT TABS -->
                        <div class="col-span-1">
                            <!-- Mobile Dropdown -->
                            <div class="mb-4 md:hidden">
                                <select
                                    v-model="activeTab"
                                    class="w-full px-3 py-1 text-sm border border-pink-300 rounded focus:outline-none focus:ring">
                                    <option
                                        v-for="tab in tabs"
                                        :key="tab"
                                        :value="tab">
                                        {{ tab }}
                                    </option>
                                </select>
                            </div>

                            <!-- Desktop Vertical Tab Buttons -->
                            <div class="hidden space-y-2 md:block">
                                <button
                                    v-for="tab in tabs"
                                    :key="tab"
                                    @click="activeTab = tab"
                                    :class="[
                                        'w-full text-left px-2 py-1 text-sm rounded-md transition',
                                        activeTab === tab
                                            ? 'bg-primary text-white'
                                            : 'bg-gray-100 text-gray-800 hover:bg-gray-200',
                                    ]">
                                    {{ tab }}
                                </button>
                            </div>

                            <!-- LOCATION FILTER -->
                            <div class="mt-6">
                                <label class="block mb-1 text-sm font-medium text-gray-700">Filter by Location</label>
                                <select
                                    v-model="selectedLocation"
                                    class="w-full px-3 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring">
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

                        <!-- RIGHT VIEW -->
                        <div class="col-span-1 space-y-12 md:col-span-3">
                            <!-- ALL -->
                            <div
                                v-if="activeTab === 'All'"
                                class="space-y-12">
                                <!-- closed -->
                                <div v-if="closedOffices.length">
                                    <h3 class="mb-3 text-xl font-semibold">Closed Offices</h3>
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                        <div
                                            v-for="office in closedOffices"
                                            :key="office.id"
                                            class="p-4 bg-white rounded shadow">
                                            <h3 class="text-xl text-primary">{{ office.office_name }}</h3>

                                            <p class="text-sm text-gray-500">
                                                <strong>Location:</strong> {{ office.location?.name }}
                                            </p>

                                            <hr class="my-2" />
                                            <div class="w-full">
                                                <button
                                                    v-if="canShowButton(office)"
                                                    @click="goToClosed(office.id)"
                                                    class="w-full px-4 py-2 mt-2 text-sm text-white transition rounded bg-bluemain hover:bg-bluemain/90">
                                                    Select
                                                </button>

                                                <button
                                                    v-else
                                                    @click="showAvail(office)"
                                                    class="w-full px-4 py-2 mt-2 text-sm text-white transition bg-gray-500 rounded hover:bg-gray-600">
                                                    View Availability
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- dedicated -->
                                <div v-if="dedicatedDesks.length">
                                    <h3 class="mb-3 text-xl font-semibold">Dedicated Desks</h3>
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                        <div
                                            v-for="desk in dedicatedDesks"
                                            :key="desk.id"
                                            class="p-4 bg-white rounded shadow">
                                            <h3 class="text-xl text-primary">{{ desk.office_name }}</h3>

                                            <p class="text-sm text-gray-500">
                                                <strong>Location:</strong> {{ desk.location?.name }}
                                            </p>

                                            <hr class="my-2" />
                                            <div class="flex items-center justify-between space-x-1">
                                                <button
                                                    v-if="canShowButton(desk)"
                                                    @click="goToOffice(desk.id)"
                                                    class="w-full px-4 py-2 mt-2 text-sm text-white transition rounded bg-bluemain hover:bg-bluemain/90">
                                                    Select
                                                </button>

                                                <button
                                                    v-else
                                                    @click="showAvail(desk)"
                                                    class="w-full px-4 py-2 mt-2 text-sm text-white transition bg-gray-500 rounded hover:bg-gray-600">
                                                    View Availability
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- hot -->
                                <div v-if="filteredHotDesks.length">
                                    <h3 class="mb-3 text-xl font-semibold">Hot Desks</h3>
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                        <div
                                            v-for="desk in filteredHotDesks"
                                            :key="desk.help_desk_name"
                                            class="p-4 bg-white rounded shadow">
                                            <h3 class="text-xl text-primary">{{ desk.help_desk_name }}</h3>

                                            <p class="text-sm text-gray-500">
                                                <strong>Location:</strong> {{ desk.location?.name }}
                                            </p>
                                            <hr class="my-2" />
                                            <div class="flex items-center justify-between space-x-1">
                                                <button
                                                    v-if="canShowButton(desk)"
                                                    @click="goToHotDesk(desk.id)"
                                                    class="w-full px-4 py-2 mt-2 text-sm text-white transition rounded bg-bluemain hover:bg-bluemain/90">
                                                    Select
                                                </button>

                                                <button
                                                    v-else
                                                    @click="showAvail(desk)"
                                                    class="w-full px-4 py-2 mt-2 text-sm text-white transition bg-gray-500 rounded hover:bg-gray-600">
                                                    View Availability
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Closed Offices -->
                            <div v-else-if="activeTab === 'Closed Offices'">
                                <h3 class="mb-4 text-xl font-semibold">Closed Offices</h3>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    <div
                                        v-for="office in closedOffices"
                                        :key="office.id"
                                        class="p-4 bg-white rounded shadow">
                                        <h3 class="text-xl text-primary">{{ office.office_name }}</h3>

                                        <p class="text-sm text-gray-500">
                                            <strong>Location:</strong>{{ office.location?.name }}
                                        </p>

                                        <hr class="my-2" />
                                        <div class="flex items-center justify-between space-x-1">
                                            <button
                                                v-if="canShowButton(office)"
                                                @click="goToClosed(office.id)"
                                                class="w-full px-4 py-2 mt-2 text-sm text-white transition rounded bg-bluemain hover:bg-bluemain/90">
                                                Select
                                            </button>

                                            <button
                                                v-else
                                                @click="showAvail(office)"
                                                class="w-full px-4 py-2 mt-2 text-sm text-white transition bg-gray-500 rounded hover:bg-gray-600">
                                                View Availability
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Dedicated Desks -->
                            <div v-else-if="activeTab === 'Dedicated Desks'">
                                <h3 class="mb-4 text-xl font-semibold">Dedicated Desks</h3>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    <div
                                        v-for="desk in dedicatedDesks"
                                        :key="desk.id"
                                        class="p-4 bg-white rounded shadow">
                                        <h3 class="text-xl text-primary">{{ desk.office_name }}</h3>

                                        <p class="text-sm text-gray-500">
                                            <strong>Location:</strong>{{ desk.location?.name }}
                                        </p>
                                        <hr class="my-2" />
                                        <div class="flex items-center justify-between space-x-1">
                                            <button
                                                v-if="canShowButton(desk)"
                                                @click="goToOffice(desk.id)"
                                                class="w-full px-4 py-2 mt-2 text-sm text-white transition rounded bg-bluemain hover:bg-bluemain/90">
                                                Select
                                            </button>

                                            <button
                                                v-else
                                                @click="showAvail(desk)"
                                                class="w-full px-4 py-2 mt-2 text-sm text-white transition bg-gray-500 rounded hover:bg-gray-600">
                                                View Availability
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Hot Desks -->
                            <div v-else-if="activeTab === 'Hot Desks'">
                                <h3 class="mb-4 text-xl font-semibold">Hot Desks</h3>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    <div
                                        v-for="desk in filteredHotDesks"
                                        :key="desk.help_desk_name"
                                        class="p-4 bg-white rounded shadow">
                                        <h3 class="text-xl text-primary">{{ desk.help_desk_name }}</h3>

                                        <p class="text-sm text-gray-500">
                                            <strong>Location:</strong>{{ desk.location?.name }}
                                        </p>
                                        <hr class="my-2" />
                                        <div class="flex items-center justify-between space-x-1">
                                            <button
                                                v-if="canShowButton(desk)"
                                                @click="goToHotDesk(desk.id)"
                                                class="w-full px-4 py-2 mt-2 text-sm text-white transition rounded bg-bluemain hover:bg-bluemain/90">
                                                Select
                                            </button>

                                            <button
                                                v-else
                                                @click="showAvail(desk)"
                                                class="w-full px-4 py-2 mt-2 text-sm text-white transition bg-gray-500 rounded hover:bg-gray-600">
                                                View Availability
                                            </button>
                                        </div>
                                    </div>
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
                                v-if="closedAvail"
                                class="space-y-2 text-sm">
                                <div class="flex">
                                    <div class="w-[120px] font-medium text-gray-600">Available:</div>
                                    <div :class="closedAvail.is_available ? 'text-green-600' : 'text-red-600'">
                                        {{
                                            closedAvail.is_available
                                                ? 'Yes (Based on the below Date.)'
                                                : 'Not Available'
                                        }}
                                    </div>
                                </div>

                                <div class="flex">
                                    <div class="w-[120px] font-medium text-gray-600">Available on:</div>
                                    <div>{{ formatDate(closedAvail.available_dates) }}</div>
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
            <!-- {{ allBookings }} -->
            <cartOfficeModal
                :show="showAvailModal"
                :can="can"
                :cart="allBookings"
                route-name="/receive/cart"
                :onClose="() => (showAvailModal = false)" />
        </div>
    </AuthenticatedLayout>
</template>
