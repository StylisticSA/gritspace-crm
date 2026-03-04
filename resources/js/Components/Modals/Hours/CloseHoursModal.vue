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
    ids: [],
    user_id: '',
    boardroom_id: '',
    status: '',
    hours_used: 0,
    start_at: '',
    closed_at: '',
});

const inprogres = ref([]);
const closed = ref([]);

const boardrooms = ref([]);
const bookings = ref([]);
const totalHours = ref('');
const remainingHours = ref('');
const hoursUsed = ref('');

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
                const response = await axios.get(route('admin.hours.closed'), {
                    params: { user_id: newUserId },
                });

                inprogres.value = response.data.hours ?? [];
                closed.value = response.data.hours_closed ?? [];

                boardrooms.value = response.data.boardrooms ?? [];
                bookings.value = response.data.bookings ?? [];
                totalHours.value = response.data.hours_used ?? '';
                remainingHours.value = response.data.remaining_hours ?? '';
                hoursUsed.value = response.data.sum_hours_used ?? '';
            } catch (error) {
                console.error('Error fetching boardrooms:', error);
                inprogres.value = [];
                closed.value = [];
                boardrooms.value = [];
                bookings.value = [];
                totalHours.value = '';
                remainingHours.value = '';
                hoursUsed.value = '';
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

const submit = () => {
    amountError.value = null;
    statusError.value = null;

    if (form.status === 'none') {
        statusError.value = 'Please choose In progress or Closed';
        return;
    }

    form.ids = inprogres.value.map(item => item.id);

    if (form.ids.length === 0) {
        console.error('No record IDs found for update');
        return;
    }

    form.put(route('admin.hours.update', form.ids[0]), {
        preserveScroll: true,
        onSuccess: () => {
            message.value = 'Boardroom Hours updated successfully';
            status.value = 'success';

            setTimeout(() => {
                router.reload({ preserveScroll: true });
                router.visit(route('admin.dashboard'));
            }, 2000);
        },
        onError: errors => {
            message.value = Object.values(errors).join('\n');
            status.value = 'deleted';
        },
    });
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 overflow-y-auto bg-black/60">
        <div class="w-full max-w-6xl max-h-[80vh] overflow-y-auto p-4 bg-white rounded shadow-lg sm:p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-2xl sm:text-2xl">Close Boardroom Free Hours</h2>

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
                            class="mb-5"
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
                        <div class="mt-5">
                            <div v-if="hoursUsed">
                                <h4 class="block mb-3 text-xl text-center text-gray-900">Used Hours</h4>

                                <p class="block text-5xl font-semibold text-center text-black">
                                    {{ hoursUsed }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Hours Counter + Info -->
                    <div>
                        <div class="grid grid-cols-1 gap-6 mt-5">
                            <hr class="my-2 border-gray-300 md:hidden" />

                            <div v-if="form.user_id && inprogres.length > 0">
                                <div class="overflow-x-auto">
                                    <h4 class="block mb-3 text-xl text-gray-900">In Progress</h4>

                                    <table class="w-full">
                                        <thead>
                                            <tr class="border-b border-secondary-200 dark:border-secondary-700">
                                                <th
                                                    class="text-left py-3 px-4 text-sm font-semibold text-secondary-700 dark:text-secondary-300">
                                                    Username
                                                </th>
                                                <th
                                                    class="text-left py-3 px-4 text-sm font-semibold text-secondary-700 dark:text-secondary-300">
                                                    Boardrooms
                                                </th>
                                                <th
                                                    class="text-left py-3 px-4 text-sm font-semibold text-secondary-700 dark:text-secondary-300">
                                                    Start Date
                                                </th>
                                                <th
                                                    class="text-left py-3 px-4 text-sm font-semibold text-secondary-700 dark:text-secondary-300">
                                                    Status
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="closed in inprogres"
                                                :key="closed.id"
                                                class="border-b border-secondary-100 dark:border-secondary-800 hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition-colors">
                                                <td class="py-3 px-4">
                                                    <div>
                                                        <p class="text-sm font-medium text-secondary-900">
                                                            {{ closed.user?.name }}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-4">
                                                    <span class="text-sm text-secondary-700 dark:text-secondary-300">{{
                                                        closed.boardroom?.boardroom_name
                                                    }}</span>
                                                </td>
                                                <td class="py-3 px-4">
                                                    <span class="text-sm text-secondary-700 dark:text-secondary-300">{{
                                                        closed.start_at
                                                    }}</span>
                                                </td>
                                                <td class="py-3 px-4">
                                                    <span
                                                        class="px-2 py-1 rounded text-xs font-semibold capitalize bg-yellow-100 text-yellow-800">
                                                        In Progress
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <hr class="my-5 border-gray-300" />
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
