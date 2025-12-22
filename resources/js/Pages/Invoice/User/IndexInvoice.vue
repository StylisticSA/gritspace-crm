<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import InvoiceActionModal from '@/Components/Modals/Invoice/InvoiceActionModal.vue';

const props = defineProps({
    invoices: Object,
    can: Object,
    filters: Object,
});

const showInvoiceModal = ref(false);
const selectedInvoiceId = ref(null);

const currencySymbols = {
    ZAR: 'R',
    USD: '$',
    EUR: '€',
    GBP: '£',
};

const searchQuery = ref(props.filters?.search || '');

watch(searchQuery, value => {
    router.get(
        route('user.invoice'),
        { search: value },
        {
            preserveState: true,
            replace: true,
        }
    );
});

function formatAmount(invoice) {
    const symbol = currencySymbols[invoice.currency] || invoice.currency;
    return `${symbol} ${Number(invoice.total_amount).toFixed(2)}`;
}

const formatDate = date => {
    if (!date) return '—';
    const d = new Date(date);
    return d.toLocaleDateString('en-ZA', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
};

function viewInvoice(Id) {
    if (!Id) return;

    router.visit(`/user/invoices/${Id}`);
}
</script>
<template>
    <Head title="Admin Invoices" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Invoices</h2>
        </template>

        <div class="py-12 px-5 lg:px-0">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="flex flex-col gap-2 mb-10 sm:flex-row sm:items-center sm:justify-between">
                    <div class="space-x-2 flex">
                        <Link
                            :href="route('dashboard')"
                            class="inline-block w-full px-3 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
                            Dashboard
                        </Link>
                    </div>

                    <!-- Second div wider on desktop, full width on mobile -->
                    <div class="flex justify-items-center flex-col sm:flex-row gap-4 w-full sm:w-2/4">
                        <div class="relative flex-1">
                            <input
                                v-model="searchQuery"
                                placeholder="Search invoices..."
                                class="w-full pl-10 pr-4 py-2 border rounded" />
                        </div>
                    </div>
                </div>

                <div class="card p-6 transition-all duration-200 bg-white rounded-lg">
                    <div class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead
                                    class="bg-secondary-50 dark:bg-secondary-800/50 border-b border-secondary-200 dark:border-secondary-700">
                                    <tr>
                                        <th
                                            class="px-6 py-4font-semibold text-left text-xs text-secondary-600 dark:text-secondary-400 uppercase tracking-wider">
                                            Invoice
                                        </th>
                                        <th
                                            class="px-6 py-4 font-semibold text-left text-xs text-secondary-600 dark:text-secondary-400 uppercase tracking-wider">
                                            Customer
                                        </th>
                                        <th
                                            class="px-6 py-4 font-semibold text-left text-xs text-secondary-600 dark:text-secondary-400 uppercase tracking-wider">
                                            Amount
                                        </th>
                                        <th
                                            class="px-6 py-4 font-semibold text-left text-xs text-secondary-600 dark:text-secondary-400 uppercase tracking-wider">
                                            Creation Date
                                        </th>
                                        <th
                                            class="px-6 py-4 font-semibold text-left text-xs text-secondary-600 dark:text-secondary-400 uppercase tracking-wider">
                                            Due Date
                                        </th>
                                        <th
                                            class="px-6 py-4 font-semibold text-left text-xs text-secondary-600 dark:text-secondary-400 uppercase tracking-wider">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                                    <tr
                                        v-for="invoice in invoices.data"
                                        :key="invoice.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button
                                                @click="viewInvoice(invoice.id)"
                                                class="text-sm font-medium text-primary hover:text-primary/60">
                                                {{ invoice.invoice_number }}
                                            </button>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm">
                                                <div class="font-semibold text-secondary-900 text-bluemain">
                                                    {{ invoice.user_name }}
                                                </div>
                                                <div class="text-secondary-500 dark:text-secondary-400">
                                                    {{ invoice.customer_email }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-secondary-900 text-bluemain">
                                                {{ formatAmount(invoice) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-secondary-600 dark:text-secondary-400">
                                                {{ formatDate(invoice.issued_date) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-secondary-600 dark:text-secondary-400">
                                                {{ formatDate(invoice.issued_due_date) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                :class="{
                                                    'px-2 py-1 rounded text-xs font-semibold capitalize': true,
                                                    'bg-yellow-100 text-yellow-800': invoice.status === 'pending',
                                                    'bg-green-100 text-green-800': invoice.status === 'approved',
                                                    'bg-gray-200 text-gray-700': invoice.status === 'cancelled',
                                                    'bg-red-100 text-red-700': invoice.status === 'rejected',
                                                    'bg-red-100 text-primary': invoice.status === 'paid',
                                                }">
                                                {{ invoice.status ?? 'N/A' }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <InvoiceActionModal
                :invoicedata="invoiceData"
                :show="showInvoiceModal"
                :can="can"
                :onClose="() => (showInvoiceModal = false)" />
        </div>
    </AuthenticatedLayout>
</template>
