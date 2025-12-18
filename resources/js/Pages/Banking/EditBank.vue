<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import useStatusMessage from '../../Composables/useStatusMessage';

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();

const props = defineProps({
    banking: Object,
    can: Object,
});

const form = useForm({
    bank_name: props.banking.bank_name,
    account_holder: props.banking.account_holder,
    account_number: props.banking.account_number,
    branch_code: props.banking.branch_code,
    swift_code: props.banking.swift_code,
    iban: props.banking.iban,
});

const submit = () => {
    form.put(route('admin.banking.update', props.banking.id), {
        onSuccess: () => {
            message.value = 'Banking Details have been Updated Successfully.';
            status.value = 'success';

            setTimeout(() => {
                router.reload({ preserveScroll: true });
                router.visit(route('admin.banking.index'));
            }, 2000);
        },
    });
};

function formatCardNumber(e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.match(/.{1,4}/g)?.join(' ') || '';
    form.account_number = value;
}
</script>

<template>
    <Head title="Edit Banking Details" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Banking</h2>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-4xl p-6 mx-auto space-y-6">
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-2xl font-medium text-black">Edit banking Details</h3>

                        <Link
                            :href="route('admin.banking.index')"
                            class="inline-block px-3 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
                            Back
                        </Link>
                    </div>

                    <template v-if="showMessage">
                        <div :class="messageClass">
                            {{ messageText }}
                        </div>
                    </template>
                    <form
                        @submit.prevent="submit"
                        class="space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <!-- Bank Name -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Bank Name</label>
                                <select
                                    v-model="form.bank_name"
                                    class="w-full px-3 py-2 border rounded">
                                    <option value="">Select a bank</option>
                                    <option value="Standard Bank">Standard Bank</option>
                                    <option value="First National Bank (FNB)">First National Bank (FNB)</option>
                                    <option value="Absa">Absa</option>
                                    <option value="Nedbank">Nedbank</option>
                                    <option value="Capitec Bank">Capitec Bank</option>
                                    <option value="Investec">Investec</option>
                                    <option value="African Bank">African Bank</option>
                                    <option value="TymeBank">TymeBank</option>
                                    <option value="Discovery Bank">Discovery Bank</option>
                                </select>
                                <div
                                    v-if="form.errors.bank_name"
                                    class="text-sm text-red-600">
                                    {{ form.errors.bank_name }}
                                </div>
                            </div>

                            <!-- Account Holder -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Account Holder</label>
                                <input
                                    v-model="form.account_holder"
                                    type="text"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.account_holder"
                                    class="text-sm text-red-600">
                                    {{ form.errors.account_holder }}
                                </div>
                            </div>

                            <!-- Account Number -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Card Number</label>
                                <input
                                    v-model="form.account_number"
                                    type="text"
                                    class="w-full px-3 py-2 border rounded"
                                    placeholder="1234 1234 1234 1234"
                                    @input="formatCardNumber" />
                                <div
                                    v-if="form.errors.account_number"
                                    class="text-sm text-red-600">
                                    {{ form.errors.account_number }}
                                </div>
                            </div>

                            <!-- Branch Code -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Branch Code</label>
                                <input
                                    v-model="form.branch_code"
                                    type="text"
                                    class="w-full px-3 py-2 border rounded"
                                    placeholder="e.g. 123456" />
                                <div
                                    v-if="form.errors.branch_code"
                                    class="text-sm text-red-600">
                                    {{ form.errors.branch_code }}
                                </div>
                            </div>

                            <!-- Swift Code -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Swift Code</label>
                                <input
                                    v-model="form.swift_code"
                                    type="text"
                                    class="w-full px-3 py-2 border rounded"
                                    placeholder="e.g. SBZAZAJJ" />
                                <div
                                    v-if="form.errors.swift_code"
                                    class="text-sm text-red-600">
                                    {{ form.errors.swift_code }}
                                </div>
                            </div>

                            <!-- IBAN -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">IBAN Number</label>
                                <input
                                    v-model="form.iban"
                                    type="text"
                                    class="w-full px-3 py-2 border rounded"
                                    placeholder="e.g. GB82WEST12345698765432" />
                                <div
                                    v-if="form.errors.iban"
                                    class="text-sm text-red-600">
                                    {{ form.errors.iban }}
                                </div>
                            </div>
                        </div>

                        <div class="w-full pt-4 md:col-span-2">
                            <button
                                type="submit"
                                class="block w-full px-3 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60"
                                :disabled="form.processing">
                                Update Banking Details
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
