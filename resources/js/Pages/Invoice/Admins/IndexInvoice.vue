<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { format } from 'date-fns';
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
                    <div class="space-x-2">
                        <Link
                            :href="route('admin.invoices.create')"
                            class="inline-block px-3 py-2 text-lg font-medium text-white rounded bg-primary hover:bg-bluemain/60">
                            + Create Invoice
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
                                            class="px-6 py-4 text-left text-xs font-medium text-secondary-600 dark:text-secondary-400 uppercase tracking-wider">
                                            Invoice
                                        </th>
                                        <th
                                            class="px-6 py-4 text-left text-xs font-medium text-secondary-600 dark:text-secondary-400 uppercase tracking-wider">
                                            Customer
                                        </th>
                                        <th
                                            class="px-6 py-4 text-left text-xs font-medium text-secondary-600 dark:text-secondary-400 uppercase tracking-wider">
                                            Amount
                                        </th>
                                        <th
                                            class="px-6 py-4 text-left text-xs font-medium text-secondary-600 dark:text-secondary-400 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th
                                            class="px-6 py-4 text-left text-xs font-medium text-secondary-600 dark:text-secondary-400 uppercase tracking-wider">
                                            Due Date
                                        </th>
                                        <th
                                            class="px-6 py-4 text-left text-xs font-medium text-secondary-600 dark:text-secondary-400 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th
                                            class="px-6 py-4 text-right text-xs font-medium text-secondary-600 dark:text-secondary-400 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                                    <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a
                                                class="text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300"
                                                href="/TailPanel/ecommerce/invoice/INV-001"
                                                data-discover="true"
                                                >INV-001</a
                                            >
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm">
                                                <div class="font-medium text-secondary-900 text-bluemain">
                                                    Acme Corporation
                                                </div>
                                                <div class="text-secondary-500 dark:text-secondary-400">
                                                    contact@acme.com
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-secondary-900 text-bluemain">
                                                $2499.99
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-secondary-600 dark:text-secondary-400">
                                                Dec 13, 2025
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-secondary-600 dark:text-secondary-400">
                                                Dec 13, 2025
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="badge badge-success px-2 py-0.5 text-xs">Paid</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div
                                                class="relative inline-block"
                                                data-headlessui-state="">
                                                <button
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
        </div>
    </AuthenticatedLayout>
</template>
