<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    invoice: {
        type: Object,
        default: () => null,
    },
    can: Object,
});

// console.log('i', props.invoice);
const formatDate = date => {
    if (!date) return '—';
    const d = new Date(date);
    return d.toLocaleDateString('en-ZA', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
};
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
                    <div class="mb-3">
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
                    </div>
                    <div class="flex gap-1">
                        <button
                            class="bg-primary inline-flex items-center justify-center rounded-lg font-medium text-white hover:bg-primary-50 px-4 py-2 text-base">
                            Print
                        </button>
                        <button
                            class="bg-bluemain inline-flex items-center justify-center rounded-lg font-medium text-white hover:bg-primary-50 px-4 py-2 text-base">
                            Download
                        </button>
                        <button
                            class="bg-green-800 inline-flex items-center justify-center rounded-lg font-medium text-white hover:bg-primary-700 px-4 py-2 text-base">
                            Send Invoice
                        </button>
                        <button
                            class="bg-yellow-800 inline-flex items-center justify-center rounded-lg font-medium text-white hover:bg-primary-700 px-4 py-2 text-base">
                            Edit Invoice
                        </button>
                    </div>
                </div>
                <div class="card p-4 transition-all duration-200 bg-white rounded-lg">
                    <div class="p-2">
                        <div class="flex justify-between items-start mb-12">
                            <div>
                                <div class="mb-5">
                                    <Link
                                        :href="route('dashboard')"
                                        class="flex items-center">
                                        <ApplicationLogo class="block w-auto h-12 text-gray-800 fill-current" />
                                    </Link>
                                </div>
                                <!-- <h2 class="text-2xl font-bold text-secondary-900 text-bluemain mb-1">TailPanel Inc.</h2> -->
                                <div class="text-sm text-secondary-600 dark:text-secondary-400">
                                    <p>123 Business Street</p>
                                    <p>San Francisco, CA 94102</p>
                                    <p class="mt-2">billing@tailpanel.com</p>
                                    <p>+1 (555) 123-4567</p>
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
                                                'px-2 py-1 rounded text-xs font-semibold capitalize': true,
                                                'bg-yellow-100 text-yellow-800': invoice.status === 'pending',
                                                'bg-green-100 text-green-800': invoice.status === 'approved',
                                                'bg-gray-200 text-gray-700': invoice.status === 'cancelled',
                                                'bg-red-100 text-red-700': invoice.status === 'rejected',
                                                'bg-red-100 text-primary': invoice.status === 'paid',
                                            }">
                                            {{ invoice.status ?? 'N/A' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mb-8">
                            <h4 class="text-sm font-semibold text-secondary-900 text-bluemain uppercase mb-2">
                                Bill To
                            </h4>
                            <div class="text-sm text-secondary-600 dark:text-secondary-400">
                                <p class="font-semibold text-bluemain">{{ invoice.customer_name }}</p>
                                <p>{{ invoice.user_name }}</p>
                                <p>{{ invoice.customer_address }}</p>
                                <p>{{ invoice.customer_city }}</p>
                                <p class="mt-2">{{ invoice.customer_email }}</p>
                                <p>{{ invoice.customer_phone }}</p>
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
        </div>
    </AuthenticatedLayout>
</template>
