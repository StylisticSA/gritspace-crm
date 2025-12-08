<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    bookings: Object,
    filters: Object,
    can: Object,
});

const page = usePage();

// Flash messages
const successMessage = ref(null);
const messageType = ref(null);

const flashMessage = computed(() => page.props?.flash?.success || null);
const showMessage = computed(() => !!(flashMessage.value?.trim?.() || successMessage.value?.trim?.()));

watch(showMessage, msg => {
    if (msg) {
        setTimeout(() => {
            successMessage.value = null;
            messageType.value = null;
            page.props.flash.success = null;
        }, 1500);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
});

// Booking actions
const approveBooking = id => {
    if (!id) return;
    router.put(
        route('bookingvirtual.approve', { virtual: id }),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                successMessage.value = 'Booking approved successfully.';
                messageType.value = 'success';
                closeViewModal();
            },
            onError: () => {
                successMessage.value = 'Failed to approve booking.';
                messageType.value = 'rejected';
            },
        }
    );
};

const rejectBooking = id => {
    router.put(
        route('bookingvirtual.reject', { virtual: id }),
        {},
        {
            onSuccess: () => {
                successMessage.value = 'Booking rejected.';
                messageType.value = 'rejected';
                closeViewModal();
            },
        }
    );
};

const cancelBooking = id => {
    router.put(
        route('bookingvirtual.cancel', { virtual: id }),
        {},
        {
            onSuccess: () => {
                successMessage.value = 'Booking cancelled.';
                messageType.value = 'cancelled';
                closeViewModal();
            },
        }
    );
};

// Search logic
const search = ref(props.filters?.search ?? '');
const isLoading = ref(false);

watch(search, value => {
    router.get(
        route('admin.bookings'),
        { search: value },
        {
            preserveState: true,
            replace: true,
            onBefore: () => (isLoading.value = true),
            onFinish: () => (isLoading.value = false),
        }
    );
});

// Delete logic
const showModal = ref(false);
const bookingToDelete = ref(null);

const confirmDelete = id => {
    showModal.value = true;
    bookingToDelete.value = id;
};

const deleteBooking = () => {
    if (bookingToDelete.value) {
        router.delete(route('admin.bookings.destroy', bookingToDelete.value), {
            preserveScroll: true,
            onSuccess: () => {
                successMessage.value = 'Booking rejected successfully.';
            },
            onFinish: () => {
                showModal.value = false;
                bookingToDelete.value = null;
            },
        });
    }
};

// Modal logic
const showViewModal = ref(false);
const selectedBooking = ref(null);
const selectedDates = ref(null);
const showDatesModal = ref(false);

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
    selectedDates.value = null;
};

