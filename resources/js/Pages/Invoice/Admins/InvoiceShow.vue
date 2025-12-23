<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import InvoiceSendModal from '../../../Components/Modals/Invoice/InvoiceSendModal.vue';
import html2pdf from 'html2pdf.js';

const props = defineProps({
    invoice: {
        type: Object,
        default: () => null,
    },
    can: Object,
});

const showInvoiceUserModal = ref(false);
console.log('i', props.invoice);

const exportToPDF = async () => {
    const element = document.getElementById('invoice-template');

    const imgs = element.querySelectorAll('img');
    await Promise.all(
        Array.from(imgs).map(img => {
            if (img.complete) return Promise.resolve();
            return new Promise(res => {
                img.addEventListener('load', res, { once: true });
                img.addEventListener('error', res, { once: true });
            });
        })
    );

    html2pdf()
        .set({
            margin: 1,
            filename: `${props.invoice?.user_name}-invoice-${props.invoice?.invoice_number}.pdf`,
            image: { type: 'svg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
        })
        .from(element)
        .save();
};

const formatDate = date => {
    if (!date) return '—';
    const d = new Date(date);
    return d.toLocaleDateString('en-ZA', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
};

function editInvoice() {
    router.visit(`/admin/invoices/${props.invoice.id}/edit`);
}
</script>
<template>
    <Head title="View Invoice" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-semibold leading-tight text-gray-800">Invoices</h2>
        </template>
        <div class="py-2 px-5 lg:px-0">
            <div class="mx-auto max-w-7xl sm:px-4 lg:px-8 mb-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6 mt-10">
                    <div class="mb-3 flex space-x-2">
                        <Link
                            v-if="can['manage settings']"
                            :href="route('admin.invoices.index')"
                            class="block w-full sm:w-auto text-center px-3 py-2 text-base font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
                            Back
                        </Link>
                        <Link
                            v-if="!can['manage settings']"
                            :href="route('user.invoice')"
                            class="block w-full sm:w-auto text-center px-3 py-2 text-base font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
                            Back
                        </Link>
                        <Link
                            v-if="can['manage settings']"
                            @click="editInvoice()"
                            class="block w-full sm:w-auto text-center px-3 py-2 text-base font-medium text-white rounded bg-primary hover:bg-bluemain/60">
                            Edit Invoice
                        </Link>
                    </div>
                    <div class="flex gap-1">
                        <button
                            @click="exportToPDF()"
                            class="bg-bluemain inline-flex items-center justify-center rounded-lg font-medium text-white hover:bg-primary-50 px-4 py-2 text-base">
                            Download
                        </button>
                        <button
                            v-if="can['manage settings']"
                            @click="showInvoiceUserModal = true"
                            class="bg-green-800 inline-flex items-center justify-center rounded-lg font-medium text-white hover:bg-primary-700 px-4 py-2 text-base">
                            Send Invoice
                        </button>
                    </div>
                </div>
                <div
                    id="invoice-template"
                    class="card p-4 transition-all duration-200 bg-white rounded-lg"
                    ref="invoiceRef">
                    <div class="p-2">
                        <div class="flex justify-between items-start mb-12">
                            <div>
                                <div class="my-5">
                                    <img
                                        src="/files_grits/gritspace_logo.png"
                                        alt="Logo"
                                        class="w-auto h-10" />
                                </div>

                                <div class="text-sm text-secondary-600 dark:text-secondary-400">
                                    <p>{{ invoice.banking?.company?.company_name }}</p>
                                    <p>{{ invoice.banking?.company?.address }}</p>
                                    <p class="mt-2">{{ invoice.banking?.company?.email }}</p>
                                    <p>+{{ invoice.banking?.company?.phone }}</p>
                                    <p class="mt-2">
                                        <span class="text-medium pr-1">Reg #:</span
                                        >{{ invoice.banking?.company?.reg_no }}
                                    </p>
                                    <p>
                                        <span class="text-medium pr-1">Vat #:</span
                                        >{{ invoice.banking?.company?.vat_no }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right mt-3">
                                <h3 class="text-3xl font-bold text-secondary-900 text-bluemain mb-4">INVOICE</h3>
                                <div class="text-sm">
                                    <p class="text-secondary-600 dark:text-secondary-400">
                                        <strong>Invoice Number: </strong>
                                        <span class="pl-2font-medium text-secondary-900 text-bluemain">{{
                                            invoice.invoice_number
                                        }}</span>
                                    </p>
                                    <p class="text-secondary-600 dark:text-secondary-400 mt-1">
                                        <strong>Issue Date: </strong>
                                        <span class="pl-2font-medium text-secondary-900 text-bluemain">{{
                                            formatDate(invoice.issued_date)
                                        }}</span>
                                    </p>
                                    <p class="text-secondary-600 dark:text-secondary-400 mt-1">
                                        <strong>Due Date: </strong>
                                        <span class="pl-2font-medium text-secondary-900 text-bluemain">{{
                                            formatDate(invoice.issued_due_date)
                                        }}</span>
                                    </p>
                                    <p class="text-secondary-600 dark:text-secondary-400 mt-3">
                                        <strong>Status: </strong>
                                        <span
                                            :class="{
                                                'px-2  rounded text-sm font-semibold capitalize': true,
                                                ' text-yellow-800': invoice.status === 'pending',
                                                ' text-green-800': invoice.status === 'approved',
                                                ' text-gray-700': invoice.status === 'cancelled',
                                                ' text-red-700': invoice.status === 'rejected',
                                                ' text-primary': invoice.status === 'paid',
                                            }">
                                            {{ invoice.status ?? 'N/A' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-between items-start mb-12">
                            <div class="">
                                <h4 class="text-sm font-semibold text-secondary-900 text-bluemain uppercase mb-2">
                                    Bill To
                                </h4>
                                <div class="text-sm text-secondary-600 dark:text-secondary-400">
                                    <p class="font-semibold text-bluemain">{{ invoice.customer_name }}</p>

                                    <p>{{ invoice.user?.name }}</p>
                                    <p>{{ invoice.customer_address }}</p>
                                    <p>{{ invoice.customer_city }}</p>
                                    <p class="mt-2">{{ invoice.customer_email }}</p>
                                    <p>{{ invoice.customer_phone }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <h4 class="text-sm font-semibold text-secondary-900 text-bluemain uppercase mb-2">
                                    Banking Details
                                </h4>
                                <div class="text-sm">
                                    <p class="text-secondary-600 dark:text-secondary-400">
                                        <span class="pl-2font-medium text-secondary-900 text-bluemain">
                                            {{ invoice.banking?.bank_name }}</span
                                        >
                                    </p>
                                    <p class="text-secondary-600 dark:text-secondary-400 mt-1">
                                        <span class="pl-2font-medium text-secondary-900 text-bluemain">
                                            {{ invoice.banking?.account_holder }}
                                        </span>
                                    </p>
                                    <p class="text-secondary-600 dark:text-secondary-400 mt-1">
                                        <span class="pl-2font-medium text-secondary-900 text-bluemain">
                                            {{ invoice.banking?.account_number }}
                                        </span>
                                    </p>
                                    <p class="text-secondary-600 dark:text-secondary-400 mt-1">
                                        <span class="pl-2font-medium text-secondary-900 text-bluemain">
                                            {{ invoice.banking?.branch_code }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mb-8">
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b-2 border-secondary-200 dark:border-secondary-700">
                                            <th
                                                class="pb-3 text-left text-sm font-semibold text-secondary-900 text-bluemain uppercase">
                                                Description
                                            </th>
                                            <th
                                                class="pb-3 text-center text-sm font-semibold text-secondary-900 text-bluemain uppercase">
                                                Qty
                                            </th>
                                            <th
                                                class="pb-3 text-right text-sm font-semibold text-secondary-900 text-bluemain uppercase">
                                                Rate
                                            </th>
                                            <th
                                                class="pb-3 text-right text-sm font-semibold text-secondary-900 text-bluemain uppercase">
                                                Amount
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                                        <tr v-for="invoice in invoice?.invoice_items">
                                            <td class="py-4 text-sm text-secondary-900 text-bluemain">
                                                {{ invoice.item_name }}
                                            </td>
                                            <td
                                                class="py-4 text-center text-sm text-secondary-600 dark:text-secondary-400">
                                                {{ invoice.item_quantity }}
                                            </td>
                                            <td
                                                class="py-4 text-right text-sm text-secondary-600 dark:text-secondary-400">
                                                {{ invoice.item_rate }}
                                            </td>
                                            <td
                                                class="py-4 text-right text-sm font-medium text-secondary-900 text-bluemain">
                                                {{ invoice.item_amount }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="flex justify-end mb-8">
                            <div class="w-full sm:w-80">
                                <div class="flex justify-between py-2 text-sm">
                                    <span class="text-secondary-600 dark:text-secondary-400">Subtotal:</span
                                    ><span class="font-medium text-secondary-900 text-bluemain">{{
                                        invoice.subtotal
                                    }}</span>
                                </div>
                                <div class="flex justify-between py-2 text-sm">
                                    <span class="text-secondary-600 dark:text-secondary-400"
                                        >Tax ({{ invoice.tax_rate }}%):</span
                                    ><span class="font-medium text-secondary-900 text-bluemain">{{
                                        invoice.tax_amount
                                    }}</span>
                                </div>
                                <div
                                    class="flex justify-between py-3 border-t-2 border-secondary-200 dark:border-secondary-700">
                                    <span class="text-lg font-bold text-secondary-900 text-bluemain">Total:</span
                                    ><span class="text-lg font-bold text-primary-600 dark:text-primary-400">{{
                                        invoice.total_amount
                                    }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="pt-8 border-t border-secondary-200 dark:border-secondary-700">
                            <h4 class="text-sm font-semibold text-secondary-900 text-bluemain uppercase mb-2">Notes</h4>
                            <p class="text-sm text-secondary-600 dark:text-secondary-400">
                                Thank you for your business. Payment is due within 15 days. Please include invoice
                                number with payment.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <InvoiceSendModal
                :invoice="props.invoice"
                :show="showInvoiceUserModal"
                :can="can"
                :onClose="() => (showInvoiceUserModal = false)" />
        </div>
    </AuthenticatedLayout>
</template>
