<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import useStatusMessage from '../../../Composables/useStatusMessage';
import GlobalNoteModal from '@/Components/Modals/NoteModal.vue';
import cartOfficeModal from '../../../Components/Modals/Cart/CartOfficeModal.vue';

const props = defineProps({
    bookings: Object,
    discounts: Number,
    filters: Object,
    users: Object,
    can: Object,
    approvedClosed: Object,
});

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();

const search = ref(props.filters?.search ?? '');
const isLoading = ref(false);

const showNoteModal = ref(false);
const showDiscountModal = ref(false);
const showDatesModal = ref(false);
const showAvailModal = ref(false);
const selectedDates = ref(null);
const showModal = ref(false);
const bookingToDelete = ref(null);
const showViewModal = ref(false);

const monthDuration = ref(null);
const showMonths = ref(false);
const selectedBooking = ref(null);

const pendingCount = computed(() => props.approvedClosed?.length ?? 0);

const viewDatesModal = booking => {
    if (booking.plan === 'daily') {
        selectedDates.value = booking;
        showDatesModal.value = true;
    }

    if (booking.plan === 'monthly') {
        generateMonthDuration(booking.start_date, booking.months);
    }
};

const closeDateModal = () => {
    showViewModal.value = false;
    selectedBooking.value = null;
    selectedDates.value = null;
};

const generateMonthDuration = (start, monthsCount) => {
    const months = [];
    const current = new Date(start);

    current.setDate(1);

    for (let i = 0; i < monthsCount; i++) {
        months.push(current.toLocaleString('default', { month: 'long', year: 'numeric' }));
        current.setMonth(current.getMonth() + 1);
    }

    monthDuration.value = months;
    showMonths.value = true;
};

const groupedMonths = computed(() => {
    const raw = monthDuration.value;
    if (!Array.isArray(raw)) return {};

    return raw.reduce((acc, label) => {
        const year = label.split(' ')[1];
        if (!year) return acc;

        (acc[year] ||= []).push(label);
        return acc;
    }, {});
});

watch(search, value => {
    router.get(
        route('bookingclosed.show'),
        { search: value },
        {
            preserveState: true,
            replace: true,
            onBefore: () => (isLoading.value = true),
            onFinish: () => (isLoading.value = false),
        }
    );
});

