<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    clients: Object,
    locations: Array,
    can: Object,
    users: Array,
});

const form = useForm({
    user_id: props.clients.user_id,
    location_id: props.clients.location_id,
    name: props.clients.name,
    surname: props.clients.surname,
    cell_number: props.clients.cell_number,
    email_address: props.clients.email_address,
    company_name: props.clients.company_name,
    company_registration_number: props.clients.company_registration_number,
    agreement: !!props.clients?.agreement,
    identity: null,
    residency: null,
    company_reg_path: null,
    _method: 'PUT',
});

const handleFileUpload = (event, field) => {
    form[field] = event.target.files[0];
};

const submit = () => {
    form.post(route('admin.clientinfor.update', props.clients.id), {
        preserveScroll: true,
        forceFormData: true,
        onError: errors => {
            bookingConflict.value = errors.booking_conflict ?? null;
        },
        onSuccess: () => {
            setTimeout(() => {
                successMessage.value = null;
                Inertia.visit(route('companydetails.index'));
            }, 1500);
        },
    });
};
</script>

<template>
    <Head title="Edit Client" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Client Information</h2>
        </template>

        <div class="p-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-4xl p-6 mx-auto space-y-6">
                    <!-- Search Filter -->
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-2xl font-medium text-black">Edit Client</h3>

                        <Link
                            :href="route('admin.clientinfor.index')"
                            class="inline-block px-2 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
                            Back
                        </Link>
                    </div>

                    <form
                        @submit.prevent="submit"
                        class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- User Details -->
                            <div v-if="can['manage settings']">
                                <label class="block text-lg text-gray-700">User</label>
                                <select
                                    v-model="form.user_id"
                                    class="w-full px-3 py-2 border rounded">
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

                                <img
                                    v-if="props.clients.company_reg_path"
                                    :src="props.clients.company_reg_path"
                                    alt="Company Registration Image"
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
                                    Update Client
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
