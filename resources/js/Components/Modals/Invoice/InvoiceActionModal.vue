<script setup>
import { router } from '@inertiajs/vue3';
import useStatusMessage from '../../../Composables/useStatusMessage';

const props = defineProps({
    invoicedata: {
        type: Object,
        default: () => null,
    },

    show: Boolean,
    onClose: Function,
    can: Object,
});

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();

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
        router.visit(`/user/invoices/${Id}`);
    }
}

function editInvoice(Id) {
    router.visit(`/admin/invoices/${Id}/edit`);
}

const paidInvoice = id => {
    if (!id) return;

    router.put(route('admin.invoice.paid', id), {
        preserveScroll: true,
        onSuccess: () => {
            message.value = 'Invoice marked Paid Successfully';
            status.value = 'success';
        },
    });

    setTimeout(() => {
        router.visit(route('admin.invoices.index'));
    }, 2000);
};

const cancelledInvoice = id => {
    if (!id) return;

    router.put(route('admin.invoice.cancelled', id), {
        preserveScroll: true,
        onSuccess: () => {
            message.value = 'Invoice marked Cancelled Successfully';
            status.value = 'success';
        },
    });

    setTimeout(() => {
        router.visit(route('admin.invoices.index'));
    }, 2000);
};

const pendingInvoice = id => {
    if (!id) return;

    router.put(route('admin.invoice.pending', id), {
        preserveScroll: true,
        onSuccess: () => {
            message.value = 'Invoice marked Pending Successfully';
            status.value = 'success';
        },
    });

    setTimeout(() => {
        router.visit(route('admin.invoices.index'));
    }, 2000);
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 overflow-y-auto bg-black/60">
        <div class="w-full max-w-2xl px-5 bg-white rounded shadow-lg sm:p-4">
            <div class="flex items-center justify-between m-5">
                <h2 class="text-2xl sm:text-2xl">Invoice</h2>

                <button
                    @click="props.onClose"
                    class="text-sm text-black">
                    Close
                </button>
            </div>
            <template v-if="showMessage">
                <div :class="messageClass">
                    {{ messageText }}
                </div>
            </template>
            <hr class="my-2 border-gray-300" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-10">
                <!-- Column 1: Details grid -->
                <div class="grid grid-cols-[120px_1fr] gap-x-2 items-start md:border-r-2">
                    <div class="mb-3 font-medium text-gray-600"><strong>User:</strong></div>
                    <div>{{ invoicedata.user_name }}</div>

                    <div class="mb-8 font-medium text-gray-600"><strong>Email:</strong></div>
                    <div>{{ invoicedata.customer_email }}</div>

                    <div class="mb-3 font-medium text-gray-600"><strong>Bank Details:</strong></div>
                    <div>{{ invoicedata.banking.bank_name }}</div>
                    <div class="mb-8 font-medium text-gray-600"><strong>Total:</strong></div>
                    <div>R{{ invoicedata.total_amount }}</div>

                    <div class="mb-3 font-medium text-gray-600"><strong>Issued Date:</strong></div>
                    <div>{{ formatDate(invoicedata.issued_date) }}</div>

                    <div class="mb-8 font-medium text-gray-600"><strong>Due Date:</strong></div>
                    <div>{{ formatDate(invoicedata.issued_due_date) }}</div>

                    <div class="mb-3 font-medium text-gray-600"><strong>Status:</strong></div>
                    <div>
                        <span
                            :class="{
                                'px-2 py-1 rounded text-xs font-semibold capitalize': true,
                                'bg-yellow-100 text-yellow-800': invoicedata.status === 'pending',
                                'bg-green-100 text-green-800': invoicedata.status === 'approved',
                                'bg-gray-200 text-gray-700': invoicedata.status === 'cancelled',
                                'bg-red-100 text-red-700': invoicedata.status === 'rejected',
                                'bg-red-100 text-primary': invoicedata.status === 'paid',
                            }">
                            {{ invoicedata.status ?? 'N/A' }}
                        </span>
                    </div>
                </div>

                <!-- Column 2: Buttons -->
                <div class="flex flex-col justify-center items-center gap-2 pt-10 md:p-0">
                    <button
                        @click="viewInvoice(invoicedata.id)"
                        class="block px-4 py-2 text-sm font-medium text-white rounded bg-primary hover:bg-bluemain/60 w-full">
                        View Invoice
                    </button>

                    <button
                        v-if="can['manage settings']"
                        @click="editInvoice(invoicedata.id)"
                        class="block px-4 py-2 text-sm font-medium text-white rounded bg-bluemain hover:bg-bluemain/60 w-full">
                        Edit Invoice
                    </button>
                    <button
                        v-if="can['manage settings']"
                        @click="paidInvoice(invoicedata.id)"
                        class="block px-4 py-2 text-sm font-medium text-white rounded bg-green-600 hover:bg-bluemain/60 w-full"
                        :disabled="invoicedata.status === 'paid'">
                        Paid
                    </button>

                    <button
                        v-if="can['manage settings']"
                        @click="pendingInvoice(invoicedata.id)"
                        class="block px-4 py-2 text-sm font-medium text-white rounded bg-yellow-600 hover:bg-bluemain/60 w-full"
                        :disabled="invoicedata.status === 'pending'">
                        Pending
                    </button>

                    <button
                        v-if="can['manage settings']"
                        @click="cancelledInvoice(invoicedata.id)"
                        class="block px-4 py-2 text-sm font-medium text-white rounded bg-gray-700 hover:bg-bluemain/60 w-full"
                        :disabled="invoicedata.status === 'cancelled'">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
