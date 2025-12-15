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
                <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                    <Link
                        :href="route('admin.help-desk.create')"
                        class="inline-block px-3 py-2 text-lg font-medium text-white rounded bg-primary hover:bg-bluemain/60">
                        + Create Invoice
                    </Link>
                    <div></div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="card p-6 transition-all duration-200 bg-white rounded-lg">
                        <div class="pt-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-secondary-600 dark:text-secondary-400 mb-1">
                                        Total Invoices
                                    </p>
                                    <p class="text-2xl font-bold text-secondary-900 text-bluemain">6</p>
                                </div>
                                <div
                                    class="w-12 h-12 rounded-lg bg-primary-100 dark:bg-primary-900/20 flex items-center justify-center">
                                    <svg
                                        class="w-6 h-6 text-primary-600 dark:text-primary-400"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card p-6 transition-all duration-200 bg-white rounded-lg">
                        <div class="pt-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-secondary-600 dark:text-secondary-400 mb-1">Paid</p>
                                    <p class="text-2xl font-bold text-success-600 dark:text-success-400">3</p>
                                </div>
                                <div
                                    class="w-12 h-12 rounded-lg bg-success-100 dark:bg-success-900/20 flex items-center justify-center">
                                    <svg
                                        class="w-6 h-6 text-success-600 dark:text-success-400"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card p-6 transition-all duration-200 bg-white rounded-lg">
                        <div class="pt-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-secondary-600 dark:text-secondary-400 mb-1">Pending</p>
                                    <p class="text-2xl font-bold text-warning-600 dark:text-warning-400">2</p>
                                </div>
                                <div
                                    class="w-12 h-12 rounded-lg bg-warning-100 dark:bg-warning-900/20 flex items-center justify-center">
                                    <svg
                                        class="w-6 h-6 text-warning-600 dark:text-warning-400"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card p-6 transition-all duration-200 bg-white rounded-lg">
                        <div class="pt-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-secondary-600 dark:text-secondary-400 mb-1">Overdue</p>
                                    <p class="text-2xl font-bold text-danger-600 dark:text-danger-400">1</p>
                                </div>
                                <div
                                    class="w-12 h-12 rounded-lg bg-danger-100 dark:bg-danger-900/20 flex items-center justify-center">
                                    <svg
                                        class="w-6 h-6 text-danger-600 dark:text-danger-400"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card p-6 transition-all duration-200 bg-white rounded-lg mb-6">
                    <div class="pt-6">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <div class="relative flex-1">
                                <input
                                    placeholder="Search invoices..."
                                    class="w-full pl-10 pr-4 py-2 border border-secondary-200 dark:border-secondary-700 rounded-lg bg-white dark:bg-secondary-900 text-secondary-900 text-bluemain focus:outline-none focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-400"
                                    type="text"
                                    value="" />
                            </div>
                            <div
                                class="relative"
                                data-headlessui-state="">
                                <button
                                    class="flex items-center gap-2 px-4 py-2 border border-secondary-200 dark:border-secondary-700 rounded-lg text-sm text-secondary-700 dark:text-secondary-300 hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-colors"
                                    type="button"
                                    aria-expanded="false"
                                    data-headlessui-state=""
                                    id="headlessui-popover-button-_r_3i_">
                                    All Status
                                </button>
                            </div>
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
                                    <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a
                                                class="text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300"
                                                href="/TailPanel/ecommerce/invoice/INV-002"
                                                data-discover="true"
                                                >INV-002</a
                                            >
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm">
                                                <div class="font-medium text-secondary-900 text-bluemain">
                                                    TechStart Inc
                                                </div>
                                                <div class="text-secondary-500 dark:text-secondary-400">
                                                    billing@techstart.com
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-secondary-900 text-bluemain">
                                                $1299.50
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-secondary-600 dark:text-secondary-400">
                                                Dec 10, 2025
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-secondary-600 dark:text-secondary-400">
                                                Dec 25, 2025
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="badge badge-warning px-2 py-0.5 text-xs">Pending</span>
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
                                                    id="headlessui-popover-button-_r_3u_">
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
                                    <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a
                                                class="text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300"
                                                href="/TailPanel/ecommerce/invoice/INV-003"
                                                data-discover="true"
                                                >INV-003</a
                                            >
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm">
                                                <div class="font-medium text-secondary-900 text-bluemain">
                                                    Global Dynamics
                                                </div>
                                                <div class="text-secondary-500 dark:text-secondary-400">
                                                    info@globaldynamics.com
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-secondary-900 text-bluemain">
                                                $5499.00
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-secondary-600 dark:text-secondary-400">
                                                Dec 7, 2025
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-secondary-600 dark:text-secondary-400">
                                                Dec 7, 2025
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
                                                    id="headlessui-popover-button-_r_44_">
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
                                    <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a
                                                class="text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300"
                                                href="/TailPanel/ecommerce/invoice/INV-004"
                                                data-discover="true"
                                                >INV-004</a
                                            >
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm">
                                                <div class="font-medium text-secondary-900 text-bluemain">
                                                    NextGen Solutions
                                                </div>
                                                <div class="text-secondary-500 dark:text-secondary-400">
                                                    accounts@nextgen.com
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-secondary-900 text-bluemain">
                                                $899.99
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-secondary-600 dark:text-secondary-400">
                                                Oct 31, 2025
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-secondary-600 dark:text-secondary-400">
                                                Nov 30, 2025
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="badge badge-danger px-2 py-0.5 text-xs">Overdue</span>
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
                                                    id="headlessui-popover-button-_r_4a_">
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
                                    <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a
                                                class="text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300"
                                                href="/TailPanel/ecommerce/invoice/INV-005"
                                                data-discover="true"
                                                >INV-005</a
                                            >
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm">
                                                <div class="font-medium text-secondary-900 text-bluemain">
                                                    Innovation Labs
                                                </div>
                                                <div class="text-secondary-500 dark:text-secondary-400">
                                                    finance@innovationlabs.com
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-secondary-900 text-bluemain">
                                                $3299.00
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-secondary-600 dark:text-secondary-400">
                                                Dec 12, 2025
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-secondary-600 dark:text-secondary-400">
                                                Dec 27, 2025
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="badge badge-warning px-2 py-0.5 text-xs">Pending</span>
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
                                                    id="headlessui-popover-button-_r_4g_">
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
                                    <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a
                                                class="text-sm font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300"
                                                href="/TailPanel/ecommerce/invoice/INV-006"
                                                data-discover="true"
                                                >INV-006</a
                                            >
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm">
                                                <div class="font-medium text-secondary-900 text-bluemain">
                                                    Digital Ventures
                                                </div>
                                                <div class="text-secondary-500 dark:text-secondary-400">
                                                    billing@digitalventures.com
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-secondary-900 text-bluemain">
                                                $1899.50
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-secondary-600 dark:text-secondary-400">
                                                Dec 3, 2025
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-secondary-600 dark:text-secondary-400">
                                                Dec 3, 2025
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
                                                    id="headlessui-popover-button-_r_4m_">
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