// Helpers
const splitDates = dates => {
    if (!dates || !Array.isArray(dates)) return [];
    if (dates.length <= 7) return [dates];
    const mid = Math.ceil(dates.length / 2);
    return [dates.slice(0, mid), dates.slice(mid)];
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

const getDateKey = date => {
    const d = new Date(date);
    return d.toISOString().split('T')[0]; // Ensures 'YYYY-MM-DD' format
};

// Grouped booking dates by month and year
const groupedDates = computed(() => {
    if (!selectedDates.value?.selected_dates) return {};
    return selectedDates.value.selected_dates.reduce((acc, dateStr) => {
        const date = new Date(dateStr);
        const key = date.toLocaleString('default', { month: 'long', year: 'numeric' });
        (acc[key] ||= []).push(dateStr);
        return acc;
    }, {});
});
</script>

<template>
    <Head title="Virtual Bookings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Booked Virtual Offices</h2>
        </template>

        <div class="py-12">
            <div class="max-w-full px-4 mx-auto sm:max-w-xl sm:px-6 lg:max-w-7xl lg:px-8">
                <div
                    v-if="showMessage"
                    :class="[
                        'p-3 mb-4 rounded text-sm font-medium',
                        messageType === 'success'
                            ? 'bg-green-100 text-green-800'
                            : messageType === 'rejected'
                              ? 'bg-red-100 text-red-700'
                              : messageType === 'cancelled'
                                ? 'bg-gray-200 text-gray-700'
                                : 'bg-blue-100 text-blue-800',
                    ]">
                    {{ flashMessage || successMessage }}
                </div>

                <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <Link
                            :href="route('virtual.home')"
                            class="inline-block w-full px-4 py-2 text-sm font-medium text-center text-white rounded md:w-auto bg-primary hover:bg-bluemain">
                            Book
                        </Link>
                    </div>
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
                                <!-- <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">ID</th> -->
                                <th
                                    v-if="can['manage settings']"
                                    class="px-6 py-3 text-sm font-medium text-left text-gray-700">
                                    Booked By
                                </th>
                                <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Virtual Office</th>
                                <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Start Date</th>
                                <th
                                    class="w-1 px-1 py-3 text-sm font-medium text-center text-gray-700 whitespace-nowrap"></th>
                                <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">End Date</th>
                                <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Status</th>
                                <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Booked At</th>
                                <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="(booking, index) in bookings.data"
                                :key="booking.id">
                                <!-- <td class="px-6 py-4 text-sm text-gray-800">{{ index + 1 }}</td> -->

                                <td
                                    v-if="can['manage settings']"
                                    class="px-6 py-4 text-sm text-gray-800">
                                    {{ booking.user?.name ?? '—' }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-800">
                                    {{ booking.virtual_office?.virtualoffice_name ?? '—' }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-800">
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
                                            class="px-2 py-1 text-sm text-white rounded bg-primary hover:bg-bluemain/60">
                                            View
                                        </button>
                                        <button
                                            v-if="can['edit bookings']"
                                            @click="openEditModal(booking)"
                                            class="px-2 py-1 text-sm text-white rounded bg-bluemain hover:bg-bluemain/60">
                                            Edit
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
                <template v-if="showDatesModal && selectedDates?.selected_dates?.length">
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                        <div
                            class="w-full max-w-4xl mx-3 p-6 bg-white rounded-lg shadow-lg overflow-y-auto max-h-[80vh]">
                            <!-- Modal Header -->
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-bold text-gray-800">Selected Booking Dates</h2>
                                <button
                                    @click="closeViewModal"
                                    class="text-2xl leading-none text-gray-500 hover:text-gray-700">
                                    &times;
                                </button>
                            </div>

                            <!-- Grouped Dates -->
                            <div
                                v-for="(dates, group) in groupedDates"
                                :key="group"
                                class="mb-6">
                                <h3 class="mb-2 font-semibold text-gray-700 text-md">{{ group }}</h3>

                                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                                    <div
                                        v-for="(date, index) in dates"
                                        :key="index"
                                        class="px-3 py-2 text-sm text-center bg-gray-100 border rounded shadow-sm">
                                        {{ formatDate(date) }}
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Footer -->
                            <div class="mt-6 text-right">
                                <button
                                    @click="closeViewModal"
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
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
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

                            <!-- Modal Content -->
                            <div class="grid grid-cols-1 gap-6 text-sm text-gray-700 sm:grid-cols-2">
                                <!-- General Info Table Style -->
                                <div class="space-y-2">
                                    <div class="grid grid-cols-2 gap-x-2 items-start">
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

                                        <div>{{ selectedBooking.virtual_office?.virtualoffice_name ?? '—' }}</div>

                                        <div class="mb-3 font-medium text-gray-600"><strong>Start Date:</strong></div>
                                        <div>{{ formatDate(selectedBooking.start_date) }}</div>

                                        <div class="mb-3 font-medium text-gray-600"><strong>End Date:</strong></div>
                                        <div>{{ formatDate(selectedBooking.end_date) }}</div>

                                        <div class="mb-3 font-medium text-gray-600">
                                            <strong># of Months:</strong>
                                        </div>
                                        <div>{{ selectedBooking.months ?? '—' }}</div>

                                        <div class="mb-3 font-medium text-gray-600"><strong>Total Price:</strong></div>
                                        <div>R {{ selectedBooking.selected_price ?? '0.00' }}</div>

                                        <div class="mb-3 font-medium text-gray-600"><strong>Booked Date:</strong></div>
                                        <div>
                                            {{ formatDate(selectedBooking.created_at) }}
                                        </div>

                                        <div class="mb-3 font-medium text-gray-600"><strong>Status</strong></div>

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
                                    @click="approveBooking(selectedBooking.id)"
                                    class="flex-1 px-4 py-1 text-xs text-white bg-green-600 rounded hover:bg-green-700">
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
            </div>
        </div>
    </AuthenticatedLayout>
</template>
