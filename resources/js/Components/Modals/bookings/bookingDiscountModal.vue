<script setup>
import { ref } from 'vue';
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
});

const boardrooms = ref([]);
const booked = ref([]);

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
</script>

<template>
    <template v-if="showMessage">
        <div :class="messageClass">
            {{ messageText }}
        </div>
    </template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
        <div>
            <!-- Second column content -->
        </div>
        <form
            @submit.prevent="submit"
            class="w-full">
            <div>
                <label class="block text-md font-medium text-gray-700">Update Discount (%)</label>
                <input
                    type="number"
                    class="w-full px-3 py-1 border rounded" />
                <!-- <div
                                            v-if="form.errors.discount"
                                            class="text-sm text-red-600">
                                            {{ form.errors.discount }}
                                        </div> -->
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
</template>
