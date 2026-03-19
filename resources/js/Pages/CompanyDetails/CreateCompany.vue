<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import { ref, computed } from 'vue';
import StatusFeedback from '@/Components/StatusFeedback.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    locations: Array,
    closedoffices: Array,
    dedicated: Array,
    hotdesks: Array,
    virtuals: Array,
    errors: Object,
    user_type: String,
});

const step = ref(1);
const successMessage = ref(null);
const bookingConflict = ref(null);

const form = useForm({
    location_id: null,
    closed_id: null,
    name: '',
    surname: '',
    cell_number: '',
    email_address: '',
    company_name: '',
    company_registration_number: '',
    identity: null,
    residency: null,
    company_reg_path: null,
    agreement: false,
});

const validateStepOne = () => {
    form.errors = {};

    if (!form.location_id) {
        form.errors.location_id = 'Location is required.';
    }

    if (!form.name) {
        form.errors.name = 'First name is required.';
    }

    if (!form.surname) {
        form.errors.surname = 'Surname is required.';
    }

    validateCellNumber();

    if (!form.email_address) {
        form.errors.email_address = 'Email address is required.';
    }

    validateEmail();

    if (!form.company_name) {
        form.errors.company_name = 'Company name is required.';
    }

    // if (!form.company_registration_number) {
    //     form.errors.company_registration_number = 'Company registration number is required.';
    // }

    if (!form.identity) {
        form.errors.identity = 'ID upload is required.';
    }

    if (!form.company_reg_path) {
        form.errors.company_reg_path = 'Company registration upload is required.';
    }

    if (!form.residency) {
        form.errors.residency = 'Proof of residency is required.';
    }

    if (!form.agreement) {
        form.errors.agreement = 'You must agree to the terms.';
    }

    return Object.keys(form.errors).length === 0;
};

const validateStep = () => {
    if (step.value === 1) return validateStepOne();
    if (step.value === 2) return true;
    return true;
};

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

const nextStep = () => {
    const valid = validateStep();
    if (!valid) return;

    if (step.value === 1) {
        if (props.user_type === 'existing') {
            step.value = 2;
        } else {
            submit();
        }
    } else if (step.value === 2) {
        submit();
    }
};

