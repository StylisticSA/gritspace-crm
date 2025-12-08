<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import { ref, watch } from 'vue';
import StatusFeedback from '@/Components/StatusFeedback.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import GlobalNoteModal from '@/Components/Modals/NoteModal.vue';

const props = defineProps({
    locations: Array,
    users: Array,
    can: Object,
});

const showNoteModal = ref(false);

const successMessage = ref(null);
const bookingConflict = ref(null);

const form = useForm({
    user_id: '',
    location_id: '',
    name: '',
    surname: '',
    cell_number: '',
    email_address: '',
    company_name: '',
    company_registration_number: '',
    identity: null,
    residency: null,
    agreement: true,
    company_reg_path: null,
});

const validateCellNumber = () => {
    // Require +27 followed by exactly 9 digits
    const pattern = /^\+\d{1,3}\d{7,12}$/;

    if (!form.cell_number) {
        form.errors.cell_number = 'Cell number is required.';
    } else if (!pattern.test(form.cell_number)) {
        form.errors.cell_number = 'Enter a valid South African cell number (e.g. +27-).';
    } else {
        form.errors.cell_number = null;
    }
};

const handleFileUpload = (event, field) => {
    form[field] = event.target.files[0];
};

const submit = () => {
    validateCellNumber();

    if (form.errors.cell_number) return;

    form.post(route('admin.clientinfor.store'), {
        preserveScroll: true,
        forceFormData: true,
        onError: errors => {
            bookingConflict.value = errors.booking_conflict ?? null;
        },
        onSuccess: () => {
            successMessage.value = 'Closed Office booked successfully!';
            bookingConflict.value = null;

            setTimeout(() => {
                successMessage.value = null;
                Inertia.visit(route('admin.clientinfor.index'));
            }, 1500);
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
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Client Information</h2>
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

                    <StatusFeedback
                        :conflict="bookingConflict"
                        :success="successMessage" />

                    <div
                        v-if="form.errors.residency"
                        class="mt-1 text-sm text-red-600">
                        {{ form.errors.residency }}
                    </div>

                    <div
                        v-if="form.errors.identity"
                        class="mt-1 text-sm text-red-600">
                        {{ form.errors.identity }}
                    </div>

                    <div
                        v-if="showError"
                        class="p-3 text-white bg-red-600">
                        {{ form.errors.available }}
                    </div>

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
                                <label class="block text-lg text-gray-700">Upload Client ID</label>
                                <input
                                    type="file"
                                    @change="handleFileUpload($event, 'identity')"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.indentity"
                                    class="text-sm text-red-600">
                                    {{ form.errors.indentity }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-lg text-gray-700">Client Proof of Residency</label>
                                <input
                                    type="file"
                                    @change="handleFileUpload($event, 'residency')"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.residency"
                                    class="text-sm text-red-600">
                                    {{ form.errors.residency }}
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

                            <div class="w-full pt-4 md:col-span-2">
                                <button
                                    type="submit"
                                    class="block w-full px-3 py-2 text-lg text-white rounded bg-bluemain hover:bg-bluemain/60"
                                    :disabled="form.processing">
                                    Add Client
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
