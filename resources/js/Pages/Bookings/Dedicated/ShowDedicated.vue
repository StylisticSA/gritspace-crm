<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { eachDayOfInterval } from 'date-fns';
import useStatusMessage from '../../../Composables/useStatusMessage';
import GlobalNoteModal from '@/Components/Modals/NoteModal.vue';

const props = defineProps({
    bookings: Object,
    filters: Object,
    users: Object,
    can: Object,
});

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();

const isLoading = ref(false);
const showNoteModal = ref(false);

const showDatesModal = ref(false);
const selectedDates = ref(null);

const viewDatesModal = booking => {
    selectedDates.value = booking;
    showDatesModal.value = true;
};

const closeDateModal = () => {
    showViewModal.value = false;
    selectedBooking.value = null;
    selectedDates.value = null;
};

const search = ref(props.filters?.search ?? '');
watch(search, value => {
    router.get(
        route('bookingdedicated.show'),
        { search: value },
        {
            preserveState: true,
            replace: true,
            onBefore: () => (isLoading.value = true),
            onFinish: () => (isLoading.value = false),
        }
    );
});

const showModal = ref(false);
const bookingToDelete = ref(null);

const deleteBooking = () => {
    if (bookingToDelete.value) {
        router.delete(route('admin.bookings.destroy', bookingToDelete.value), {
            preserveScroll: true,
            onSuccess: () => {
                message.value = 'Booking rejected successfully.';
            },
            onFinish: () => {
                showModal.value = false;
                bookingToDelete.value = null;
            },
        });
    }
};

const showViewModal = ref(false);
const selectedBooking = ref(null);

const openViewModal = booking => {
    selectedBooking.value = booking;
    showViewModal.value = true;
};

const closeViewModal = () => {
    showViewModal.value = false;
    selectedBooking.value = null;
    window.location.reload();
};

const formatDate = date => {
    if (!date) return '—';
    const d = new Date(date);
    return d.toLocaleDateString('en-ZA', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
};

const formatLabel = label => {
    if (label === '&laquo; Previous') return 'Prev';
    if (label === 'Next &raquo;') return 'Next';
    return label;
};

const splitDates = dates => {
    if (!Array.isArray(dates)) return [[]];
    if (dates.length <= 7) return [dates];
    const half = Math.ceil(dates.length / 2);
    return [dates.slice(0, half), dates.slice(half)];
};

const paidBooking = id => {
    if (!id) return;

    if (confirm('Are you sure you want to change to Paid?')) {
        router.put(route('bookingdedicated.paid', id), {
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
        route('bookingdedicated.approve', id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                message.value = 'Booking approved successfully.';
                status.value = 'success';
            },
            onError: () => {
                message.value = 'Failed to approve booking.';
                status.value = 'deleted';
            },
        }
    );
};

const rejectBooking = id => {
    if (!id) return;
    if (confirm('Are you sure you want to reject this booking?')) {
        router.put(
            route('bookingdedicated.reject', id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    message.value = 'Booking rejected.';
                    status.value = 'deleted';
                    closeViewModal();
                },
                onError: () => {
                    message.value = 'Failed to reject booking.';
                    status.value = 'deleted';
                },
            }
        );
    }
};

const cancelBooking = id => {
    if (!id) return;
    if (confirm('Cancel this booking?')) {
        router.put(
            route('bookingdedicated.cancel', id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    message.value = 'Booking cancelled.';
                    status.value = 'cancelled';
                    closeViewModal();
                },
                onError: () => {
                    message.value = 'Failed to cancel booking.';
                    status.value = 'deleted';
                },
            }
        );
    }
};

const computedBookingDates = computed(() => {
    if (!selectedDates.value?.start_date || !selectedDates.value?.end_date) return [];
    const start = new Date(selectedDates.value.start_date);
    const end = new Date(selectedDates.value.end_date);
    return eachDayOfInterval({ start, end });
});

function groupByMonth(dates) {
    const grouped = {};
    dates.forEach(date => {
        const key = date.toLocaleString('default', { month: 'long', year: 'numeric' });
        if (!grouped[key]) grouped[key] = [];
        grouped[key].push(date);
    });
    return grouped;
}
</script>