const deleteBooking = () => {
    if (bookingToDelete.value) {
        router.delete(route('admin.bookings.destroy', bookingToDelete.value), {
            preserveScroll: true,

            onSuccess: () => {
                message.value = 'Booking rejected successfully..';
                status.value = 'deleted';

                setTimeout(() => {
                    router.reload({ preserveScroll: true });
                    router.visit(route('admin.banking.index'));
                }, 2000);
            },
        });
    }
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

function capitalizeFirst(str) {
    if (!str) return '—';
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

const discountedPrice = computed(() => {
    if (!selectedBooking.value || !bookingDiscount.value) {
        return selectedBooking.value?.total_price ?? 0;
    }

    const total = Number(selectedBooking.value.total_price);
    const discountPercent = Number(bookingDiscount.value.discount);

    const discountAmount = total * (discountPercent / 100);
    const finalTotal = total - discountAmount;

    return {
        finalTotal,
        discountPercent,
        discountAmount,
    };
});

const approveBooking = id => {
    if (!id) return;

    if (confirm('Are you sure you want to change to Approved?')) {
        router.put(
            route('bookingclosed.approve', id),
            {
                discount_percent: discountedPrice.value.discountPercent,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    message.value = 'Office status changed to Approved';
                    status.value = 'success';

                    setTimeout(() => {
                        router.reload({ preserveScroll: true });
                    }, 2000);
                },
                onError: () => {
                    message.value = 'Failed to change to approved.';
                    status.value = 'deleted';
                },
            }
        );
    }
};

const paidBooking = id => {
    if (!id) return;

    if (confirm('Are you sure you want to change to Paid?')) {
        router.put(route('bookingclosed.paid', id), {
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

const rejectBooking = id => {
    if (!id) return;

    if (confirm('Are you sure you want to reject this booking?')) {
        router.put(
            route('bookingclosed.reject', id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    message.value = '';
                    status.value = 'deleted';

                    setTimeout(() => {
                        router.reload({ preserveScroll: true });
                        closeViewModal();
                    }, 2000);
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
            route('bookingclosed.cancel', id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    message.value = '';
                    status.value = 'cancelled';

                    setTimeout(() => {
                        router.reload({ preserveScroll: true });
                        closeViewModal();
                    }, 2000);
                },
                onError: () => {
                    successMessage.value = 'Failed to cancel booking.';
                },
            }
        );
    }
};

const allBookings = computed(() => {
    const closed = props.approvedClosed.map(b => {
        return {
            name: b.office.office_name,
            type: 'Closed Office',
            price: b.total_price,
            plan: b.plan,
            id: b.office_id,
            months: b.months,
            monthly_rate: Number(b.office.monthly_rate),
            daily_rate: Number(b.office.daily_rate),
            location: b.office.location?.name,
        };
    });

    return closed;
});
</script>

<template>
    <Head title="Closed Offices Bookings" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Booked Closed Offices</h2>

                <div class="flex flex-col gap-2 sm:flex-row sm:gap-3">
                    <div v-if="pendingCount > 0">
                        <button
                            @click="showAvailModal = true"
                            type="button"
                            class="w-full sm:w-auto px-4 py-2 text-sm sm:text-lg text-white border border-solid rounded bg-primary hover:bg-bluemain/60 focus:outline-none">
                            Payment Pending ({{ pendingCount }})
                        </button>
                    </div>
                    <button
                        v-if="can['manage settings']"
                        @click="showNoteModal = true"
                        class="px-2 py-2 text-lg text-white rounded bg-bluemain hover:bluemain/60">
                        Add Note
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-full px-4 mx-auto sm:px-6 lg:max-w-7xl lg:px-8">
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
                                    class="px-6 py-3 text-sm font-semibold text-left text-gray-700">
                                    Booked By
                                </th>
                                <th class="px-6 py-3 text-sm font-semibold text-left text-gray-700">Office Name</th>
                                <th class="px-6 py-3 text-sm font-semibold text-left text-gray-700">Location</th>
                                <th class="px-6 py-3 text-sm font-semibold text-left text-gray-700">Start Date</th>
                                <th class="px-6 py-3 text-sm font-semibold text-left text-gray-700"></th>
                                <th class="px-6 py-3 text-sm font-semibold text-left text-gray-700">End Date</th>
                                <th class="px-6 py-3 text-sm font-semibold text-left text-gray-700">Status</th>
                                <th class="px-6 py-3 text-sm font-semibold text-left text-gray-700">Actions</th>
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

                                <td class="px-6 py-4 text-sm text-gray-800">
                                    {{ booking.office?.location?.name ?? '—' }}
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
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-5">
                        <!-- Modal container -->
                        <div class="w-full max-w-4xl p-2 bg-white rounded-lg shadow-lg overflow-y-auto max-h-screen">
                            <!-- Modal Header -->
                            <div class="flex items-center justify-between mb-4 p-3">
                                <h2 class="text-lg font-bold text-gray-800">
                                    {{ selectedBooking.office?.office_name ?? '—' }} -
                                    {{ capitalizeFirst(selectedBooking.plan) ?? '—' }}
                                </h2>
                                <button
                                    @click="closeViewModal"
                                    class="text-2xl leading-none text-gray-500 hover:text-gray-700">
                                    &times;
                                </button>
                            </div>

                            <!-- Modal Content -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 m-5 text-sm text-gray-700">
                                <!-- General Info -->
                                <div class="space-y-2">
                                    <div class="grid grid-cols-2 gap-x-2 items-start">
                                        <div
                                            v-if="can['manage settings']"
                                            class="mb-3 font-medium text-gray-600">
                                            <strong>Booking ID</strong>
                                        </div>
                                        <div v-if="can['manage settings']">{{ selectedBooking.id }}</div>

                                        <div
                                            v-if="can['manage settings']"
                                            class="mb-3 font-medium text-gray-600">
                                            <strong>User:</strong>
                                        </div>
                                        <div v-if="can['manage settings']">{{ selectedBooking.user?.name ?? '—' }}</div>

                                        <div class="mb-3 font-medium text-gray-600"><strong>Plan:</strong></div>
                                        <div class="mb-2">{{ capitalizeFirst(selectedBooking.plan) ?? '—' }}</div>

                                        <div class="mb-3 font-medium text-gray-600"><strong>Start Date:</strong></div>
                                        <div class="mb-2">{{ formatDate(selectedBooking.start_date) }}</div>

                                        <div class="mb-3 font-medium text-gray-600"><strong>End Date:</strong></div>
                                        <div class="mb-2">{{ formatDate(selectedBooking.end_date) }}</div>

                                        <div class="mb-3 font-medium text-gray-600"><strong>Duration:</strong></div>
                                        <div class="mb-2">
                                            {{
                                                selectedBooking.plan === 'daily'
                                                    ? (selectedBooking.weekdays_count + ' Days' ?? '—')
                                                    : (selectedBooking.months + ' Months' ?? '—')
                                            }}
                                        </div>

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

                                <!-- Discounts -->
                                <div class="space-y-2">
                                    <div class="grid grid-cols-2 gap-x-1 items-start mb-10">
                                        <div class="mb-3 font-medium text-gray-600">
                                            <strong>Parking:</strong>
                                        </div>
                                        <div class="mb-3">
                                            {{
                                                selectedBooking.parking_price === '0.00'
                                                    ? 'None'
                                                    : 'R ' + selectedBooking.parking_price
                                            }}
                                        </div>
                                        <div class="col-span-2 my-2">
                                            <hr />
                                        </div>
                                        <div class="mb-3 font-medium text-gray-600"><strong>Total Price:</strong></div>
                                        <div class="mb-3">R {{ selectedBooking.total_price ?? '0.00' }}</div>

                                        <div
                                            v-if="selectedBooking.plan === 'monthly'"
                                            class="mb-3 font-medium text-gray-600">
                                            <strong>Boardroom Discount Monthly:</strong>
                                        </div>
                                        <div v-if="selectedBooking.plan === 'monthly'">
                                            {{ selectedBooking.office?.free_boardroom_hours }} Hours
                                        </div>

                                        <div
                                            v-if="selectedBooking.plan === 'daily'"
                                            class="mb-3 font-medium text-gray-600">
                                            <strong>Boardroom Discount Daily:</strong>
                                        </div>
                                        <div v-if="selectedBooking.plan === 'daily'">{{ discounts }} %</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Inline form section -->
                            <div
                                class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4"
                                v-if="ndumi">
                                <div>
                                    <!-- Second column content -->
                                </div>
                                <form
                                    @submit.prevent="submitForm"
                                    class="w-full">
                                    <div>
                                        <label class="block text-md font-medium text-gray-700"
                                            >Update Discount (%)</label
                                        >
                                        <input
                                            v-model="form.discount"
                                            type="number"
                                            class="w-full px-3 py-1 border rounded" />
                                        <div
                                            v-if="form.errors.discount"
                                            class="text-sm text-red-600">
                                            {{ form.errors.discount }}
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <button
                                            v-if="can['manage settings']"
                                            class="w-full px-4 py-1 text-xs text-white bg-bluemain rounded hover:bg-bluemain/60">
                                            Submit
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Modal Footer -->
                            <div class="flex flex-col gap-3 mt-6 sm:flex-row sm:justify-between sm:gap-4">
                                <hr />
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

                <!-- Selected Dates Modal -->
                <template v-if="showDatesModal && selectedDates?.selected_dates?.length">
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

                            <!-- Scrollable Grid -->
                            <div
                                class="grid gap-3 grid-cols-2 sm:grid-cols-3 md:grid-cols-4 max-h-[350px] overflow-y-auto">
                                <div
                                    v-for="(date, index) in selectedDates.selected_dates"
                                    :key="index"
                                    class="px-3 py-2 text-sm text-center bg-gray-100 border rounded shadow-sm">
                                    {{ formatDate(date) }}
                                </div>
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

                <!-- selected Months -->
                <template v-if="showMonths && monthDuration?.length">
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                        <div
                            class="w-full max-w-3xl mx-3 p-6 bg-white rounded-lg shadow-lg overflow-y-auto max-h-[80vh]">
                            <!-- Modal Header -->
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-bold text-gray-800">Selected Months</h2>
                                <button
                                    @click="showMonths = false"
                                    class="text-2xl leading-none text-gray-500 hover:text-gray-700">
                                    &times;
                                </button>
                            </div>

                            <!-- Scrollable Grid -->
                            <div
                                class="grid gap-3 grid-cols-2 sm:grid-cols-3 md:grid-cols-4 max-h-[350px] overflow-y-auto">
                                <div
                                    v-for="(month, index) in monthDuration"
                                    :key="index"
                                    class="px-3 py-2 text-sm text-center bg-gray-100 border rounded shadow-sm">
                                    {{ month }}
                                </div>
                            </div>

                            <!-- Modal Footer -->
                            <div class="mt-6 text-right">
                                <button
                                    @click="showMonths = false"
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

                <GlobalNoteModal
                    :users="users"
                    :show="showDiscountModal"
                    :onClose="() => (showDiscountModal = false)" />

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
