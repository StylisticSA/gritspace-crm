<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import InvoiceActionModal from '@/Components/Modals/Invoice/InvoiceActionModal.vue';

const props = defineProps({
    invoices: Object,
    can: Object,
});

const showInvoiceModal = ref(false);
const selectedInvoiceId = ref(null);

const currencySymbols = {
    ZAR: 'R',
    USD: '$',
    EUR: '€',
    GBP: '£',
};

const invoiceData = ref(null);

const setOffice = id => {
    selectedInvoiceId.value = id;

    const officeList = props.invoices || [];
    invoiceData.value = officeList.find(o => o.id === id);

    showInvoiceModal.value = true;
};

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

    if (props.can['manage settings']) {
        router.visit(`/admin/invoices/${Id}`);
    } else {
        alert('me');
        router.visit(`/user/invoices/${Id}`);
    }
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
                    <div class="flex flex-col sm:flex-row gap-2 sm:space-x-2">
                        <Link
                            v-if="can['manage settings']"
                            :href="route('admin.invoices.create')"
                            class="px-3 py-2 text-center text-base sm:text-lg font-medium text-white rounded bg-primary hover:bg-bluemain/60 w-full sm:w-auto">
                            + Create Invoice
                        </Link>

                        <Link
                            v-if="can['add permissions']"
                            :href="route('admin.company.index')"
                            class="px-3 py-2 text-center text-base sm:text-lg font-medium text-white rounded bg-bluemain hover:bg-gray-700 w-full sm:w-auto">
                            Companies
                        </Link>

                        <Link
                            v-if="can['manage settings']"
                            :href="route('admin.banking.index')"
                            class="px-3 py-2 text-center text-base sm:text-lg font-medium text-white rounded bg-muted hover:bg-bluemain/60 w-full sm:w-auto">
                            Banking
                        </Link>
                    </div>

                    <!-- Second div wider on desktop, full width on mobile -->
                    <div class="flex justify-items-center flex-col sm:flex-row gap-4 w-full sm:w-2/4">
                        <div class="relative flex-1">
                            <input
                                placeholder="Search invoices..."
                                class="w-full pl-10 pr-4 py-2 border border-secondary-200 dark:border-secondary-700 rounded-lg bg-white dark:bg-secondary-900 text-secondary-900 text-bluemain focus:outline-none focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-400"
                                type="text"
                                value="" />
                        </div>

                        <div class="relative flex-1">
                            <button
                                class="px-4 py-2 border bg-bluemain hover:bg-bluemain/60 text-white rounded-lg w-full text-center"
                                type="button">
                                All Status
                            </button>
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
                                        <th
                                            class="px-6 py-4 font-semibold text-right text-xs text-secondary-600 dark:text-secondary-400 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                                    <tr
                                        v-for="invoice in invoices"
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
                                                    {{ invoice?.user?.name }}
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
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div
                                                class="relative inline-block"
                                                data-headlessui-state="">
                                                <button
                                                    @click="
                                                        setOffice(invoice.id);
                                                        showInvoiceModal = true;
                                                    "
                                                    class="p-2 hover:bg-secondary-100 dark:hover:bg-secondary-700 rounded-lg transition-colors"
                                                    type="button"
                                                    aria-expanded="false"
                                                    data-headlessui-state=""
                                                    id="headlessui-popover-button-_r_3o_">
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        width="24"
                                                        height="24"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        class="lucide lucide-ellipsis-vertical w-4 h-4 text-secondary-600 dark:text-secondary-400"
                                                        aria-hidden="true">
                                                        <circle
                                                            cx="12"
                                                            cy="12"
                                                            r="1"></circle>
                                                        <circle
                                                            cx="12"
                                                            cy="5"
                                                            r="1"></circle>
                                                        <circle
                                                            cx="12"
                                                            cy="19"
                                                            r="1"></circle>
                                                    </svg>
                                                </button>
                                            </div>
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