const prevStep = () => {
    if (step.value > 1) {
        step.value--;
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
    if (!validateStep()) return;

    if (!form.agreement) {
        form.errors.agreement = 'You must agree to the terms and conditions before submitting.';
        return;
    } else {
        form.errors.agreement = null;
    }

    form.transform(data => ({
        ...data,
        closed_rows: closedRows.value,
        dedicated_rows: dedicatedRows.value,
        virtual_rows: virtualRows.value,
        hotdesk_rows: hotdeskRows.value,
    })).post(route('companydetail.store'), {
        preserveScroll: true,
        forceFormData: true,
        onError: errors => {
            bookingConflict.value = errors.booking_conflict ?? null;
        },
        onSuccess: () => {
            successMessage.value = 'Client information saved successfully.';
            bookingConflict.value = null;

            setTimeout(() => {
                Inertia.visit(route('dashboard'));
            }, 2000);
        },
    });
};

// tabs
const activeTab = ref('Closed Offices');

// Closed Offices
const closedRows = ref([{ start_date: '', end_date: '', discount_price: '' }]);

// Dedicated Desks
const dedicatedRows = ref([{ start_date: '', end_date: '', discount_price: '' }]);

// Virtuals
const virtualRows = ref([{ start_date: '', end_date: '', discount_price: '' }]);

// Hot Desks
const hotdeskRows = ref([{ start_date: '', end_date: '', discount_price: '' }]);

// Generic row helpers
const makeRow = () => ({
    start_date: '',
    end_date: '',
    discount_price: '',
});

// Add/remove helpers per category
const addRow = type => {
    switch (type) {
        case 'Closed Offices':
            closedRows.value.push({
                office: {
                    id: '',
                    name: '',
                },
                ...makeRow(),
            });
            break;
        case 'Dedicated Desks':
            dedicatedRows.value.push({ desk: { id: '', name: '' }, ...makeRow() });
            break;
        case 'Virtuals':
            virtualRows.value.push({ virtual: { id: '', name: '' }, ...makeRow() });
            break;
        case 'Hot Desks':
            hotdeskRows.value.push({ hotdesk: { id: '', name: '' }, ...makeRow() });
            break;
    }
};

const removeRow = (type, index) => {
    const targetMap = {
        'Closed Offices': closedRows,
        'Dedicated Desks': dedicatedRows,
        Virtuals: virtualRows,
        'Hot Desks': hotdeskRows,
    };
    const target = targetMap[type];
    if (target.value.length > 1) target.value.splice(index, 1);
};

const rowPrefixes = {
    closed_rows: 'Closed Office',
    dedicated_rows: 'Dedicated Desk',
    virtual_rows: 'Virtual Office',
    hotdesk_rows: 'Hot Desk',
};

const rowFieldErrors = computed(() => {
    const errors = form.errors || {};
    const grouped = {};

    Object.keys(errors).forEach(key => {
        const match = key.match(/^(\w+_rows)\.\d+\.(\w+)$/);
        if (match) {
            const [_, prefix, field] = match;
            if (rowPrefixes[prefix]) {
                if (!grouped[prefix]) grouped[prefix] = new Set();
                grouped[prefix].add(field);
            }
        }
    });

    return Object.entries(grouped).map(([prefix, fields]) => {
        const label = rowPrefixes[prefix];
        const fieldList = Array.from(fields).join(', ');
        return `${label}: ${fieldList}`;
    });
});
</script>

<template>
    <Head title="Company Details" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-semibold leading-tight text-gray-800">Company Details</h2>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="p-6 mt-5 space-y-6 bg-white rounded-md shadow mx7auto max-w-8xl">
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-xl text-black">Add Company Details</h3>

                        <Link
                            :href="route('dashboard')"
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

                    <div
                        v-if="rowFieldErrors.length"
                        class="mt-2 text-sm text-red-600">
                        The following rate sections have validation errors:
                        <ul class="mt-1 list-disc list-inside">
                            <li
                                v-for="error in rowFieldErrors"
                                :key="error">
                                {{ error }}
                            </li>
                        </ul>
                    </div>

                    <form
                        @submit.prevent="submit"
                        class="space-y-6">
                        <!-- Step 1: Always visible -->
                        <div v-show="step === 1">
                            <div class="mb-5">
                                <label class="block text-lg text-gray-700">Location</label>
                                <select
                                    v-on:focus="form.clearErrors('location_id')"
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
                                        v-on:focus="form.clearErrors('name')"
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
                                        v-on:focus="form.clearErrors('surname')"
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
                                        v-on:focus="form.clearErrors('company_name')"
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
                                        v-on:focus="form.clearErrors('company_registration_number')"
                                        v-mask="'####/######/##'"
                                        placeholder="YYYY / NNNNNN / NN"
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
                                        @change="handleFileUpload($event, 'identity')"
                                        v-on:focus="form.clearErrors('identity')"
                                        class="w-full px-3 py-2 border rounded" />
                                    <div
                                        v-if="form.errors.identity"
                                        class="text-sm text-red-600">
                                        {{ form.errors.identity }}
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
                                        @change="handleFileUpload($event, 'residency')"
                                        v-on:focus="form.clearErrors('residency')"
                                        class="w-full px-3 py-2 border rounded" />
                                    <div
                                        v-if="form.errors.residency"
                                        class="text-sm text-red-600">
                                        {{ form.errors.residency }}
                                    </div>
                                </div>
                            </div>

                            <!-- check the agrement -->
                            <div class="my-5">
                                <label class="inline-flex items-center">
                                    <input
                                        type="checkbox"
                                        v-model="form.agreement"
                                        v-on:focus="form.clearErrors('agreement')"
                                        class="w-5 h-5 border-gray-300 rounded text-primary" />
                                    <span class="ml-2 text-lg text-gray-700">
                                        I agree to the terms and conditions
                                    </span>
                                </label>
                                <div
                                    v-if="form.errors.agreement"
                                    class="mt-1 text-sm text-red-600">
                                    {{ form.errors.agreement }}
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Confirmation -->
                        <div v-show="step === 2 && props.user_type === 'existing'">
                            <div>
                                <!-- Tab Buttons -->
                                <div class="flex gap-4 mb-4 border-b">
                                    <button
                                        type="button"
                                        @click="activeTab = 'Closed Offices'"
                                        :class="[
                                            'px-4 py-2 font-semibold',
                                            activeTab === 'Closed Offices'
                                                ? 'border-b-2 border-primary text-primary'
                                                : 'text-gray-500',
                                        ]">
                                        Closed Offices
                                    </button>

                                    <button
                                        type="button"
                                        @click="activeTab = 'Dedicated Desks'"
                                        :class="[
                                            'px-4 py-2 font-semibold',
                                            activeTab === 'Dedicated Desks'
                                                ? 'border-b-2 border-primary text-primary'
                                                : 'text-gray-500',
                                        ]">
                                        Dedicated Desks
                                    </button>

                                    <button
                                        type="button"
                                        @click="activeTab = 'Virtuals'"
                                        :class="[
                                            'px-4 py-2 font-semibold',
                                            activeTab === 'Virtuals'
                                                ? 'border-b-2 border-primary text-primary'
                                                : 'text-gray-500',
                                        ]">
                                        Virtuals
                                    </button>

                                    <button
                                        type="button"
                                        @click="activeTab = 'Hot Desks'"
                                        :class="[
                                            'px-4 py-2 font-semibold',
                                            activeTab === 'Hot Desks'
                                                ? 'border-b-2 border-primary text-primary'
                                                : 'text-gray-500',
                                        ]">
                                        Hot Desks
                                    </button>
                                </div>

                                <!-- Tab Content -->
                                <div v-if="activeTab === 'Closed Offices'">
                                    <div v-if="activeTab === 'Closed Offices'">
                                        <div
                                            v-for="(row, index) in closedRows"
                                            :key="index"
                                            class="grid grid-cols-1 col-span-2 gap-2 mt-5 md:grid-cols-7">
                                            <!-- Closed Office -->
                                            <div class="col-span-2 mb-5">
                                                <label class="block text-lg text-gray-700">Closed Office</label>
                                                <select
                                                    v-model="row.id"
                                                    @change="
                                                        row.name =
                                                            closedoffices.find(o => o.id == row.id)?.office_name || ''
                                                    "
                                                    class="w-full px-3 py-2 border rounded">
                                                    <option value="">Select Closed</option>
                                                    <option
                                                        v-for="item in closedoffices"
                                                        :key="item.id"
                                                        :value="item.id">
                                                        {{ item.office_name }}
                                                    </option>
                                                </select>
                                                <div
                                                    v-if="form.errors?.closedoffices"
                                                    class="text-sm text-red-600">
                                                    {{ form.errors.closedoffices }}
                                                </div>
                                            </div>

                                            <!-- Start Date -->
                                            <div class="mb-5">
                                                <label class="block text-lg text-gray-700">Start Date</label>
                                                <input
                                                    v-model="row.start_date"
                                                    type="date"
                                                    class="w-full px-3 py-2 border rounded" />
                                                <div
                                                    v-if="form.errors.start_date"
                                                    class="text-sm text-red-600">
                                                    {{ form.errors.start_date }}
                                                </div>
                                            </div>

                                            <!-- End Date -->
                                            <div class="mb-5">
                                                <label class="block text-lg text-gray-700">End Date</label>
                                                <input
                                                    v-model="row.end_date"
                                                    type="date"
                                                    class="w-full px-3 py-2 border rounded" />
                                                <div
                                                    v-if="form.errors?.end_date"
                                                    class="text-sm text-red-600">
                                                    {{ form.errors.end_date }}
                                                </div>
                                            </div>

                                            <!-- Price -->
                                            <div class="mb-5">
                                                <label class="block text-lg text-gray-700">Price</label>
                                                <input
                                                    v-model="row.discount_price"
                                                    type="number"
                                                    min="0"
                                                    step="0.01"
                                                    class="w-full px-3 py-2 border rounded" />
                                                <div
                                                    v-if="form.errors?.discount_price"
                                                    class="text-sm text-red-600">
                                                    {{ form.errors.discount_price }}
                                                </div>
                                            </div>

                                            <!-- Add/Remove Buttons -->
                                            <div class="flex items-center px-3 space-x-2">
                                                <button
                                                    type="button"
                                                    @click="addRow('Closed Offices')"
                                                    class="px-2 py-1 text-white bg-green-500 rounded hover:bg-green-600">
                                                    +
                                                </button>
                                                <button
                                                    type="button"
                                                    @click="removeRow('Closed Offices', index)"
                                                    class="px-2 py-1 text-white bg-red-500 rounded hover:bg-red-600"
                                                    :disabled="closedRows.length === 1">
                                                    −
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-else-if="activeTab === 'Dedicated Desks'">
                                    <div
                                        v-for="(row, index) in dedicatedRows"
                                        :key="index"
                                        class="grid grid-cols-1 col-span-2 gap-2 mt-5 md:grid-cols-7">
                                        <div class="col-span-2 mb-5">
                                            <label class="block text-lg text-gray-700">Dedicated Desk</label>
                                            <select
                                                v-model="row.id"
                                                @change="
                                                    row.name = dedicated.find(o => o.id == row.id)?.office_name || ''
                                                "
                                                class="w-full px-3 py-2 border rounded">
                                                <option value="">Select Closed</option>
                                                <option
                                                    v-for="item in dedicated"
                                                    :key="item.id"
                                                    :value="item.id">
                                                    {{ item.office_name }}
                                                </option>
                                            </select>
                                        </div>

                                        <div class="mb-5">
                                            <label class="block text-lg text-gray-700">Start Date</label>
                                            <input
                                                v-model="row.start_date"
                                                type="date"
                                                class="w-full px-3 py-2 border rounded" />
                                        </div>

                                        <div class="mb-5">
                                            <label class="block text-lg text-gray-700">End Date</label>
                                            <input
                                                v-model="row.end_date"
                                                type="date"
                                                class="w-full px-3 py-2 border rounded" />
                                        </div>

                                        <div class="mb-5">
                                            <label class="block text-lg text-gray-700">Price</label>
                                            <input
                                                v-model="row.discount_price"
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                class="w-full px-3 py-2 border rounded" />
                                        </div>

                                        <div class="flex items-center px-3 space-x-2">
                                            <button
                                                type="button"
                                                @click="addRow('Dedicated Desks')"
                                                class="px-2 py-1 text-white bg-green-500 rounded hover:bg-green-600">
                                                +
                                            </button>
                                            <button
                                                type="button"
                                                @click="removeRow('Dedicated Desks', index)"
                                                class="px-2 py-1 text-white bg-red-500 rounded hover:bg-red-600"
                                                :disabled="dedicatedRows.length === 1">
                                                −
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div v-else-if="activeTab === 'Virtuals'">
                                    <div
                                        v-for="(row, index) in virtualRows"
                                        :key="index"
                                        class="grid grid-cols-1 col-span-2 gap-2 mt-5 md:grid-cols-7">
                                        <!-- Virtual -->
                                        <div class="col-span-2 mb-5">
                                            <label class="block text-lg text-gray-700">Virtual Office</label>
                                            <select
                                                v-model="row.id"
                                                @change="
                                                    row.name =
                                                        virtuals.find(o => o.id == row.id)?.virtualoffice_name || ''
                                                "
                                                class="w-full px-3 py-2 border rounded">
                                                <option value="">Select Virtual Office</option>

                                                <option
                                                    v-for="item in virtuals"
                                                    :key="item.id"
                                                    :value="item.id">
                                                    {{ item.virtualoffice_name }} - {{ item?.location.name }}
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Start Date -->
                                        <div class="mb-5">
                                            <label class="block text-lg text-gray-700">Start Date</label>
                                            <input
                                                v-model="row.start_date"
                                                type="date"
                                                class="w-full px-3 py-2 border rounded" />
                                        </div>

                                        <!-- End Date -->
                                        <div class="mb-5">
                                            <label class="block text-lg text-gray-700">End Date</label>
                                            <input
                                                v-model="row.end_date"
                                                type="date"
                                                class="w-full px-3 py-2 border rounded" />
                                        </div>

                                        <!-- Price -->
                                        <div class="mb-5">
                                            <label class="block text-lg text-gray-700">Price</label>
                                            <input
                                                v-model="row.discount_price"
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                class="w-full px-3 py-2 border rounded" />
                                        </div>

                                        <!-- Buttons -->
                                        <div class="flex items-center px-3 space-x-2">
                                            <button
                                                type="button"
                                                @click="addRow('Virtuals')"
                                                class="px-2 py-1 text-white bg-green-500 rounded hover:bg-green-600">
                                                +
                                            </button>
                                            <button
                                                type="button"
                                                @click="removeRow('Virtuals', index)"
                                                class="px-2 py-1 text-white bg-red-500 rounded hover:bg-red-600"
                                                :disabled="virtualRows.length === 1">
                                                −
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div v-else-if="activeTab === 'Hot Desks'">
                                    <div
                                        v-for="(row, index) in hotdeskRows"
                                        :key="index"
                                        class="grid grid-cols-1 col-span-2 gap-2 mt-5 md:grid-cols-7">
                                        <!-- Hot Desk -->
                                        <div class="col-span-2 mb-5">
                                            <label class="block text-lg text-gray-700">Hot Desk</label>
                                            <select
                                                v-model="row.id"
                                                @change="
                                                    row.name = hotdesks.find(o => o.id == row.id)?.help_desk_name || ''
                                                "
                                                class="w-full px-3 py-2 border rounded">
                                                <option value="">Select Closed</option>
                                                <option
                                                    v-for="item in hotdesks"
                                                    :key="item.id"
                                                    :value="item.id">
                                                    {{ item.help_desk_name }}
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Start Date -->
                                        <div class="mb-5">
                                            <label class="block text-lg text-gray-700">Start Date</label>
                                            <input
                                                v-model="row.start_date"
                                                type="date"
                                                class="w-full px-3 py-2 border rounded" />
                                        </div>

                                        <!-- End Date -->
                                        <div class="mb-5">
                                            <label class="block text-lg text-gray-700">End Date</label>
                                            <input
                                                v-model="row.end_date"
                                                type="date"
                                                class="w-full px-3 py-2 border rounded" />
                                        </div>

                                        <!-- Price -->
                                        <div class="mb-5">
                                            <label class="block text-lg text-gray-700">Price</label>
                                            <input
                                                v-model="row.discount_price"
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                class="w-full px-3 py-2 border rounded" />
                                        </div>

                                        <!-- Buttons -->
                                        <div class="flex items-center px-3 space-x-2">
                                            <button
                                                type="button"
                                                @click="addRow('Hot Desks')"
                                                class="px-2 py-1 text-white bg-green-500 rounded hover:bg-green-600">
                                                +
                                            </button>
                                            <button
                                                type="button"
                                                @click="removeRow('Hot Desks', index)"
                                                class="px-2 py-1 text-white bg-red-500 rounded hover:bg-red-600"
                                                :disabled="hotdeskRows.length === 1">
                                                −
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr />
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="flex justify-between pt-4">
                            <button
                                v-if="step > 1"
                                type="button"
                                @click="prevStep"
                                class="px-4 py-2 text-lg text-gray-700 bg-gray-200 rounded hover:bg-gray-300">
                                Back
                            </button>

                            <!-- Next button -->
                            <button
                                v-if="step === 1 && props.user_type === 'existing'"
                                type="button"
                                @click="nextStep"
                                class="px-4 py-2 text-lg text-white rounded bg-bluemain hover:bg-bluemain/60">
                                Next
                            </button>

                            <!-- Submit button -->
                            <button
                                v-if="
                                    (step === 2 && props.user_type === 'existing') ||
                                    (step === 1 && props.user_type === 'new')
                                "
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 text-lg text-white rounded bg-bluemain hover:bg-bluemain/60">
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
