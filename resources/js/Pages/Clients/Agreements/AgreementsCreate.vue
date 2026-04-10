<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import useStatusMessage from './../../../Composables/useStatusMessage';

const props = defineProps({
    users: Array,
    locations: Array,
    can: Object,
});

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();

const form = useForm({
    location_id: '',
    user_id: '',
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
                router.visit(route('admin.agreement.index'));
            }, 3000);
        },
        onError: errors => {
            message.value = Object.values(errors).join('\n');
            status.value = 'deleted';
        },
    });
};
</script>

<template>
    <Head title="Agreements Uploads" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between space-x-5">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Agreements Uploads</h2>
                <!-- <button
                    @click="showAgreementModal = true"
                    class="px-2 py-2 text-lg text-white rounded bg-bluemain hover:bluemain/60">
                    Upload Agreement
                </button> -->
            </div>
        </template>

        <div class="py-12">
            <div class="px-4 mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="inline-block py-2 text-2xl font-medium text-black">Add Agreement</h3>

                    <Link
                        :href="route('admin.agreement.index')"
                        class="inline-block px-2 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
                        Back
                    </Link>
                </div>
                <template v-if="showMessage">
                    <div :class="messageClass">
                        {{ messageText }}
                    </div>
                </template>
                <form
                    @submit.prevent="submit"
                    class="my-6 space-y-5 md:my-10">
                    <div>
                        <label class="block mb-2 text-lg font-medium">User</label>
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
                    <!-- Location Selection -->
                    <div class="flex flex-col">
                        <label class="block mb-1.5 text-lg font-medium md:text-lg">Location</label>
                        <select
                            v-model="form.location_id"
                            class="w-full h-12 px-4 py-2 bg-white border rounded appearance-none focus:ring-2 focus:ring-bluemain focus:border-bluemain outline-none transition-all">
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
                            class="mt-1 text-sm text-red-600">
                            {{ form.errors.location_id }}
                        </div>
                    </div>

                    <!-- File Upload -->
                    <div class="flex flex-col">
                        <label class="block mb-1.5 text-lg font-medium md:text-lg">Upload the Document</label>
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
                            class="mt-1 text-sm text-red-600">
                            {{ form.errors.agreement }}
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
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
    </AuthenticatedLayout>
</template>
