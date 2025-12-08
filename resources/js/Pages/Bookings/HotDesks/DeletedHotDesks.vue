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
const showViewModal = ref(false);
const selectedBooking = ref(null);
const selectedDates = ref(null);
const showDatesModal = ref(false);

// Delete logic (e.g. Reject)
const showModal = ref(false);
const bookingToDelete = ref(null);

const confirmDelete = id => {
    showModal.value = true;
    bookingToDelete.value = id;
};

const viewDatesModal = booking => {
    selectedDates.value = booking;
    showDatesModal.value = true;
};

const closeViewModal = () => {
    showViewModal.value = false;
    selectedBooking.value = null;
    selectedDates.value = null;
};

// Flash messages
const successMessage = ref(null);
const messageType = ref(null);

const flashMessage = computed(() => page.props?.flash?.success || null);

const showMessage = computed(() => !!(flashMessage.value?.trim?.() || successMessage.value?.trim?.()));

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

// Watch and reset logic
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

const deleteBooking = () => {
    if (bookingToDelete.value) {
        router.put(route('hotdesk.restore', bookingToDelete.value), {
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
</script>

<template>
    <Head title="Hot Desks Bookings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Booked Hot Desks</h2>
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
                    <div class="space-x-1">
                        <Link
                            :href="route('booking.offices')"
                            class="inline-block w-full px-4 py-2 text-sm font-medium text-center text-white rounded md:w-auto bg-primary hover:bg-bluemain">
                            Book
                        </Link>

                        <Link
                            v-if="can['manage settings']"
                            :href="route('bookinghotdesk.show')"
                            class="inline-block w-full px-4 py-2 text-sm font-medium text-center text-white rounded md:w-auto bg-bluemain hover:bg-bluemain">
                            Booked
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
                                <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">ID</th>
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

                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="(booking, index) in bookings.data"
                                :key="booking.id">
                                <td class="px-6 py-4 text-sm text-gray-800">{{ index + 1 }}</td>

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
                                            v-if="can['delete permissions'] || can['manage settings']"
                                            @click="confirmDelete(booking.id)"
                                            class="px-1 py-1 text-sm text-white rounded bg-muted hover:bg-muted/60">
                                            Restore
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
                                <h2 class="text-lg font-bold text-gray-800">
                                    Selected Booking Dates for {{ selectedDates.plan }}
                                </h2>
                                <button
                                    @click="closeViewModal"
                                    class="text-2xl leading-none text-gray-500 hover:text-gray-700">
                                    &times;
                                </button>
                            </div>

                            <!-- Scrollable Grid -->
                            <div
                                class="grid gap-3 grid-cols-2 sm:grid-cols-3 md:grid-cols-4 max-h-[350px] overflow-y-auto">
                                <div
                                    v-for="(item, index) in selectedDates.is_half_day === 1
                                        ? Object.entries(selectedDates.time_slots)
                                        : selectedDates.selected_dates"
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
                        <div class="w-full max-w-md p-6 bg-white rounded shadow-lg">
                            <h2 class="mb-4 text-xl font-semibold text-gray-800">Restore Booking</h2>
                            <p class="mb-4 text-sm text-gray-600">Are you sure you want to restore this booking?</p>

                            <form @submit.prevent>
                                <div class="mb-4">
                                    <label
                                        for="restoreReason"
                                        class="block mb-1 text-sm font-medium text-gray-700">
                                        Reason for Restore
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
                                    type="button"
                                    class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded hover:bg-gray-200">
                                    Cancel
                                </button>
                                <button
                                    @click="deleteBooking"
                                    type="button"
                                    class="px-1 py-2 text-sm text-white rounded bg-muted hover:bg-muted/60">
                                    Restore
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div></AuthenticatedLayout
    >
</template>
