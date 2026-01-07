<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import GlobalNoteModal from '@/Components/Modals/NoteModal.vue';

const props = defineProps({
    bookings: Object,
    filters: Object,
    can: Object,
});

const showNoteModal = ref(false);
const page = usePage();

const successMessage = ref(null);
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

watch(showMessage, msg => {
    if (msg) {
        setTimeout(() => {
            successMessage.value = null;
        }, 1500);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
});

const showModal = ref(false);
const bookingToDelete = ref(null);

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

const showViewModal = ref(false);
const selectedBooking = ref(null);

const openViewModal = booking => {
    selectedBooking.value = booking;
    showViewModal.value = true;
};

const closeViewModal = () => {
    showViewModal.value = false;
    selectedBooking.value = null;
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

const capitalize = text => {
    if (!text) return 'N/A';
    return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase();
};

const formatLabel = label => {
    if (label === '&laquo; Previous') return 'Prev';
    if (label === 'Next &raquo;') return 'Next';
    return label;
};

const splitDates = dates => {
    if (!Array.isArray(dates)) return [[]];

    if (dates.length <= 7) {
        return [dates];
    }

    const half = Math.ceil(dates.length / 2);
    return [dates.slice(0, half), dates.slice(half)];
};

const approveBooking = id => {
    if (!id) return;

    router.put(
        route('bookingoffice.approve', id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                successMessage.value = 'Booking approved successfully.';
                closeViewModal();
            },
            onError: () => {
                successMessage.value = 'Failed to approve booking.';
            },
        }
    );
};

const rejectBooking = id => {
    if (!id) return;

    if (confirm('Are you sure you want to reject this booking?')) {
        router.put(
            route('bookingoffice.reject', id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    successMessage.value = 'Booking rejected.';
                    closeViewModal();
                },
                onError: () => {
                    successMessage.value = 'Failed to reject booking.';
                },
            }
        );
    }
};

const cancelBooking = id => {
    if (!id) return;

    if (confirm('Cancel this booking?')) {
        router.put(
            route('bookingoffice.cancel', id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    successMessage.value = 'Booking cancelled.';
                    closeViewModal();
                },
                onError: () => {
                    successMessage.value = 'Failed to cancel booking.';
                },
            }
        );
    }
};
</script>

<template>
    <Head title="Closed Offices Bookings" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between space-x-5">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Booking {{ bookings.data[0]?.office?.category?.name ?? 'Office' }}
                </h2>

                <button
                    @click="showNoteModal = true"
                    class="px-2 py-2 text-lg text-white rounded bg-bluemain hover:bluemain/60">
                    Add Note
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Success Message -->
                <template v-if="showMessage">
                    <div class="p-3 mb-4 text-green-800 bg-green-100 rounded">
                        {{ successMessage || flashMessage || '✔️ Success' }}
                    </div>
                </template>

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
                                <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">ID</th>
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
                                <td class="px-6 py-4 text-sm text-gray-800">{{ booking.id }}</td>
                                <td
                                    v-if="can['manage settings']"
                                    class="px-6 py-4 text-sm text-gray-800">
                                    {{ booking.user?.name ?? '—' }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-800">
                                    {{ booking.office?.office_name ?? '—' }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-800">
                                    {{ formatDate(booking.start_date) ?? '—' }}
                                </td>

                                <th
                                    class="w-1 px-1 py-3 text-sm font-medium text-center text-gray-700 whitespace-nowrap">
                                    hello
                                </th>

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
                                    <div class="flex space-x-1 text-center">
                                        <button
                                            @click="openViewModal(booking)"
                                            class="px-2 py-1 text-sm text-white rounded bg-primary hover:bg-bluemain">
                                            View
                                        </button>
                                        <button
                                            v-if="can['edit bookings']"
                                            @click="openViewModal(booking)"
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
                                    <div class="grid grid-cols-[140px_1fr] gap-x-2 items-start">
                                        <div
                                            v-if="can['manage settings']"
                                            class="font-medium text-gray-600">
                                            Booking ID:
                                        </div>
                                        <div v-if="can['manage settings']">{{ selectedBooking.id }}</div>

                                        <div
                                            v-if="can['manage settings']"
                                            class="font-medium text-gray-600">
                                            User:
                                        </div>
                                        <div v-if="can['manage settings']">{{ selectedBooking.user?.name ?? '—' }}</div>

                                        <div class="font-medium text-gray-600"><strong>Office Name:</strong></div>
                                        <div>{{ selectedBooking.office?.office_name ?? '—' }}</div>

                                        <div class="font-medium text-gray-600"><strong>Start Date:</strong></div>
                                        <div>{{ formatDate(selectedBooking.start_date) }}</div>

                                        <div class="font-medium text-gray-600"><strong>End Date:</strong></div>
                                        <div>{{ formatDate(selectedBooking.end_date) }}</div>

                                        <div class="font-medium text-gray-600"><strong>Number of Days:</strong></div>
                                        <div>{{ selectedBooking.months ?? '—' }}</div>

                                        <div class="font-medium text-gray-600"><strong>Total Price:</strong></div>
                                        <div>R {{ selectedBooking.total_price ?? '0.00' }}</div>

                                        <div class="font-medium text-gray-600">Status:</div>
                                        <div class="text-yellow-800 bg-yellow-100">
                                            {{ capitalize(selectedBooking.status) }}
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
                <GlobalNoteModal
                    :users="users"
                    :show="showNoteModal"
                    :onClose="() => (showNoteModal = false)" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
