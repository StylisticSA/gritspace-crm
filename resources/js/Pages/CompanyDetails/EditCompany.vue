<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import { ref } from 'vue';
import StatusFeedback from '@/Components/StatusFeedback.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    clients: Object,
    locations: Array,
});

console.log('so', props.clients);
const successMessage = ref(null);
const bookingConflict = ref(null);

const form = useForm({
    location_id: props.clients?.location_id ?? '',
    name: props.clients?.name ?? '',
    surname: props.clients?.surname ?? '',
    cell_number: props.clients?.cell_number ?? '',
    email_address: props.clients?.email_address ?? '',
    company_name: props.clients?.company_name ?? '',
    company_registration_number: props.clients?.company_registration_number ?? '',
    agreement: !!props.clients?.agreement,
    identity: null,
    residency: null,
    company_reg_path: null,
    _method: 'PUT',
});

const validateCellNumber = () => {
    const pattern = /^\+?\d{10,15}$/;
    if (!form.cell_number) {
        form.errors.cell_number = 'Cell number is required.';
    } else if (!pattern.test(form.cell_number)) {
        form.errors.cell_number = 'Enter a valid cell number.';
    } else {
        form.errors.cell_number = null;
    }
};

const handleFileUpload = (event, field) => {
    form[field] = event.target.files[0];
};

const submit = () => {
    validateCellNumber();

    if (!form.agreement) {
        form.errors.agreement = 'You must agree to the terms and conditions before submitting.';
        return;
    } else {
        form.errors.agreement = null;
    }

    if (form.errors.cell_number) return;

    form.post(route('companydetail.update', props.clients.id), {
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
                Inertia.visit(route('companydetails.index'));
            }, 1500);
        },
    });
};
</script>

<template>
    <Head title="Edit Company Details" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Company Details</h2>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="p-6 mt-5 space-y-6 bg-white rounded-md shadow mx7auto max-w-8xl">
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-2xl text-black">Edit Company Details</h3>

                        <Link
                            :href="route('companydetail.index')"
                            class="inline-block px-2 py-2 text-lg text-white rounded bg-bluemain hover:bg-bluemain/60">
                            Back
                        </Link>
                    </div>

                    <StatusFeedback
                        :conflict="bookingConflict"
                        :success="successMessage" />

                    <div
                        v-if="form.errors.agreement"
                        class="mt-1 text-sm text-red-600">
                        {{ form.errors.agreement }}
                    </div>

                    <form
                        @submit.prevent="submit"
                        enctype="multipart/form-data"
                        class="space-y-6">
                        <!-- User Details -->
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
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
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

                            <div class="mt-4">
                                <label class="block text-lg text-gray-700">Upload your ID</label>

                                <!-- Show existing image if available -->
                                <img
                                    v-if="props.clients.identity_path"
                                    :src="props.clients.identity_path"
                                    alt="Current ID"
                                    class="w-48 h-48 mb-2 border rounded" />

                                <input
                                    type="file"
                                    @change="handleFileUpload($event, 'identity')"
                                    class="w-full px-3 py-2 border rounded" />

                                <div
                                    v-if="form.errors.identity"
                                    class="text-sm text-red-600">
                                    {{ form.errors.identity }}
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="block text-lg text-gray-700">Proof of Residency</label>

                                <!-- Show existing image if available -->
                                <img
                                    v-if="props.clients.residency_path"
                                    :src="props.clients.residency_path"
                                    alt="Current Residency"
                                    class="w-48 h-48 mb-2 border rounded" />

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

                            <div class="mt-4">
                                <label class="block text-lg text-gray-700">Company Registration</label>

                                <!-- Show existing image if available -->
                                <img
                                    v-if="props.clients.company_reg_path"
                                    :src="props.clients.company_reg_path"
                                    alt="Current Residency"
                                    class="w-48 h-48 mb-2 border rounded" />

                                <input
                                    type="file"
                                    @change="handleFileUpload($event, 'company_reg_path')"
                                    class="w-full px-3 py-2 border rounded" />

                                <div
                                    v-if="form.errors.company_reg_path"
                                    class="text-sm text-red-600">
                                    {{ form.errors.company_reg_path }}
                                </div>
                            </div>
                        </div>
                        <!-- check the agrement -->
                        <div class="my-5">
                            <label class="inline-flex items-center mt-2">
                                <input
                                    type="checkbox"
                                    v-model="form.agreement"
                                    class="w-5 h-5 rounded text-primary" />
                                <span class="ml-2 text-bluemain">I agree to the terms and conditions</span>
                            </label>
                        </div>
                        <div class="w-full pt-4 md:col-span-2">
                            <button
                                type="submit"
                                class="block w-full px-3 py-2 text-lg text-white rounded bg-bluemain hover:bg-bluemain/60"
                                :disabled="form.processing">
                                Update Company Details
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
