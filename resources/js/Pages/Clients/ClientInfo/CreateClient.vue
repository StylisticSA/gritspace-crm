<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import useStatusMessage from './../../../Composables/useStatusMessage';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import GlobalNoteModal from '@/Components/Modals/NoteModal.vue';

const props = defineProps({
    locations: Array,
    users: Array,
    can: Object,
});

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();
const showNoteModal = ref(false);

const form = useForm({
    user_id: '',
    location_id: '',
    name: '',
    surname: '',
    cell_number: '',
    email_address: '',
    company_name: '',
    company_registration_number: '',
    identity_path: null,
    residency_path: null,
    company_reg_path: null,
    agreement: true,
});

const validateCellNumber = () => {
    const pattern = /^\d{3}\s\d{3}\s\d{4}$/;

    if (!form.cell_number) {
        form.errors.cell_number = 'Cell number is required.';
    } else if (!pattern.test(form.cell_number)) {
        form.errors.cell_number = 'Enter a valid South African cell number (e.g. 089 897 1234).';
    } else {
        delete form.errors.cell_number;
    }
};

const validateEmail = () => {
    const pattern = /^[^@]+@[^@]+\.[a-zA-Z]{2,6}$/;

    if (!form.email_address) {
        form.errors.email_address = 'Email address is required.';
    } else if (!pattern.test(form.email_address)) {
        form.errors.email_address = 'Enter a valid email address (e.g. simple@domain.com).';
    } else {
        delete form.errors.email_address;
    }
};

const handleFileUpload = (event, field) => {
    const file = event.target.files[0];
    if (!file) return;

    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
    const maxSize = 5 * 1024 * 1024; // 5 MB

    if (!allowedTypes.includes(file.type)) {
        form.errors[field] = 'Only PDF, JPEG, JPG, or PNG files are allowed.';
        form[field] = null;
        return;
    }

    if (file.size > maxSize) {
        form.errors[field] = 'File size must not exceed 5 MB.';
        form[field] = null;
        return;
    }

    // Valid file
    form.errors[field] = null;
    form[field] = file;
};

const submit = () => {
    validateCellNumber();
    validateEmail();

    if (form.errors.cell_number) return;

    form.post(route('admin.clientinfor.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            message.value = 'Client Information Saved successfully!';
            status.value = 'success';

            setTimeout(() => {
                router.visit(route('admin.clientinfor.index'));
            }, 3000);
        },
        onError: errors => {
            message.value = Object.values(errors).join('\n');
            status.value = 'deleted';
        },
    });
};

const showError = ref(false);

watch(
    () => form.errors.available,
    newVal => {
        if (newVal) {
            showError.value = true;
            setTimeout(() => {
                showError.value = false;
            }, 5000);
        }
    }
);
</script>

<template>
    <Head title="Add Client" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between space-x-5">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Clients Information</h2>
                <button
                    @click="showNoteModal = true"
                    class="px-2 py-2 text-lg text-white rounded bg-primary">
                    Add Note
                </button>
            </div>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-4xl p-6 mx-auto space-y-6">
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-2xl text-black">Add Client</h3>

                        <Link
                            :href="route('admin.clientinfor.index')"
                            class="inline-block px-2 py-2 text-lg text-white rounded bg-bluemain hover:bg-bluemain/60">
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
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div v-if="can['manage settings']">
                                <label class="block text-lg text-gray-700">User</label>
                                <select
                                    v-model="form.user_id"
                                    class="w-full px-3 py-2 border rounded">
                                    <option value="">Select User</option>
                                    <option
                                        v-for="us in users"
                                        :key="us.id"
                                        :value="us.id">
                                        {{ us.name }}
                                    </option>
                                </select>
                                <div
                                    v-if="form.errors.user_id"
                                    class="text-sm text-red-600">
                                    {{ form.errors.user_id }}
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
                            <div>
                                <label class="block text-lg text-gray-700">First Name</label>
                                <input
                                    type="text"
                                    v-model="form.name"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.name"
                                    class="text-sm text-red-600">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-lg text-gray-700">Surname</label>
                                <input
                                    type="text"
                                    v-model="form.surname"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.surname"
                                    class="text-sm text-red-600">
                                    {{ form.errors.surname }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-lg text-gray-700">Cell Number</label>
                                <input
                                    type="text"
                                    v-model="form.cell_number"
                                    @blur="validateCellNumber"
                                    v-mask="'### ### ####'"
                                    placeholder="### ### ####"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.cell_number"
                                    class="text-sm text-red-600">
                                    {{ form.errors.cell_number }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-lg text-gray-700">Email Address</label>
                                <input
                                    type="email"
                                    v-model="form.email_address"
                                    @blur="validateEmail"
                                    v-on:focus="form.clearErrors('email_address')"
                                    placeholder="example@domain.com"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.email_address"
                                    class="text-sm text-red-600">
                                    {{ form.errors.email_address }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-lg text-gray-700">Company Name</label>
                                <input
                                    type="text"
                                    v-model="form.company_name"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.company_name"
                                    class="text-sm text-red-600">
                                    {{ form.errors.company_name }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-lg text-gray-700">Company Registration Number</label>
                                <input
                                    type="text"
                                    v-model="form.company_registration_number"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.company_registration_number"
                                    class="text-sm text-red-600">
                                    {{ form.errors.company_registration_number }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-lg text-gray-700">Upload your ID</label>
                                <input
                                    type="file"
                                    @change="handleFileUpload($event, 'identity_path')"
                                    v-on:focus="form.clearErrors('identity_path')"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.identity_path"
                                    class="text-sm text-red-600">
                                    {{ form.errors.identity_path }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-lg text-gray-700">Upload Company Registration</label>
                                <input
                                    type="file"
                                    @change="handleFileUpload($event, 'company_reg_path')"
                                    v-on:focus="form.clearErrors('company_reg_path')"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.company_reg_path"
                                    class="text-sm text-red-600">
                                    {{ form.errors.company_reg_path }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-lg text-gray-700">Proof of Residency</label>
                                <input
                                    type="file"
                                    @change="handleFileUpload($event, 'residency_path')"
                                    v-on:focus="form.clearErrors('residency_path')"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.residency_path"
                                    class="text-sm text-red-600">
                                    {{ form.errors.residency_path }}
                                </div>
                            </div>

                            <div class="w-full pt-4 md:col-span-2">
                                <button
                                    v-if="can['create client details']"
                                    type="submit"
                                    class="block w-full px-3 py-2 text-lg text-white rounded bg-bluemain hover:bg-bluemain/60"
                                    :disabled="form.processing">
                                    <span v-if="form.processing">Uploading...</span>
                                    <span v-else> Add Client</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <GlobalNoteModal
                :users="users"
                :show="showNoteModal"
                :onClose="() => (showNoteModal = false)" />
        </div>
    </AuthenticatedLayout>
</template>
