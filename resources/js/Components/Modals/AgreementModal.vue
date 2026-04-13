<script setup>
import { useForm, router } from '@inertiajs/vue3';
import useStatusMessage from './../../Composables/useStatusMessage';

const props = defineProps({
    location_id: Number,
    locations: Array,
    agreement: Array,
    show: Boolean,
    onClose: Function,
    can: Object,
});

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();

const form = useForm({
    location_id: props.location_id,
    agreement: null,
});

const handleFileUpload = (event, field) => {
    form[field] = event.target.files[0];
};

const submit = () => {
    form.post(route('agreement.store'), {
        preserveScroll: true,
        forceFormData: true,

        onSuccess: () => {
            message.value = 'Agreement file Successfully Uploaded!';
            status.value = 'success';

            setTimeout(() => {
                message.value = '';
                status.value = '';
                router.visit(route('companydetail.index'));
            }, 4000);
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
        <div class="w-full max-w-2xl p-4 bg-white rounded shadow-lg sm:p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-2xl sm:text-2xl">Upload Agreement</h2>

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

            <form
                @submit.prevent="submit"
                class="my-10 space-y-6">
                <div>
                    <label class="block mb-2 text-lg font-medium">Location</label>
                    <select
                        v-model="form.location_id"
                        class="w-full px-3 py-2 border rounded">
                        <option value="">Select Location</option>
                        <option
                            v-for="loc in locations"
                            :key="loc.id"
                            :value="loc.id">
                            {{ loc.name }}
                        </option>
                    </select>
                    <div
                        v-if="form.errors.location_id"
                        class="text-sm text-red-600">
                        {{ form.errors.location_id }}
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-6 mb-5">
                    <div>
                        <label class="block mb-2 text-lg font-medium">Upload the Document</label>
                        <div class="relative">
                            <input
                                type="file"
                                @change="handleFileUpload($event, 'agreement')"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-bluemain/10 file:text-bluemain hover:file:bg-bluemain/20 border rounded-lg p-2" />
                        </div>
                        <div
                            v-if="form.progress"
                            class="w-full mt-3 bg-gray-200 rounded-full h-2.5">
                            <div
                                class="bg-bluemain h-2.5 rounded-full transition-all duration-300"
                                :style="{ width: form.progress.percentage + '%' }"></div>
                            <p class="mt-1 text-xs text-right text-gray-500">{{ form.progress.percentage }}%</p>
                        </div>
                        <div
                            v-if="form.errors.agreement"
                            class="text-sm text-red-600">
                            {{ form.errors.agreement }}
                        </div>
                    </div>
                </div>

                <hr class="my-5 border-gray-300" />

                <div class="w-full pt-4 md:col-span-2">
                    <button
                        type="submit"
                        class="block w-full px-3 py-2 text-lg text-white rounded bg-bluemain hover:bg-bluemain/60"
                        :disabled="form.processing">
                        <span v-if="form.processing">Uploading...</span>
                        <span v-else>Upload Agreement</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
