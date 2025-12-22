<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import useStatusMessage from '../../../Composables/useStatusMessage';

const props = defineProps({
    invoice: Object,
    show: Boolean,
    onClose: Function,
    can: Object,
});

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();

const form = useForm({
    id: props.invoice.id,
    user: props.invoice?.user.name,
    invoice_number: props.invoice.invoice_number,
});

console.log('i', props.invoice.user);

const submit = () => {
    form.post(route('admin.invoice.send-invoice'), {
        preserveScroll: true,
        onSuccess: () => {
            message.value = '';
            status.value = 'success';

            setTimeout(() => {
                // router.visit(route(props.redirectRoute));
                router.reload({ preserveScroll: true });
            }, 2000);
        },
    });
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 overflow-y-auto bg-black/60">
        <div class="w-full max-w-2xl p-4 bg-white rounded shadow-lg sm:p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-2xl sm:text-2xl">Send Invoice</h2>

                <button
                    @click="props.onClose"
                    class="text-sm text-black">
                    Close
                </button>
            </div>
            <hr class="my-2 border-gray-300" />

            <template v-if="showMessage">
                <div :class="messageClass">
                    {{ messageText }}
                </div>
            </template>

            <form
                @submit.prevent="submit"
                class="my-10 space-y-6">
                <div class="grid grid-cols-1 gap-6 mb-5">
                    <div>
                        <label class="block mb-2 text-lg font-medium">Users</label>
                        <input
                            v-model="form.invoice_number"
                            disabled
                            type="text"
                            class="w-full px-4 py-2 border border-secondary-200 rounded-md text-secondary-900 text-bluemain" />
                        <div
                            v-if="form.errors.invoice_number"
                            class="text-sm text-red-600">
                            {{ form.errors.invoice_number }}
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-lg font-medium">Users</label>
                        <input
                            v-model="form.user"
                            disabled
                            type="text"
                            class="w-full px-4 py-2 border border-secondary-200 rounded-md text-secondary-900 text-bluemain" />
                        <div
                            v-if="form.errors.user"
                            class="text-sm text-red-600">
                            {{ form.errors.user }}
                        </div>
                    </div>
                </div>

                <hr class="my-5 border-gray-300" />

                <div class="w-full pt-4 md:col-span-2">
                    <button
                        type="submit"
                        class="block w-full px-3 py-2 text-lg text-white rounded bg-bluemain hover:bg-bluemain/60"
                        :disabled="form.processing">
                        Send Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
