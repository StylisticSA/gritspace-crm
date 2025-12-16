<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { format } from 'date-fns';
import useStatusMessage from '../../../Composables/useStatusMessage';

const props = defineProps({
    users: {
        type: Array,
        required: true,
    },
    allOptions: {
        type: Object,
        required: true,
    },
});

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();

const selectedUserId = ref(null);
const taxRate = ref(15.7);
const currency = ref('ZAR');

const currencySymbols = {
    ZAR: 'R',
    USD: '$',
    EUR: '€',
    GBP: '£',
};
// const selectedUser = computed(() => props.users.find(u => u.id === selectedUserId.value));
// console.log('selectedUser:', selectedUser);

function fillUserFields() {
    const user = props.users.find(u => u.id === selectedUserId.value);
    if (user) {
        form.user_id = user?.id;
        form.user_name = user?.name;
        form.customer_name = user?.company_details?.company_name;
        form.customer_email = user.email;
        form.customer_phone = user?.company_details?.cell_number;
    }
}

const form = useForm({
    user_id: '',
    user_name: '',
    customer_name: '',
    customer_email: '',
    customer_phone: '',

    customer_address: '',
    customer_city: '',

    issued_date: '',
    issued_due_date: '',
    invoice_notes: '',

    subtotal: '',
    tax_amount: '',
    total_amount: '',

    currency: '',

    items: [],
});

const submit = () => {
    form.subtotal = subtotal.value.toFixed(2);
    form.tax_amount = tax.value.toFixed(2);
    form.total_amount = total.value.toFixed(2);
    form.currency = currency.value;

    form.items = items.value.map(i => ({
        item_name: i.description,
        item_quantity: i.quantity,
        item_rate: i.rate,
        item_amount: (i.quantity * i.rate).toFixed(2),
    }));

    form.post(route('admin.invoices.store'), {
        onSuccess: () => {
            message.value = 'Invoice has been Created Successfully.';
            status.value = 'success';

            setTimeout(() => {
                router.reload({ preserveScroll: true });
                router.visit(route('admin.invoices.index'));
            }, 2000);
        },
    });
};

const items = ref([{ description: '', quantity: 1, rate: 0 }]);

const subtotal = computed(() => items.value.reduce((sum, item) => sum + item.quantity * item.rate, 0));

const tax = computed(() => subtotal.value * (taxRate.value / 100));

