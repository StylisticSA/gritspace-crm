<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import useStatusMessage from '../../../Composables/useStatusMessage';
import GlobalNoteModal from '@/Components/Modals/NoteModal.vue';
import cartOfficeModal from '../../../Components/Modals/Cart/CartOfficeModal.vue';

const props = defineProps({
    bookings: Object,
    filters: Object,
    users: Object,
    approvedHotDesk: Object,
    can: Object,
});

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();

const showNoteModal = ref(false);
const showViewModal = ref(false);
const showModal = ref(false);
const showDatesModal = ref(false);
const showAvailModal = ref(false);

const selectedBooking = ref(null);
const bookingToDelete = ref(null);
const selectedDates = ref(null);
const isLoading = ref(false);

const search = ref(props.filters?.search ?? '');
watch(search, value => {
    router.get(
        route('bookinghotdesk.show'),
        { search: value },
        {
            preserveState: true,
            replace: true,
            onBefore: () => (isLoading.value = true),
            onFinish: () => (isLoading.value = false),
        }
    );
});

const confirmDelete = id => {
    showModal.value = true;
    bookingToDelete.value = id;
};

const viewDatesModal = booking => {
    selectedDates.value = booking;
    showDatesModal.value = true;
};

const openViewModal = booking => {
    selectedBooking.value = booking;
    showViewModal.value = true;
};

const closeViewModal = () => {
    showViewModal.value = false;
    selectedBooking.value = null;
    window.location.reload();
};

const closeDatesModal = () => {
    showDatesModal.value = false;
    selectedDates.value = null;
};

const paidBooking = id => {
    if (!id) return;

    if (confirm('Are you sure you want to change to Paid?')) {
        router.put(route('hotdeskbooking.paid', id), {
            preserveScroll: true,
            onSuccess: () => {
                message.value = 'Office status changed to Paid';
                status.value = 'success';

                setTimeout(() => {
                    router.reload({ preserveScroll: true });
                }, 2000);
            },
            onError: () => {
                message.value = 'Failed to mark Paid.';
                status.value = 'deleted';
            },
        });
    }
};

const approveBooking = id => {
    if (!id) return;
    router.put(
        route('hotdeskbooking.approve', { hotdesk: id }),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                message.value = 'Booking approved successfully.';
                status.value = 'success';

                setTimeout(() => {
                    router.reload({ preserveScroll: true });
                    closeViewModal();
                }, 2000);
            },
            onError: () => {
                message.value = 'Failed to approve booking.';
                status.value = 'rejected';
            },
        }
    );
};

const rejectBooking = id => {
    router.put(
        route('hotdeskbooking.reject', { hotdesk: id }),
        {},
        {
            onSuccess: () => {
                message.value = 'Booking rejected.';
                status.value = 'rejected';

                setTimeout(() => {
                    router.reload({ preserveScroll: true });
                    closeViewModal();
                }, 2000);
            },
        }
    );
};

const cancelBooking = id => {
    router.put(
        route('hotdeskbooking.cancel', { hotdesk: id }),
        {},
        {
            onSuccess: () => {
                message.value = 'Booking cancelled.';
                status.value = 'cancelled';

                setTimeout(() => {
                    router.reload({ preserveScroll: true });
                    closeViewModal();
                }, 2000);
            },
        }
    );
};

const deleteBooking = () => {
    if (bookingToDelete.value) {
        router.delete(route('hotdesk.destroy', bookingToDelete.value), {
            preserveScroll: true,
            onSuccess: () => {
                message.value = 'Booking rejected successfully.';
                status.value = 'rejected';

                setTimeout(() => {
                    router.reload({ preserveScroll: true });
                    closeViewModal();
                }, 2000);
            },
            onFinish: () => {
                showModal.value = false;
                bookingToDelete.value = null;
            },
        });
    }
};

