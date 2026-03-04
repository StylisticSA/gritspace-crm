<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import useStatusMessage from '../../../Composables/useStatusMessage';

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();

const props = defineProps({
    users: Object,
    user: Number,
    show: Boolean,
    onClose: Function,
    can: Object,
});

const amountError = ref(null);
const statusError = ref(null);

const form = useForm({
    user_id: '',
    boardroom_id: '',
    status: '',
    hours_used: 0,
    start_at: '',
    closed_at: '',
});

const boardrooms = ref([]);
const bookings = ref([]);
const totalHours = ref('');
const remainingHours = ref('');

watch(
    () => props.user,
    newUser => {
        form.user_id = newUser ?? '';
    },
    { immediate: true }
);

watch(
    () => form.user_id,
    async newUserId => {
        if (newUserId) {
            try {
                const response = await axios.get(route('admin.hours.user'), {
                    params: { user_id: newUserId },
                });

                boardrooms.value = response.data.boardrooms ?? [];
                bookings.value = response.data.bookings ?? [];
                totalHours.value = response.data.hours_used ?? '';
                remainingHours.value = response.data.remaining_hours ?? '';
            } catch (error) {
                console.error('Error fetching boardrooms:', error);
                boardrooms.value = [];
                bookings.value = [];
                totalHours.value = '';
                remainingHours.value = '';
            }
        } else {
            boardrooms.value = [];
            bookings.value = [];
            totalHours.value = '';
            remainingHours.value = '';
        }
    }
);

watch(
    () => form.status,
    newStatus => {
        const today = new Date().toISOString().split('T')[0];

        if (newStatus === 'in_progress') {
            form.start_at = today;
            form.closed_at = '';
        } else if (newStatus === 'closed') {
            form.closed_at = today;
            form.start_at = '';
        } else if (newStatus === 'none') {
            form.start_at = '';
            form.closed_at = '';
        }
    }
);

watch(
    () => props.user,
    newUser => {
        form.user_id = newUser ?? '';
    },
    { immediate: true }
);

const submit = () => {
    amountError.value = null;
    statusError.value = null;

    if (Number(form.hours) === 0) {
        amountError.value = 'Amount must be greater than 0';
        return;
    }

    if (form.status === 'none') {
        statusError.value = 'Please choose In progress or Closed';
        return;
    }

    form.post(route('admin.hours.store'), {
        preserveScroll: true,
        onSuccess: () => {
            message.value = 'Boardroom Hours updated successfully';
            status.value = 'success';

            setTimeout(() => {
                router.reload({ preserveScroll: true });
                router.visit(route('admin.dashboard'));
            }, 4000);
        },
        onError: errors => {
            message.value = Object.values(errors).join('\n');
            status.value = 'deleted';
        },
    });
};

const incrementhour = () => {
    if (form.hours_used === '') form.hours_used = 0;
    form.hours_used = Math.min(Number(form.hours_used) + 1);
};

