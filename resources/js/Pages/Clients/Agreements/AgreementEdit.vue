<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import useStatusMessage from '../../../Composables/useStatusMessage';

const props = defineProps({
    users: Array,
    agreement: Object,
    locations: Array,
    can: Object,
    users: Array,
});

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();

const form = useForm({
    user_id: props.agreement.user_id,
    location_id: props.agreement.location_id,
    agreement: null,
    _method: 'PUT',
});

const handleFileUpload = (event, field) => {
    form[field] = event.target.files[0];
};

const submit = () => {
    form.post(route('admin.agreement.update', props.agreement.id), {
        preserveScroll: true,
        forceFormData: true,

        onSuccess: () => {
            message.value = 'The document has been updated.';
            status.value = 'success';

            setTimeout(() => {
                router.visit(route('admin.agreement.index'));
            }, 2000);
        },
    });
};
</script>

<template>
    <Head title="Edit Agreement" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Update Agreement</h2>
        </template>

        <div class="p-2">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="max-w-3xl p-6 mx-auto space-y-6">
                    <!-- Search Filter -->
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-2xl font-medium text-black">Edit Agreement</h3>

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
                        class="space-y-6">
                        <!-- User Details -->
                        <div>
                            <label class="block text-lg text-gray-700">User</label>
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
                                v-if="form.errors.location_id"
                                class="text-sm text-red-600">
                                {{ form.errors.location_id }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-lg text-gray-700">Location</label>
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

                        <div class="mt-4">
                            <label class="block mb-3 text-lg text-gray-700"
                                >Upload your ID

                                <a
                                    v-if="agreement.agreement"
                                    :href="agreement.agreement"
                                    target="_blank"
                                    class="pl-5 underline text-primary">
                                    View Agreement
                                </a>
                            </label>
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

                        <input
                            type="hidden"
                            v-model="form.user_id" />

                        <div class="w-full pt-4 md:col-span-2">
                            <button
                                type="submit"
                                class="block w-full px-3 py-2 text-lg text-white rounded bg-bluemain hover:bg-bluemain/60"
                                :disabled="form.processing">
                                <span v-if="form.processing">Uploading...</span>
                                <span v-else>Update Agreement</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
