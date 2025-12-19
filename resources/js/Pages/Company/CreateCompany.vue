<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import useStatusMessage from '../../Composables/useStatusMessage';

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();

const form = useForm({
    company_name: '',
    vat_no: '',
    reg_no: '',
    address: '',
    email: '',
    phone: '',
});

const submit = () => {
    form.post(route('admin.company.store'), {
        onSuccess: () => {
            message.value = 'Company has been Saved Successfully.';
            status.value = 'success';

            setTimeout(() => {
                router.reload({ preserveScroll: true });
                router.visit(route('admin.company.index'));
            }, 2000);
        },
    });
};

const validatePhone = () => {
    const regex = /^(?:\+27\d{9}|0\d{9})$/;

    if (!regex.test(this.form.phone)) {
        this.form.errors.phone = 'Phone number must be valid Phone Number: +27XXXXXXXXX or 0XXXXXXXXX';
    } else {
        this.form.errors.phone = null;
    }
};
</script>

<template>
    <Head title="Create Company" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Companies</h2>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-4xl p-6 mx-auto space-y-6">
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-2xl font-medium text-black">Add Company</h3>

                        <Link
                            :href="route('admin.company.index')"
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
                            <!-- Company -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Company Name</label>
                                <input
                                    v-model="form.company_name"
                                    type="text"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.company_name"
                                    class="text-sm text-red-600">
                                    {{ form.errors.company_name }}
                                </div>
                            </div>

                            <!-- Reg No -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Registration Number</label>
                                <input
                                    v-model="form.reg_no"
                                    type="text"
                                    class="w-full px-3 py-2 border rounded"
                                    placeholder="e.g. 123456" />
                                <div
                                    v-if="form.errors.reg_no"
                                    class="text-sm text-red-600">
                                    {{ form.errors.reg_no }}
                                </div>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Email</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="w-full px-3 py-2 border rounded"
                                    placeholder="e.g. name@gmail.com" />
                                <div
                                    v-if="form.errors.email"
                                    class="text-sm text-red-600">
                                    {{ form.errors.email }}
                                </div>
                            </div>

                            <!-- Phone -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Phone Number</label>
                                <input
                                    v-model="form.phone"
                                    type="text"
                                    class="w-full px-3 py-2 border rounded"
                                    placeholder="e.g. +27821234567 or 0821234567"
                                    @blur="validatePhone" />
                                <div
                                    v-if="form.errors.phone"
                                    class="text-sm text-red-600">
                                    {{ form.errors.phone }}
                                </div>
                            </div>

                            <!-- Vat Number -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Vat Number</label>
                                <input
                                    v-model="form.vat_no"
                                    type="text"
                                    class="w-full px-3 py-2 border rounded"
                                    placeholder="e.g year/...." />
                                <div
                                    v-if="form.errors.vat_no"
                                    class="text-sm text-red-600">
                                    {{ form.errors.vat_no }}
                                </div>
                            </div>
                            <!-- Address -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Address</label>
                                <input
                                    v-model="form.address"
                                    type="text"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.address"
                                    class="text-sm text-red-600">
                                    {{ form.errors.address }}
                                </div>
                            </div>
                        </div>

                        <div class="w-full pt-4 md:col-span-2">
                            <button
                                type="submit"
                                class="block w-full px-3 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60"
                                :disabled="form.processing">
                                Add Company
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
