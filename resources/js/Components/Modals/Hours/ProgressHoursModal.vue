<script setup>
import { ref, watch, computed } from 'vue';
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
    office_id: '',
    status: '',
    hours_used: 0,
    start_at: '',
    closed_at: '',
});

const boardrooms = ref([]);
const booked = ref([]);
const hoursByOffice = ref({});
const totalHoursUsed = ref(0);
const totalRemainingHours = ref(0);

watch(
    () => form.user_id,
    async newUserId => {
        if (newUserId) {
            try {
                const response = await axios.get(route('admin.hours.user'), {
                    params: { user_id: newUserId },
                });

                boardrooms.value = response.data.boardrooms ?? [];
                booked.value = response.data.booked ?? [];
                hoursByOffice.value = response.data.hours_by_office ?? [];
                totalHoursUsed.value = response.data.total_hours_used ?? 0;
                totalRemainingHours.value = response.data.total_remaining_hours ?? 0;
            } catch (error) {
                console.error('Error fetching data:', error);
                boardrooms.value = [];
                booked.value = [];
                hoursByOffice.value = {};
                totalHoursUsed.value = 0;
                totalRemainingHours.value = 0;
            }
        } else {
            boardrooms.value = [];
            booked.value = [];
            hoursByOffice.value = {};
            totalHoursUsed.value = 0;
            totalRemainingHours.value = 0;
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

// const modalRef = ref(null);

// // Whenever message changes, scroll modal to top
// watch(() => messageText.value, newMessage => {
//   if (newMessage && modalRef.value) {
//     modalRef.value.scrollTop = 0;
//   }
// });

const modalRef = ref(null);

const scrollModalTop = () => {
    if (modalRef.value) {
        modalRef.value.scrollTop = 0;
    }
};

const submit = () => {
    amountError.value = null;
    statusError.value = null;

    if (Number(form.hours_used) === 0) {
        amountError.value = 'Amount must be greater than 0';
        scrollModalTop();
        return;
    }

    if (form.status === 'none') {
        statusError.value = 'Please choose In progress or Closed';
        scrollModalTop();
        return;
    }

    form.post(route('admin.hours.store'), {
        preserveScroll: true,
        onSuccess: () => {
            message.value = 'Boardroom Hours Saved successfully';
            status.value = 'success';

            scrollModalTop();

            setTimeout(() => {
                router.reload({ preserveScroll: true });
                router.visit(route('admin.dashboard'));
            }, 4000);
        },
        onError: errors => {
            message.value = Object.values(errors).join('\n');
            status.value = 'deleted';

            scrollModalTop();
        },
    });
};

const incrementhour = () => {
    if (form.hours_used === '') form.hours_used = 0;
    form.hours_used = Number(form.hours_used) + 1;
};

const decrementhour = () => {
    if (form.hours_used === '' || Number(form.hours_used) <= 1) {
        form.hours_used = 1;
    } else {
        form.hours_used = Number(form.hours_used) - 1;
    }
};

const selectedOfficeHours = computed(() => {
    if (!form.office_id) return null;
    return hoursByOffice.value[form.office_id] || null;
});

const visitNormalHour = () => {
    router.visit(route('admin.boardroom_hours.index'));
};

const visitFreeHours = () => {
    router.visit(route('admin.hours.index'));
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 overflow-y-auto bg-black/60">
        <div
            ref="modalRef"
            class="w-full max-w-4xl max-h-[80vh] overflow-y-auto p-4 bg-white rounded shadow-lg sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-5 gap-3">
                <h2 class="text-2xl sm:text-2xl">Boardroom Hours</h2>

                <div class="flex flex-col sm:flex-row sm:space-x-3 gap-2 sm:gap-0 w-full sm:w-auto">
                    <button
                        @click="visitNormalHour()"
                        class="w-full sm:w-auto px-4 py-2 text-sm text-white rounded bg-primary">
                        Normal Hours
                    </button>
                    <button
                        @click="visitFreeHours()"
                        class="w-full sm:w-auto px-4 py-2 text-sm text-white rounded bg-bluemain">
                        Free Hours
                    </button>
                    <button
                        @click="props.onClose"
                        class="w-full sm:w-auto px-4 py-2 text-sm text-white rounded bg-muted">
                        Close
                    </button>
                </div>
            </div>
            <hr class="my-2 border-gray-300" />

            <template v-if="showMessage">
                <div :class="messageClass">
                    {{ messageText }}
                </div>
            </template>

            <form
                @submit.prevent="submit"
                class="mt-10 space-y-6">
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
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 mt-5">
                    <!-- Boardroom Selection -->
                    <div
                        v-if="booked.length > 0"
                        class="mb-3">
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

                    <!-- office Selection -->
                    <div
                        v-if="booked.length > 0"
                        class="mb-3">
                        <label class="block font-medium text-md">Offices</label>
                        <select
                            v-model="form.office_id"
                            class="w-full px-3 py-2 border rounded">
                            <option value="">Select Office</option>
                            <option
                                v-for="room in booked"
                                :key="room.id"
                                :value="room.id">
                                {{ room.office.office_name }} - {{ room.office.location?.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Status Radio Group -->
                    <!-- <div
                        v-if="booked.length > 0"
                        class="mb-5">
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
                    </div> -->

                    <!-- Start Date -->
                    <!-- <div
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
                    </div> -->

                    <!-- Close Date -->
                    <!-- <div
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
                    </div> -->
                </div>
                <hr class="my-5 border-gray-300" />
                <!-- Hours Counter + Info -->
                <div
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 mt-5"
                    v-if="booked.length > 0">
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
                                class="text-lg font-bold text-gray-900 bg-gray-100 border border-gray-300 rounded w-11 h-11 hover:bg-primary/60">
                                +
                            </button>
                            <input
                                type="text"
                                id="counter"
                                v-model="form.hours_used"
                                class="text-sm font-medium text-center border border-gray-300 w-14 h-11 bg-gray-50"
                                required />
                            <p
                                v-if="amountError"
                                class="mt-1 text-sm text-red-600">
                                {{ amountError }}
                            </p>
                            <button
                                type="button"
                                @click="decrementhour"
                                class="text-lg font-bold text-gray-900 bg-gray-100 border border-gray-300 rounded w-11 h-11 hover:bg-primary/60">
                                –
                            </button>
                        </div>
                    </div>
                    <div>
                        <hr class="my-5 border-gray-300 sm:border-0" />
                        <div v-for="count in hoursByOffice">
                            <div>
                                <h3 class="block mb-3 text-xl text-center text-gray-900">Total Hours Used</h3>
                                <p class="block text-5xl font-semibold text-center text-black">
                                    {{ count.hours_used }}
                                </p>
                            </div>
                            <div class="mt-8">
                                <hr class="my-5 border-gray-300" />
                                <h3 class="block mb-3 text-center text-gray-900 text-md">Remaining Hours</h3>
                                <p class="block text-3xl font-semibold text-center text-black">
                                    {{ count.remaining_hours }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-5 border-gray-300" />

                <!-- Submit + Cancel -->
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