const decrementhour = () => {
    if (form.hours_used === '' || Number(form.hours_used) <= 1) {
        form.hours_used = 1;
    } else {
        form.hours_used = Number(form.hours_used) - 1;
    }
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 overflow-y-auto bg-black/60">
        <div class="w-full max-w-4xl max-h-[80vh] overflow-y-auto p-4 bg-white rounded shadow-lg sm:p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-2xl sm:text-2xl">Boardroom Free Hours</h2>

                <button
                    @click="props.onClose"
                    class="text-sm text-green-800 underline">
                    Close
                </button>
            </div>
            <hr class="my-2 border-gray-300" />

            <template v-if="showMessage">
                <div :class="messageClass">
                    {{ messageText }}
                </div>
            </template>

            <div v-if="!props.user">
                <ul v-for="booked in bookings">
                    <!-- <li>{{ booked }}</li> -->
                    <h3 class="block mb-3 text-xl text-gray-900">User Booked Office</h3>
                    <li><strong>Office Type:</strong> {{ booked.category?.name ?? 'Category of the booking' }}</li>
                    <li><strong>Office Name:</strong> {{ booked.office?.office_name ?? 'Office Name' }}</li>
                    <li>
                        <strong>Office Location:</strong> {{ booked.office.location?.name ?? 'Location of the Office' }}
                    </li>
                </ul>
            </div>
            <form
                @submit.prevent="submit"
                class="mt-10 space-y-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 mt-5">
                    <div>
                        <!-- User Selection -->
                        <div class="mb-3">
                            <div v-if="can['manage settings']">
                                <label class="block font-medium text-md">User</label>
                                <select
                                    v-model="form.user_id"
                                    class="w-full px-3 py-2 border rounded">
                                    <option value="">Select User</option>
                                    <option
                                        v-for="user in users"
                                        :key="user.id"
                                        :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                                <div
                                    v-if="form.errors.user_id"
                                    class="text-sm text-red-600">
                                    {{ form.errors.user_id }}
                                </div>
                            </div>
                            <input
                                v-else
                                type="hidden"
                                v-model="form.user_id" />
                        </div>

                        <!-- Boardroom Selection -->
                        <div class="mb-3">
                            <label class="block font-medium text-md">Boardroom</label>
                            <select
                                v-model="form.boardroom_id"
                                class="w-full px-3 py-2 border rounded">
                                <option value="">Select Boardroom</option>
                                <option
                                    v-for="room in boardrooms"
                                    :key="room.id"
                                    :value="room.id">
                                    {{ room.boardroom_name }} - {{ room.location?.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Status Radio Group -->
                        <div class="mb-5">
                            <label class="block font-medium text-md mb-2">Status</label>
                            <p
                                v-if="statusError"
                                class="mt-1 text-sm text-red-600">
                                {{ statusError }}
                            </p>
                            <div class="flex space-x-6">
                                <label class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        value="in_progress"
                                        v-model="form.status"
                                        class="form-radio text-primary" />
                                    <span>In Progress</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        value="closed"
                                        v-model="form.status"
                                        class="form-radio text-primary" />
                                    <span>Closed</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input
                                        type="radio"
                                        value="none"
                                        v-model="form.status"
                                        class="form-radio text-primary" />
                                    <span>None</span>
                                </label>
                            </div>
                            <div
                                v-if="form.errors.status"
                                class="text-sm text-red-600">
                                {{ form.errors.status }}
                            </div>
                        </div>

                        <!-- Start Date -->
                        <div
                            class="mb-3"
                            v-if="form.status === 'in_progress'">
                            <label class="block font-medium text-md">Start Date</label>
                            <input
                                type="date"
                                v-model="form.start_at"
                                class="w-full px-3 py-2 border rounded" />
                            <div
                                v-if="form.errors.start_at"
                                class="text-sm text-red-600">
                                {{ form.errors.start_at }}
                            </div>
                        </div>

                        <!-- Close Date -->
                        <div
                            class="mb-3"
                            v-if="form.status === 'closed'">
                            <label class="block font-medium text-md">Close Date</label>
                            <input
                                type="date"
                                v-model="form.closed_at"
                                class="w-full px-3 py-2 border rounded" />
                            <div
                                v-if="form.errors.closed_at"
                                class="text-sm text-red-600">
                                {{ form.errors.closed_at }}
                            </div>
                        </div>
                    </div>

                    <!-- Hours Counter + Info -->
                    <div>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 mt-5">
                            <div class="text-center border-r border-gray-200">
                                <label
                                    for="counter"
                                    class="block mb-2 text-xl text-center text-gray-900"
                                    >Hours:</label
                                >
                                <div class="flex flex-col items-center my-10 space-y-3">
                                    <button
                                        type="button"
                                        @click="incrementhour"
                                        class="text-lg font-bold text-gray-900 bg-gray-100 border border-gray-300 rounded w-11 h-11 hover:bg-primary/60 focus:ring-gray-100 focus:ring-2 focus:outline-none">
                                        +
                                    </button>
                                    <input
                                        type="text"
                                        id="counter"
                                        v-model="form.hours_used"
                                        class="text-sm font-medium text-center border border-gray-300 w-14 h-11 bg-gray-50 focus:ring-primary/60 focus:border-primary/60"
                                        required />
                                    <p
                                        v-if="amountError"
                                        class="mt-1 text-sm text-red-600">
                                        {{ amountError }}
                                    </p>
                                    <button
                                        type="button"
                                        @click="decrementhour"
                                        class="text-lg font-bold text-gray-900 bg-gray-100 border border-gray-300 rounded w-11 h-11 hover:bg-primary/60 focus:ring-gray-100 focus:ring-2 focus:outline-none">
                                        –
                                    </button>
                                </div>
                            </div>

                            <hr class="my-2 border-gray-300 md:hidden" />

                            <div>
                                <div>
                                    <h3 class="block mb-3 text-xl text-center text-gray-900">Free Hours</h3>
                                    <p class="block text-5xl font-semibold text-center text-black">{{ totalHours }}</p>
                                </div>
                                <hr class="my-5 border-gray-300" />
                                <div class="mt-5">
                                    <h3 class="block mb-3 text-center text-gray-900 text-md">Remaining Hours</h3>
                                    <p class="block text-3xl font-semibold text-center text-black">
                                        {{ remainingHours }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-5 border-gray-300" />

                <div class="flex flex-col gap-3 sm:flex-row sm:justify-between">
                    <button
                        type="submit"
                        class="w-full px-4 py-2 text-sm text-white bg-green-700 rounded sm:w-auto hover:bg-bluemain/60"
                        :disabled="form.processing">
                        Save
                    </button>
                    <button
                        type="button"
                        @click="props.onClose"
                        class="w-full px-4 py-2 text-sm text-white rounded sm:w-auto bg-muted">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