const splitDates = dates => {
    if (!dates || !Array.isArray(dates)) return [];

    if (dates.length <= 7) return [dates];

    const mid = Math.ceil(dates.length / 2);
    return [dates.slice(0, mid), dates.slice(mid)];
};

// Date formatter
const formatDate = date => {
    if (!date) return '—';
    const d = new Date(date);
    return d.toLocaleDateString('en-ZA', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
};

// Capitalize helper
const capitalize = text => {
    if (!text) return 'N/A';
    return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase();
};

// Pagination label formatter
const formatLabel = label => {
    if (label === '&laquo; Previous') return 'Prev';
    if (label === 'Next &raquo;') return 'Next';
    return label;
};

const formatFixedDate = date => {
    if (!date) return '—';

    // Match date portion only — skips time altogether
    const match = /^(\d{4})-(\d{2})-(\d{2})/.exec(date);
    if (!match) return 'Invalid Date';

    const [_, year, month, day] = match;

    // Create UTC date (no timezone influence)
    const d = new Date(Date.UTC(year, month - 1, day));

    return d.toLocaleDateString('en-ZA', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        timeZone: 'UTC',
    });
};

// Grouped booking dates by month and year
const groupedModalDates = computed(() => {
    const isHalfDay = selectedDates.value?.is_half_day === 1;

    const source = isHalfDay
        ? Object.entries(selectedDates.value?.time_slots || {})
        : selectedDates.value?.selected_dates || [];

    const groups = {};

    source.forEach(entry => {
        const dateStr = isHalfDay ? entry[0] : entry;
        const date = new Date(dateStr);
        const key = date.toLocaleString('default', { month: 'long', year: 'numeric' });

        if (!groups[key]) groups[key] = [];
        groups[key].push(entry);
    });

    return groups;
});

const pendingCount = computed(() => props.approvedHotDesk?.length ?? 0);

const allBookings = computed(() => {
    const hot = props.approvedHotDesk.map(b => ({
        name: b.helpdesk?.help_desk_name,
        type: 'Hot Desk',
        plan: b.plan,
        price: b.selected_price,
        id: b.helpdesk_id,
    }));

    return [...hot];
});
</script>