const total = computed(() => subtotal.value + tax.value);
</script>
<template>
    <Head title="Admin Create Invoices" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-semibold leading-tight text-gray-800">Invoices</h2>
        </template>
        <div class="py-2 px-5 lg:px-0">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="my-5">
                    <div class="flex item-center flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-xl font-medium text-black">Create Invoice</h3>

                        <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-2 space-y-2 sm:space-y-0">
                            <select
                                v-model="currency"
                                class="w-[300px] px-3 py-2 text-base font-medium text-bluemain rounded border border-gray-300">
                                <option value="">Choose Currency</option>
                                <option value="ZAR">South Africa Rand (ZAR)</option>
                                <option value="USD">US Dollar (USD)</option>
                                <option value="EUR">Euro (EUR)</option>
                                <option value="GBP">British Pound (GBP)</option>
                            </select>

                            <Link
                                :href="route('admin.invoices.index')"
                                class="w-full sm:w-auto text-center px-3 py-2 text-base font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
                                Back
                            </Link>
                        </div>
                    </div>
                </div>
                <template v-if="showMessage">
                    <div :class="messageClass">
                        {{ messageText }}
                    </div>
                </template>
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 my-10">
                        <div class="lg:col-span-2 space-y-6">
                            <!-- customer info -->
                            <div class="card p-6 transition-all duration-200">
                                <div class="flex flex-col space-y-1.5 mb-4">
                                    <h2 class="text-lg font-semibold text-secondary-900 text-bluemain">
                                        Customer Information
                                    </h2>
                                </div>
                                <div class="">
                                    <div class="space-y-4">
                                        <div class="mb-10">
                                            <label
                                                class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">
                                                Choose User <span class="text-red-800">*</span>
                                            </label>
                                            <select
                                                v-model="selectedUserId"
                                                @change="fillUserFields"
                                                required
                                                class="w-full px-4 py-2 border border-secondary-200 dark:border-secondary-700 rounded-lg bg-white dark:bg-secondary-900 text-secondary-900 text-bluemain focus:outline-none focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-400">
                                                <option
                                                    disabled
                                                    value="">
                                                    Select customer
                                                </option>
                                                <option
                                                    v-for="user in users"
                                                    :key="user.id"
                                                    :value="user.id">
                                                    {{ user.name }}
                                                </option>
                                            </select>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label
                                                    class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2"
                                                    >Customer Name <span class="text-red-800">*</span></label
                                                ><input
                                                    v-model="form.customer_name"
                                                    placeholder="Enter customer name"
                                                    disabled
                                                    class="w-full px-4 py-2 border border-secondary-200 dark:border-secondary-700 rounded-lg bg-white dark:bg-secondary-900 text-secondary-900 text-bluemain focus:outline-none focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-400"
                                                    type="text" />
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2"
                                                    >Email Address <span class="text-red-800">*</span></label
                                                ><input
                                                    v-model="form.customer_email"
                                                    placeholder="customer@example.com"
                                                    disabled
                                                    class="w-full px-4 py-2 border border-secondary-200 dark:border-secondary-700 rounded-lg bg-white dark:bg-secondary-900 text-secondary-900 text-bluemain focus:outline-none focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-400"
                                                    type="email" />
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label
                                                    class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2"
                                                    >City, State, ZIP</label
                                                ><input
                                                    v-model="form.customer_zip"
                                                    placeholder="City, State, ZIP"
                                                    class="w-full px-4 py-2 border border-secondary-200 dark:border-secondary-700 rounded-lg bg-white dark:bg-secondary-900 text-secondary-900 text-bluemain focus:outline-none focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-400"
                                                    type="text" />
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2"
                                                    >Phone Number</label
                                                ><input
                                                    v-model="form.customer_phone"
                                                    disabled
                                                    placeholder="+1 (555) 000-0000"
                                                    class="w-full px-4 py-2 border border-secondary-200 dark:border-secondary-700 rounded-lg bg-white dark:bg-secondary-900 text-secondary-900 text-bluemain focus:outline-none focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-400"
                                                    type="tel" />
                                            </div>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2"
                                                >Address</label
                                            ><input
                                                v-model="form.customer_address"
                                                placeholder="Street address"
                                                class="w-full px-4 py-2 border border-secondary-200 dark:border-secondary-700 rounded-lg bg-white dark:bg-secondary-900 text-secondary-900 text-bluemain focus:outline-none focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-400"
                                                type="text"
                                                value="" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- items -->
                            <div class="card p-6 transition-all duration-200 bg-white rounded-lg">
                                <div class="flex flex-col space-y-1.5 mb-4">
                                    <div class="flex items-center justify-between">
                                        <h2 class="text-lg font-semibold text-secondary-900 text-bluemain">
                                            Invoice Items
                                        </h2>
                                        <button
                                            @click="items.push({ description: '', quantity: 1, rate: 0 })"
                                            class="block px-4 py-1 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/80"
                                            type="button">
                                            Add Item
                                        </button>
                                    </div>
                                </div>
                                <div class="">
                                    <div class="space-y-4">
                                        <div
                                            v-for="(item, index) in items"
                                            :key="index"
                                            class="p-4 border border-secondary-200 dark:border-secondary-700 rounded-lg">
                                            <div class="flex items-start gap-4">
                                                <div class="flex-1 space-y-4">
                                                    <div>
                                                        <label class="block text-sm font-medium mb-2">
                                                            Office Name <span class="text-red-800">*</span>
                                                        </label>

                                                        <select
                                                            v-model="item.description"
                                                            required
                                                            class="w-full px-4 py-2 border border-secondary-200 dark:border-secondary-700 rounded-lg bg-white dark:bg-secondary-900 text-secondary-900 text-bluemain focus:outline-none focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-400">
                                                            <option
                                                                disabled
                                                                value="">
                                                                Select Office Name
                                                            </option>
                                                            <option
                                                                v-for="option in props.allOptions"
                                                                :key="option.id"
                                                                :value="`${option.name} - ${option.location}`">
                                                                {{ option.name }} - {{ option.location }}
                                                            </option>
                                                        </select>
                                                        <div
                                                            v-if="form.errors['items.' + index + '.description']"
                                                            class="text-sm text-red-600">
                                                            {{ form.errors['items.' + index + '.description'] }}
                                                        </div>
                                                    </div>

                                                    <div class="grid grid-cols-3 gap-4">
                                                        <div>
                                                            <label class="block text-sm font-medium mb-2">
                                                                Quantity <span class="text-red-800">*</span>
                                                            </label>
                                                            <input
                                                                v-model.number="item.quantity"
                                                                min="1"
                                                                type="number"
                                                                class="w-full px-4 py-2 border rounded-lg" />
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium mb-2">
                                                                Rate <span class="text-red-800">*</span>
                                                            </label>
                                                            <div class="relative">
                                                                <span
                                                                    class="absolute left-3 top-1/2 transform -translate-y-1/2 text-secondary-500">
                                                                    {{ currencySymbols[currency] }}
                                                                </span>
                                                                <input
                                                                    v-model.number="item.rate"
                                                                    step="0.01"
                                                                    min="0"
                                                                    type="number"
                                                                    class="w-full pl-8 pr-4 py-2 border rounded-lg" />
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium mb-2">Amount</label>
                                                            <div class="px-4 py-2 bg-secondary-50 rounded-lg">
                                                                <span class="font-medium"
                                                                    >{{ currencySymbols[currency]
                                                                    }}{{ (item.quantity * item.rate).toFixed(2) }}</span
                                                                >
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-5">
                                                <!-- Buttons -->
                                                <div class="flex justify-between items-end">
                                                    <button
                                                        type="button"
                                                        @click="items.push({ description: '', quantity: 1, rate: 0 })"
                                                        class="px-3 py-1 text-sm font-medium text-white bg-green-600 rounded hover:bg-green-700">
                                                        Add
                                                    </button>
                                                    <button
                                                        type="button"
                                                        @click="items.splice(index, 1)"
                                                        class="px-3 py-1 text-sm font-medium text-white bg-red-600 rounded hover:bg-red-700">
                                                        Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- notes -->
                        </div>
                        <div class="space-y-6 mt-5">
                            <!-- invoice details -->
                            <div class="card p-6 transition-all duration-200 bg-white rounded-lg">
                                <div class="flex flex-col space-y-1.5 mb-4">
                                    <h2 class="text-lg font-semibold text-secondary-900 text-bluemain">
                                        Invoice Details
                                    </h2>
                                </div>
                                <div class="">
                                    <div class="space-y-4">
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2"
                                                >Issue Date <span class="text-red-800">*</span></label
                                            ><input
                                                v-model="form.issued_date"
                                                class="w-full px-4 py-2 border border-secondary-200 dark:border-secondary-700 rounded-lg bg-white dark:bg-secondary-900 text-secondary-900 text-bluemain focus:outline-none focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-400"
                                                type="date" />
                                            <div
                                                v-on:focus="form.clearErrors('issued_date')"
                                                v-if="form.errors.issued_date"
                                                class="text-sm text-red-600">
                                                {{ form.errors.issued_date }}
                                            </div>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2"
                                                >Due Date <span class="text-red-800">*</span></label
                                            ><input
                                                v-model="form.issued_due_date"
                                                class="w-full px-4 py-2 border border-secondary-200 dark:border-secondary-700 rounded-lg bg-white dark:bg-secondary-900 text-secondary-900 text-bluemain focus:outline-none focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-400"
                                                type="date" />
                                            <div
                                                v-on:focus="form.clearErrors('issued_due_date')"
                                                v-if="form.errors.issued_due_date"
                                                class="text-sm text-red-600">
                                                {{ form.errors.issued_due_date }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- summary -->
                            <div class="card p-6 transition-all duration-200 bg-white rounded-lg">
                                <div class="flex flex-col space-y-1.5 mb-4">
                                    <h2 class="text-lg font-semibold text-secondary-900 text-bluemain">Summary</h2>
                                </div>

                                <div class="">
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-secondary-600 dark:text-secondary-400">Subtotal:</span>
                                            <span class="font-medium text-secondary-900 text-bluemain">
                                                {{ currencySymbols[currency] }}{{ subtotal.toFixed(2) }}
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-secondary-600 dark:text-secondary-400">
                                                Tax ({{ taxRate === 0 ? 'Already Included' : taxRate }}%):
                                            </span>
                                            <span class="font-medium text-secondary-900 text-bluemain">
                                                {{ currencySymbols[currency] }}{{ tax.toFixed(2) }}
                                            </span>
                                        </div>
                                        <div class="pt-3 border-t border-secondary-200 dark:border-secondary-700">
                                            <div class="flex items-center justify-between">
                                                <span class="font-semibold text-secondary-900 text-bluemain"
                                                    >Total:</span
                                                >
                                                <span class="text-xl font-bold text-primary-600 dark:text-primary-400">
                                                    {{ currencySymbols[currency] }}{{ total.toFixed(2) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- actions -->
                            <div class="card p-6 transition-all duration-200 bg-white rounded-lg">
                                <div class="flex flex-col space-y-1.5 mb-4">
                                    <h2 class="text-lg font-semibold text-secondary-900 text-bluemain">Actions</h2>
                                </div>
                                <div class="">
                                    <div class="space-y-3">
                                        <button
                                            type="submit"
                                            class="block w-full px-4 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
                                            Create Invoice
                                        </button>

                                        <Link
                                            :href="route('admin.invoices.index')"
                                            class="block w-full text-center px-4 py-2 text-lg font-medium text-white rounded bg-gray-500 hover:bg-bluemain/80">
                                            Cancel
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