<template>
    <Head title="Dedicated Desks Bookings" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between space-x-5">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Booked Dedicated Desks</h2>

                <button
                    @click="showNoteModal = true"
                    class="px-2 py-2 text-lg text-white rounded bg-bluemain hover:bluemain/60">
                    Add Note
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-full px-4 mx-auto sm:max-w-xl sm:px-6 lg:max-w-7xl lg:px-8">
                <!-- Search & Filters -->
                <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                    <Link
                        :href="route('booking.offices')"
                        class="inline-block w-full px-4 py-2 text-sm font-medium text-center text-white rounded md:w-auto bg-primary hover:bg-bluemain">
                        Back
                    </Link>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search bookings..."
                        class="w-full px-4 py-2 text-sm border border-gray-300 rounded-md shadow-sm sm:w-48 focus:outline-none focus:ring-2 focus:ring-blue-500" />
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
                                <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Office Name</th>
                                <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Start Date</th>
                                <th class="px-6 py-3 text-sm font-medium text-left text-gray-700"></th>
                                <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">End Date</th>
                                <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Status</th>
                                <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="booking in bookings.data"
                                :key="booking.id">
                                <td
                                    v-if="can['manage settings']"
                                    class="px-6 py-4 text-sm text-gray-800">
                                    {{ booking.user?.name ?? '—' }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-800">
                                    {{ booking.office?.office_name ?? '—' }}
                                </td>

                                <td class="py-4 text-sm text-gray-800 lg:px-6">
                                    {{ formatDate(booking.start_date) ?? '—' }}
                                </td>
                                <!-- Icon Button -->
                                <td class="px-2 py-4 text-sm text-center align-middle">
                                    <button
                                        @click="viewDatesModal(booking)"
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
                                    {{ formatDate(booking.end_date) ?? 'N/A' }}
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
                                    <div class="flex space-x-1 text-center">
                                        <button
                                            @click="openViewModal(booking)"
                                            :disabled="booking.status === 'paid' && !can['manage settings']"
                                            class="px-2 py-1 text-sm text-white rounded bg-primary hover:bg-bluemain disabled:opacity-50 disabled:cursor-not-allowed">
                                            Action
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
                        Showing
                        <span class="font-medium">{{ bookings.from }}</span>
                        to
                        <span class="font-medium">{{ bookings.to }}</span>
                        of
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

                <!-- Delete Confirmation Modal -->
                <template v-if="showModal">
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                        <div class="w-full max-w-md p-6 bg-white rounded shadow">
                            <h2 class="mb-4 text-lg font-semibold">Confirm Delete</h2>
                            <p class="mb-6">
                                Are you sure you want to delete this booking? This action cannot be undone.
                            </p>
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
                    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
                        <div class="w-full max-w-xl p-6 bg-white rounded-lg shadow-lg">
                            <!-- Modal Header -->
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-bold text-gray-800">Booking Details</h2>
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
                            <div class="grid grid-cols-1 gap-2 text-sm text-gray-700">
                                <!-- General Info Table Style -->
                                <div class="space-y-2">
                                    <div class="grid items-start grid-cols-2 gap-x-1">
                                        <div
                                            v-if="can['manage settings']"
                                            class="mb-3 font-medium text-gray-600">
                                            <strong>Booking ID:</strong>
                                        </div>
                                        <div v-if="can['manage settings']">{{ selectedBooking.id }}</div>

                                        <div
                                            v-if="can['manage settings']"
                                            class="mb-3 font-medium text-gray-600">
                                            <strong>User:</strong>
                                        </div>
                                        <div v-if="can['manage settings']">{{ selectedBooking.user?.name ?? '—' }}</div>

                                        <div class="mb-3 font-medium text-gray-600"><strong>Office Name:</strong></div>
                                        <div>{{ selectedBooking.office?.office_name ?? '—' }}</div>

                                        <div class="mb-3 font-medium text-gray-600"><strong>Start Date:</strong></div>
                                        <div>{{ formatDate(selectedBooking.start_date) }}</div>

                                        <div class="mb-3 font-medium text-gray-600"><strong>End Date:</strong></div>
                                        <div>{{ formatDate(selectedBooking.end_date) }}</div>

                                        <div class="mb-3 font-medium text-gray-600">
                                            <strong>Duration:</strong>
                                        </div>
                                        <div>{{ selectedBooking.months ?? '—' }}</div>

                                        <div class="mb-3 font-medium text-gray-600"><strong>Total Price:</strong></div>
                                        <div>R {{ selectedBooking.total_price ?? '0.00' }}</div>

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

                                <!-- Selected Dates Column -->
                                <div
                                    v-if="selectedBooking.selected_dates?.length"
                                    class="space-y-2">
                                    <p class="font-medium text-gray-600">Selected Dates:</p>
                                    <div
                                        :class="{
                                            'grid grid-cols-1': selectedBooking.selected_dates.length <= 7,
                                            'grid grid-cols-2 gap-x-6': selectedBooking.selected_dates.length > 7,
                                        }">
                                        <ul
                                            v-for="(col, colIndex) in splitDates(selectedBooking.selected_dates)"
                                            :key="colIndex"
                                            class="space-y-1 list-disc list-inside">
                                            <li
                                                v-for="(date, index) in col"
                                                :key="index">
                                                {{ formatDate(date) }}
                                            </li>
                                        </ul>
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
                                    class="flex-1 px-4 py-1 text-xs text-white bg-red-600 rounded hover:bg-red-700">
                                    Reject
                                </button>

                                <button
                                    @click="cancelBooking(selectedBooking.id)"
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

                <!-- Selected Dates Modal -->
                <template v-if="showDatesModal && computedBookingDates.length">
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                        <div
                            class="w-full max-w-4xl mx-3 p-6 bg-white rounded-lg shadow-lg overflow-y-auto max-h-[80vh]">
                            <!-- Modal Header -->
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-bold text-gray-800">Selected Booking Dates</h2>
                                <button
                                    @click="closeDateModal"
                                    class="text-2xl leading-none text-gray-500 hover:text-gray-700">
                                    &times;
                                </button>
                            </div>

                            <!-- Grouped Dates -->
                            <div class="space-y-6 max-h-[350px] overflow-y-auto">
                                <template
                                    v-for="(dates, month) in groupByMonth(computedBookingDates)"
                                    :key="month">
                                    <h3 class="mb-2 font-semibold text-gray-700 text-md">{{ month }}</h3>
                                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                                        <div
                                            v-for="(date, index) in dates"
                                            :key="index"
                                            class="px-3 py-2 text-sm text-center bg-gray-100 border rounded shadow-sm">
                                            {{ formatDate(date) }}
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Modal Footer -->
                            <div class="mt-6 text-right">
                                <button
                                    @click="closeDateModal"
                                    class="px-4 py-2 text-sm text-gray-800 bg-gray-100 rounded hover:bg-gray-200">
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
            </div>
        </div>
    </AuthenticatedLayout>
</template>