<template>
    <Head title="Hot Desks Bookings" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Booked Hot Desks</h2>
                <div class="flex gap-3">
                    <div v-if="pendingCount > 0">
                        <button
                            @click="showAvailModal = true"
                            type="button"
                            class="px-4 py-2 text-lg text-lgd text-white border border-solid rounded bg-primary hover:bg-bluemain/60 focus:outline-none">
                            Payment Pending ({{ pendingCount }})
                        </button>
                    </div>
                    <button
                        @click="showNoteModal = true"
                        class="px-2 py-2 text-lg text-white rounded bg-bluemain hover:bluemain/60">
                        Add Note
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-full px-4 mx-auto sm:max-w-xl sm:px-6 lg:max-w-7xl lg:px-8">
                <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-col gap-1 sm:flex-row sm:space-x-1">
                        <Link
                            :href="route('booking.offices')"
                            class="inline-block w-full px-4 py-2 text-xs font-medium text-center text-white rounded bg-primary hover:bg-bluemain sm:w-auto sm:text-sm">
                            Book
                        </Link>

                        <Link
                            v-if="can['manage']"
                            :href="route('hotdesk.deleted')"
                            class="inline-block w-full px-4 py-2 text-xs font-medium text-center text-white rounded bg-bluemain hover:bg-bluemain sm:w-auto sm:text-sm">
                            Deleted
                        </Link>
                    </div>

                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search bookings..."
                        class="w-full px-4 py-2 text-xs border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 sm:w-48 sm:text-sm" />
                </div>

                <!-- Bookings Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-300 divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th
                                    v-if="can['manage settings']"
                                    class="px-6 py-3 text-sm font-medium text-left text-gray-700">
                                    Booked By
                                </th>
                                <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Hot Desk Name</th>
                                <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Plan</th>
                                <th
                                    class="w-1 px-1 py-3 text-sm font-medium text-center text-gray-700 whitespace-nowrap"></th>
                                <th class="px-6 py-3 text-sm font-medium text-left text-gray-700"># Days</th>
                                <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Status</th>
                                <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Booked At</th>
                                <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="bg-white divide-y divide-gray-200 mt-5">
                            <tr
                                v-for="(booking, index) in bookings.data"
                                :key="booking.id">
                                <td
                                    v-if="can['manage settings']"
                                    class="px-6 py-4 text-sm text-gray-800">
                                    {{ booking.user?.name ?? '—' }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-800">
                                    {{ booking.helpdesk?.help_desk_name ?? '—' }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-800">
                                    {{ booking.plan ?? '—' }}
                                </td>

                                <!-- Icon Button -->
                                <td class="px-2 py-4 text-sm text-center align-middle">
                                    <button
                                        @click.stop="viewDatesModal(booking)"
                                        class="inline-flex items-center justify-center text-white rounded bg-primary w-7 h-7 hover:bg-bluemain/70"
                                        title="View Selected Dates">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="w-4 h-4"
                                            viewBox="0 0 24 24"
                                            fill="currentColor">
                                            <path
                                                d="M7 10h2v2H7v-2Zm4 0h2v2h-2v-2Zm4 0h2v2h-2v-2ZM5 21a2 2 0 0 1-2-2V7h18v12a2 2 0 0 1-2 2H5ZM5 5V3h2v2h10V3h2v2h-2v2H7V5H5Zm0 4v10h14V9H5Z" />
                                        </svg>
                                    </button>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-800">
                                    {{ booking.days_count ?? 'N/A' }}
                                </td>

                                <td class="px-6 py-4 text-sm">
                                    <span
                                        :class="{
                                            'px-2 py-1 rounded text-xs font-semibold capitalize': true,
                                            'bg-yellow-100 text-yellow-800': booking.status === 'pending',
                                            'bg-green-100 text-green-800': booking.status === 'approved',
                                            'bg-gray-200 text-gray-700': booking.status === 'cancelled',
                                            'bg-red-100 text-red-700': booking.status === 'rejected',
                                            'bg-red-100 text-primary': booking.status === 'paid',
                                        }">
                                        {{ booking.status ?? 'N/A' }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-800">
                                    {{ formatDate(booking.created_at) ?? 'N/A' }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-800">
                                    <div class="flex space-x-1 text-center">
                                        <button
                                            @click="openViewModal(booking)"
                                            :disabled="booking.status === 'paid' && !can['manage settings']"
                                            class="px-2 py-1 text-sm text-white rounded bg-primary hover:bg-bluemain disabled:opacity-50 disabled:cursor-not-allowed">
                                            Action
                                        </button>

                                        <button
                                            v-if="can['edit']"
                                            @click="openEditModal(booking)"
                                            class="px-2 py-1 text-sm text-white rounded bg-bluemain hover:bg-bluemain/60">
                                            Edit
                                        </button>
                                        <button
                                            v-if="can['deleteme']"
                                            @click="confirmDelete(booking.id)"
                                            class="px-1 py-1 text-sm text-white bg-red-500 rounded hover:bg-red-600">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex items-center justify-between mt-4">
                    <div class="text-sm text-gray-600">
                        Showing <span class="font-medium">{{ bookings.from }}</span> to
                        <span class="font-medium">{{ bookings.to }}</span> of
                        <span class="font-medium">{{ bookings.total }}</span> results
                    </div>

                    <div class="flex space-x-1">
                        <template
                            v-for="(link, index) in bookings.links"
                            :key="index">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-bluemain/60 hover:text-white"
                                :class="link.active ? 'bg-bluemain text-white' : 'text-gray-700'"
                                v-html="formatLabel(link.label)" />
                            <span
                                v-else
                                class="px-3 py-1 text-sm text-gray-400 border border-gray-300 rounded-md cursor-not-allowed"
                                v-html="formatLabel(link.label)" />
                        </template>
                    </div>
                </div>

                <!-- Selected Dates Modal -->
                <template v-if="showDatesModal">
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                        <div
                            class="w-full max-w-4xl mx-3 p-6 bg-white rounded-lg shadow-lg overflow-y-auto max-h-[80vh]">
                            <!-- Modal Header -->
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-bold text-gray-800">
                                    Selected Booking Dates for {{ selectedDates.plan }}
                                </h2>
                                <button
                                    @click="closeDatesModal"
                                    class="text-2xl leading-none text-gray-500 hover:text-gray-700">
                                    &times;
                                </button>
                            </div>

                            <!-- Scrollable Grid -->
                            <div
                                v-for="(groupItems, groupLabel) in groupedModalDates"
                                :key="groupLabel"
                                class="mb-6">
                                <h3 class="mb-2 font-semibold text-gray-700 text-md">{{ groupLabel }}</h3>

                                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                                    <div
                                        v-for="(item, index) in groupItems"
                                        :key="selectedDates.is_half_day === 1 ? item[0] : index"
                                        class="flex flex-col px-3 py-2 text-sm text-center bg-gray-100 border rounded shadow-sm">
                                        <template v-if="selectedDates.is_half_day === 1">
                                            <div>{{ formatFixedDate(item[0]) }}</div>
                                            <div class="mt-1 text-xs text-gray-600">
                                                {{ capitalize(item[1].block) }}
                                            </div>
                                        </template>
                                        <template v-else>
                                            <div>{{ formatFixedDate(item) }}</div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Footer -->
                            <div class="mt-6 text-right">
                                <button
                                    @click="closeDatesModal"
                                    class="px-4 py-2 text-sm text-gray-800 bg-gray-100 rounded hover:bg-gray-200">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <template v-if="showModal">
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                        <div class="w-full max-w-md p-6 bg-white rounded shadow">
                            <h2 class="mb-4 text-lg font-semibold">Confirm Delete</h2>
                            <p class="mb-6">
                                Are you sure you want to delete this booking? This action cannot be undone.
                            </p>
                            <form @submit.prevent>
                                <div class="mb-4">
                                    <label
                                        for="restoreReason"
                                        class="block mb-1 text-sm font-medium text-gray-700">
                                        Reason for Delete
                                    </label>
                                    <textarea
                                        id="restoreReason"
                                        v-model="restoreReason"
                                        rows="4"
                                        class="w-full p-3 text-sm border rounded-md resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Enter reason..."></textarea>
                                </div>
                            </form>
                            <div class="flex justify-end space-x-3">
                                <button
                                    @click="showModal = false"
                                    class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded hover:bg-gray-200">
                                    Cancel
                                </button>
                                <button
                                    @click="deleteBooking"
                                    class="px-4 py-2 text-sm text-white bg-red-600 rounded hover:bg-red-700">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- View the Booking -->
                <template v-if="showViewModal && selectedBooking">
                    <div class="fixed inset-0 z-50 flex items-center justify-center p-5 bg-black bg-opacity-50">
                        <div class="w-full max-w-xl p-6 bg-white rounded-lg shadow-lg">
                            <!-- Modal Header -->
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-bold text-gray-800 mb-5">
                                    {{ selectedBooking.plan }}
                                </h2>
                                <button
                                    @click="closeViewModal"
                                    class="text-2xl leading-none text-gray-500 hover:text-gray-700">
                                    &times;
                                </button>
                            </div>

                            <template v-if="showMessage">
                                <div :class="messageClass">
                                    {{ messageText }}
                                </div>
                            </template>

                            <!-- Modal Content -->
                            <div class="grid grid-cols-1 gap-6 text-sm text-gray-700 sm:grid-cols-2">
                                <!-- General Info Table Style -->
                                <div class="space-y-2">
                                    <div class="grid grid-cols-2 gap-x-3 items-start">
                                        <div
                                            v-if="can['manage settings']"
                                            class="mb-3 font-medium text-gray-600">
                                            <strong>Booking ID:</strong>
                                        </div>
                                        <div
                                            v-if="can['manage settings']"
                                            class="mb-2">
                                            {{ selectedBooking.id }}
                                        </div>

                                        <div
                                            v-if="can['manage settings']"
                                            class="mb-3 font-medium text-gray-600">
                                            <strong>User:</strong>
                                        </div>
                                        <div
                                            v-if="can['manage settings']"
                                            class="mb-2">
                                            {{ selectedBooking.user?.name ?? '—' }}
                                        </div>

                                        <div class="mb-3 font-medium text-gray-600"><strong>Plan:</strong></div>
                                        <div class="mb-2">{{ selectedBooking.plan }}</div>

                                        <div class="mb-3 font-medium text-gray-600">
                                            <strong>Number of Days:</strong>
                                        </div>
                                        <div class="mb-2">{{ selectedBooking.days_count ?? '—' }}</div>

                                        <div class="mb-3 font-medium text-gray-600"><strong>Total Price:</strong></div>
                                        <div class="mb-2">R {{ selectedBooking.selected_price ?? '0.00' }}</div>

                                        <div class="mb-3 font-medium text-gray-600"><strong>Status: </strong></div>
                                        <div>
                                            <span
                                                :class="{
                                                    'px-2 py-1 rounded text-xs font-semibold capitalize': true,
                                                    'bg-yellow-100 text-yellow-800':
                                                        selectedBooking.status === 'pending',
                                                    'bg-green-100 text-green-800':
                                                        selectedBooking.status === 'approved',
                                                    'bg-gray-200 text-gray-700': selectedBooking.status === 'cancelled',
                                                    'bg-red-100 text-red-700': selectedBooking.status === 'rejected',
                                                    'bg-red-100 text-primary': selectedBooking.status === 'paid',
                                                }">
                                                {{ selectedBooking.status ?? 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Footer -->
                            <div class="flex flex-col gap-3 mt-6 sm:flex-row sm:justify-between sm:gap-4">
                                <button
                                    v-if="can['manage settings']"
                                    @click="paidBooking(selectedBooking.id)"
                                    v-show="selectedBooking.status === 'approved'"
                                    class="flex-1 px-4 py-1 text-xs text-white bg-primary rounded hover:bg-bluemain/60 disabled:opacity-50 disabled:cursor-not-allowed">
                                    Paid
                                </button>

                                <button
                                    v-if="can['manage settings']"
                                    @click="approveBooking(selectedBooking.id)"
                                    :disabled="selectedBooking.status === 'approved'"
                                    class="flex-1 px-4 py-1 text-xs text-white bg-green-600 rounded hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    Approve
                                </button>

                                <button
                                    v-if="can['manage settings']"
                                    @click="rejectBooking(selectedBooking.id)"
                                    :disabled="selectedBooking.status === 'rejected'"
                                    class="flex-1 px-4 py-1 text-xs text-white bg-red-600 rounded hover:bg-red-700">
                                    Reject
                                </button>

                                <button
                                    @click="cancelBooking(selectedBooking.id)"
                                    :disabled="selectedBooking.status === 'cancelled'"
                                    class="flex-1 px-2 py-1 text-xs text-white bg-gray-600 rounded hover:bg-gray-700">
                                    Cancel
                                </button>

                                <button
                                    @click="closeViewModal"
                                    class="flex-1 px-4 py-2 text-sm text-gray-800 bg-gray-100 rounded hover:bg-gray-200">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <GlobalNoteModal
                    :users="users"
                    :show="showNoteModal"
                    :onClose="() => (showNoteModal = false)" />

                <cartOfficeModal
                    :show="showAvailModal"
                    :can="can"
                    :cart="allBookings"
                    route-name="/receive/cart"
                    :onClose="() => (showAvailModal = false)" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
