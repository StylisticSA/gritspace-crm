<script setup>
import { ref, watch, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import useStatusMessage from '../../../Composables/useStatusMessage';

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();

const props = defineProps({
    users: Object,
    user: Number,
    boardrooms: Number,
    show: Boolean,
    onClose: Function,
    can: Object,
});

const amountError = ref(null);
const statusError = ref(null);

const form = useForm({
    user_type: '',
    user_id: '',
    user_in_office: '',
    boardroom_id: '',
    status: '',
    hours_used: 0,
    start_at: '',
    closed_at: '',
});

const boardroomsUsers = ref([]);
const booked = ref([]);
const hotdesks = ref([]);
const virtuals = ref([]);

// Watch user_id → fetch boardroom data
watch(
    () => form.user_id,
    async newUserId => {
        if (newUserId) {
            try {
                const response = await axios.get(route('admin.boardroom_hours.inprogres'), {
                    params: { user_id: newUserId },
                });

                boardroomsUsers.value = response.data.boardrooms ?? [];
                booked.value = response.data.booked ?? [];
                hotdesks.value = response.data.hotdesks ?? [];
                virtuals.value = response.data.virtuals ?? [];
            } catch (error) {
                console.error('Error fetching data:', error);
                boardroomsUsers.value = [];
                booked.value = [];
                hotdesks.value = [];
                virtuals.value = [];
            }
        } else {
            boardroomsUsers.value = [];
            booked.value = [];
            hotdesks.value = [];
            virtuals.value = [];
        }
    }
);

// Initialize user_id from props
watch(
    () => props.user,
    newUser => {
        form.user_id = newUser ?? '';
    },
    { immediate: true }
);

// Watch status → auto-fill dates
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

// Watch user_type → clear irrelevant fields
watch(
    () => form.user_type,
    newUserType => {
        if (newUserType === 'in_office') {
            form.user_id = '';
            form.start_at = '';
            form.closed_at = '';
        } else if (newUserType === 'existing') {
            form.boardroom_id = '';
            form.office_id = '';
            form.hours_used = 0;
            form.start_at = '';
            form.closed_at = '';
        } else {
            form.user_id = '';
            form.boardroom_id = '';
            form.office_id = '';
            form.hours_used = 0;
            form.start_at = '';
            form.closed_at = '';
        }
    }
);

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

    form.post(route('admin.boardroom_hours.store'), {
        preserveScroll: true,
        onSuccess: () => {
            message.value = 'Boardroom Hours Saved successfully';
            status.value = 'success';

            scrollModalTop();

            setTimeout(() => {
                router.reload({ preserveScroll: true });
                router.visit(route('admin.dashboard'));
            }, 3000);
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
            class="w-full max-w-2xl max-h-[80vh] overflow-y-auto p-4 bg-white rounded shadow-lg sm:p-6">
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
                ref="modalRef">
                <!-- Type of Booking -->
                <div class="grid grid-cols-1 gap-6 mt-5">
                    <div class="mb-5">
                        <label class="block font-medium text-md mb-1">Type of Booking</label>
                        <div class="flex space-x-6">
                            <label
                                class="flex items-center justify-center flex-1 space-x-2 border rounded py-2 cursor-pointer"
                                :class="form.user_type === 'in_office' ? 'border-primary bg-primary/10' : ''">
                                <input
                                    type="radio"
                                    value="in_office"
                                    v-model="form.user_type"
                                    class="form-radio text-primary" />
                                <span>In Office</span>
                            </label>

                            <label
                                class="flex items-center justify-center flex-1 space-x-2 border rounded py-2 cursor-pointer"
                                :class="form.user_type === 'existing' ? 'border-primary bg-primary/10' : ''">
                                <input
                                    type="radio"
                                    value="existing"
                                    v-model="form.user_type"
                                    class="form-radio text-primary" />
                                <span>Existing User</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Existing User Section -->
                <div v-if="form.user_type === 'existing'">
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

                    <div
                        class="mt-5"
                        v-if="booked.length > 0">
                        <label class="block font-medium text-md">Boardroom</label>
                        <select
                            v-model="form.boardroom_id"
                            class="w-full px-3 py-2 border rounded">
                            <option value="">Select Boardroom</option>
                            <option
                                v-for="rooms in boardroomsUsers"
                                :key="rooms.id"
                                :value="rooms.id">
                                {{ rooms.boardroom_name }} - {{ rooms.location?.name }}
                            </option>
                        </select>
                    </div>

                    <div
                        v-if="booked.length > 0"
                        class="my-5">
                        <label class="block font-medium text-md">Offices Booked</label>
                        <div class="grid grid-col-3 gap-3 mt-2">
                            <div class="w-full px-3 py-2 border rounded">
                                <h6 class="text-md font-semibold mb-2">Closed Office</h6>
                                <div
                                    class="text-sm"
                                    v-for="room in booked"
                                    :key="room.id"
                                    :value="room.id">
                                    {{ room.office.office_name }} - {{ room.office.location?.name }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="w-full px-3 py-2 border rounded"
                        v-if="hotdesks.length > 0">
                        <label class="block font-medium text-md">Hot Desk</label>
                        <div
                            v-for="desk in hotdesks"
                            :key="desk.id"
                            :value="desk.id">
                            {{ desk.helpdesk?.help_desk_name }} - {{ desk.helpdesk?.location?.name }}
                        </div>
                    </div>
                    <div
                        class="w-full px-3 py-2 border rounded"
                        v-if="virtuals.length > 0">
                        <label class="block font-medium text-md">Virtual Offices</label>
                        <div
                            v-for="virtual in virtuals"
                            :key="virtual.id"
                            :value="virtual.id">
                            {{ virtual.virtualOffice?.virtualoffice_name }} -
                            {{ virtual.virtualOffice?.location?.name }}
                        </div>
                    </div>
                </div>

                <!-- In Office Section -->
                <div v-if="form.user_type === 'in_office'">
                    <!-- User Selection -->
                    <div class="mb-3">
                        <label class="block font-medium text-md">User In Office</label>
                        <input
                            type="text"
                            v-model="form.user_in_office"
                            placeholder="Enter user name"
                            class="w-full px-3 py-2 border rounded" />
                        <div
                            v-if="form.errors.user_in_office"
                            class="text-sm text-red-600">
                            {{ form.errors.user_in_office }}
                        </div>
                    </div>
                    <div class="mt-5">
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
                </div>

                <!-- Status -->
                <hr class="mt-10 border-gray-300" />

                <div class="grid grid-cols-1 gap-6 mt-5">
                    <!-- <div>
                        <label class="block font-medium text-md">Status</label>
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

                <!-- Hours Counter -->
                <hr class="my-5 border-gray-300" />
                <div class="grid grid-cols-1 gap-6 mt-5">
                    <div class="text-center border-r border-gray-200">
                        <label
                            for="counter"
                            class="block mb-2 text-xl text-center text-gray-900">
                            Hours:
                        </label>
                        <div class="flex flex-row items-center justify-center my-10 space-x-3">
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
                            <button
                                type="button"
                                @click="decrementhour"
                                class="text-lg font-bold text-gray-900 bg-gray-100 border border-gray-300 rounded w-11 h-11 hover:bg-primary/60">
                                –
                            </button>
                        </div>
                        <p
                            v-if="amountError"
                            class="mt-1 text-sm text-red-600">
                            {{ amountError }}
                        </p>
                    </div>
                </div>

                <!-- Submit + Cancel -->
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
