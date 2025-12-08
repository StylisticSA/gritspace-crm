<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import { ref, computed } from 'vue';

const props = defineProps({
    locations: Array,
    categories: Array,
    closedoffices: Array,
    users: Array,
    dedicateddesks: Array,
    boardrooms: Array,
    virtuals: Array,
    hotdesks: Array,
});

const successMessage = ref(null);
const errorsValidation = ref({});

const form = useForm({
    user_id: null,
    location_id: null,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        closed_rows: closedRows.value,
        dedicated_rows: dedicatedRows.value,
        virtual_rows: virtualRows.value,
        hotdesk_rows: hotdeskRows.value,
    })).post(route('admin.clientrates.store'), {
        preserveScroll: true,
        onError: errors => {
            const friendly = {};

            for (const [key, message] of Object.entries(errors)) {
                if (key.startsWith('closed_rows')) {
                    friendly.closed_rows =
                        'Please check the Closed row dates — Start Date should not be greater than End Date.';
                } else if (key.startsWith('dedicated_rows')) {
                    friendly.dedicated_rows =
                        'Please check the Dedicated row dates — Start Date should not be greater than End Date.';
                } else if (key.startsWith('virtual_rows')) {
                    friendly.virtual_rows =
                        'Please check the Virtual row dates — Start Date should not be greater than End Date.';
                } else if (key.startsWith('hotdesk_rows')) {
                    friendly.hotdesk_rows =
                        'Please check the Hotdesk row dates — Start Date should not be greater than End Date.';
                } else {
                    friendly[key] = message;
                }
            }

            errorsValidation.value = friendly;
        },
        onSuccess: () => {
            successMessage.value = 'Client Rates has been Saved successfully!';
            bookingConflict.value = null;

            setTimeout(() => {
                successMessage.value = null;
            }, 1500);
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
</script>

<template>
    <Head title="Add Discounted Rates" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Discounted Rates</h2>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-4xl p-6 mx-auto space-y-6">
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-2xl text-black">Add Discounted Rate</h3>

                        <Link
                            :href="route('admin.clientrates.index')"
                            class="inline-block px-2 py-2 text-lg text-white rounded bg-bluemain hover:bg-bluemain/60">
                            Back
                        </Link>
                    </div>

                    <div
                        v-if="Object.keys(errorsValidation).length"
                        class="p-3 text-white bg-red-600 text-md">
                        <div
                            v-for="(msg, key) in errorsValidation"
                            :key="key">
                            {{ msg }}
                        </div>
                    </div>

                    <form
                        @submit.prevent="submit"
                        class="space-y-6">
                        <!-- User Details -->

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="block text-lg text-gray-700">Users</label>
                                <select
                                    v-model="form.user_id"
                                    v-on:focus="form.clearErrors('user_id')"
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
                            <div>
                                <label class="block text-lg text-gray-700">Location</label>
                                <select
                                    v-model="form.location_id"
                                    v-on:focus="form.clearErrors('location_id')"
                                    class="w-full px-3 py-2 border rounded">
                                    <option
                                        value=""
                                        default>
                                        Select Location
                                    </option>
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
                        </div>
                        <div class="py-10">
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
                                    Virtual Offices
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
                                                class="px-5 py-1 text-white bg-green-500 rounded hover:bg-green-600">
                                                Add
                                            </button>
                                            <button
                                                type="button"
                                                @click="removeRow('Closed Offices', index)"
                                                class="px-2 py-1 text-white bg-red-500 rounded hover:bg-red-600"
                                                :disabled="closedRows.length === 1">
                                                Remove
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
                                                row.name = dedicateddesks.find(o => o.id == row.id)?.office_name || ''
                                            "
                                            class="w-full px-3 py-2 border rounded">
                                            <option value="">Select Dedicated</option>
                                            <option
                                                v-for="item in dedicateddesks"
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
                                            Add
                                        </button>
                                        <button
                                            type="button"
                                            @click="removeRow('Dedicated Desks', index)"
                                            class="px-2 py-1 text-white bg-red-500 rounded hover:bg-red-600"
                                            :disabled="dedicatedRows.length === 1">
                                            Remove
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
                                                row.name = virtuals.find(o => o.id == row.id)?.virtualoffice_name || ''
                                            "
                                            class="w-full px-3 py-2 border rounded">
                                            <option value="">Select Closed</option>
                                            <option
                                                v-for="item in virtuals"
                                                :key="item.id"
                                                :value="item.id">
                                                {{ item.virtualoffice_name }}
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
                                            Add
                                        </button>
                                        <button
                                            type="button"
                                            @click="removeRow('Virtuals', index)"
                                            class="px-2 py-1 text-white bg-red-500 rounded hover:bg-red-600"
                                            :disabled="virtualRows.length === 1">
                                            Remove
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
                                            Add
                                        </button>
                                        <button
                                            type="button"
                                            @click="removeRow('Hot Desks', index)"
                                            class="px-2 py-1 text-white bg-red-500 rounded hover:bg-red-600"
                                            :disabled="hotdeskRows.length === 1">
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="w-full pt-4 md:col-span-2">
                            <button
                                type="submit"
                                class="block w-full px-3 py-2 text-lg text-white rounded bg-bluemain hover:bg-bluemain/60"
                                :disabled="form.processing">
                                Add Client Rate
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
