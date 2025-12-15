<template>
    <div class="p-4 lg:p-6">
        <div>
            <nav
                class="flex items-center space-x-2 text-sm mb-6"
                aria-label="Breadcrumb">
                <a
                    aria-label="Home"
                    class="flex items-center text-secondary-600 dark:text-secondary-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                    href="/TailPanel/"
                    data-discover="true"></a>
                <div class="flex items-center gap-2">
                    <a
                        class="text-secondary-600 dark:text-secondary-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                        href="/TailPanel/ecommerce"
                        data-discover="true"
                        >E-Commerce</a
                    >
                </div>
                <div class="flex items-center gap-2">
                    <a
                        class="text-secondary-600 dark:text-secondary-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                        href="/TailPanel/ecommerce/invoices"
                        data-discover="true"
                        >Invoices</a
                    >
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-secondary-900 dark:text-white font-medium">INV-001</span>
                </div>
            </nav>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div class="flex items-center gap-4">
                    <button
                        class="p-2 hover:bg-secondary-100 dark:hover:bg-secondary-800 rounded-lg transition-colors"></button>
                    <div>
                        <h1 class="text-3xl font-bold text-secondary-900 dark:text-white mb-2">INV-001</h1>
                        <div class="flex items-center gap-3">
                            <span class="badge badge-warning px-2.5 py-0.5 text-sm">Pending</span
                            ><span class="text-sm text-secondary-600 dark:text-secondary-400"
                                >Issued: December 15, 2025</span
                            >
                        </div>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button
                        class="inline-flex items-center justify-center rounded-lg font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed bg-transparent border-2 border-primary-600 text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 focus:ring-primary-500 px-4 py-2 text-base">
                        Print</button
                    ><button
                        class="inline-flex items-center justify-center rounded-lg font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed bg-transparent border-2 border-primary-600 text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 focus:ring-primary-500 px-4 py-2 text-base">
                        Download</button
                    ><button
                        class="inline-flex items-center justify-center rounded-lg font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500 px-4 py-2 text-base">
                        Send Invoice
                    </button>
                </div>
            </div>
            <div class="card p-6 transition-all duration-200">
                <div class="p-8">
                    <div class="flex justify-between items-start mb-12">
                        <div>
                            <div
                                class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-2xl mb-4">
                                T
                            </div>
                            <h2 class="text-2xl font-bold text-secondary-900 dark:text-white mb-1">TailPanel Inc.</h2>
                            <div class="text-sm text-secondary-600 dark:text-secondary-400">
                                <p>123 Business Street</p>
                                <p>San Francisco, CA 94102</p>
                                <p class="mt-2">billing@tailpanel.com</p>
                                <p>+1 (555) 123-4567</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <h3 class="text-3xl font-bold text-secondary-900 dark:text-white mb-2">INVOICE</h3>
                            <div class="text-sm">
                                <p class="text-secondary-600 dark:text-secondary-400">
                                    Invoice Number:
                                    <span class="font-medium text-secondary-900 dark:text-white">INV-001</span>
                                </p>
                                <p class="text-secondary-600 dark:text-secondary-400 mt-1">
                                    Issue Date:
                                    <span class="font-medium text-secondary-900 dark:text-white"
                                        >December 15, 2025</span
                                    >
                                </p>
                                <p class="text-secondary-600 dark:text-secondary-400 mt-1">
                                    Due Date:
                                    <span class="font-medium text-secondary-900 dark:text-white"
                                        >December 30, 2025</span
                                    >
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mb-8">
                        <h4 class="text-sm font-semibold text-secondary-900 dark:text-white uppercase mb-2">Bill To</h4>
                        <div class="text-sm text-secondary-600 dark:text-secondary-400">
                            <p class="font-medium text-secondary-900 dark:text-white">Acme Corporation</p>
                            <p>John Smith</p>
                            <p>456 Client Avenue</p>
                            <p>New York, NY 10001</p>
                            <p class="mt-2">john@acme.com</p>
                            <p>+1 (555) 987-6543</p>
                        </div>
                    </div>
                    <div class="mb-8">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b-2 border-secondary-200 dark:border-secondary-700">
                                        <th
                                            class="pb-3 text-left text-sm font-semibold text-secondary-900 dark:text-white uppercase">
                                            Description
                                        </th>
                                        <th
                                            class="pb-3 text-center text-sm font-semibold text-secondary-900 dark:text-white uppercase">
                                            Qty
                                        </th>
                                        <th
                                            class="pb-3 text-right text-sm font-semibold text-secondary-900 dark:text-white uppercase">
                                            Rate
                                        </th>
                                        <th
                                            class="pb-3 text-right text-sm font-semibold text-secondary-900 dark:text-white uppercase">
                                            Amount
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-secondary-200 dark:divide-secondary-700">
                                    <tr>
                                        <td class="py-4 text-sm text-secondary-900 dark:text-white">
                                            Web Design Service
                                        </td>
                                        <td class="py-4 text-center text-sm text-secondary-600 dark:text-secondary-400">
                                            1
                                        </td>
                                        <td class="py-4 text-right text-sm text-secondary-600 dark:text-secondary-400">
                                            $1500.00
                                        </td>
                                        <td
                                            class="py-4 text-right text-sm font-medium text-secondary-900 dark:text-white">
                                            $1500.00
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-4 text-sm text-secondary-900 dark:text-white">Logo Design</td>
                                        <td class="py-4 text-center text-sm text-secondary-600 dark:text-secondary-400">
                                            1
                                        </td>
                                        <td class="py-4 text-right text-sm text-secondary-600 dark:text-secondary-400">
                                            $500.00
                                        </td>
                                        <td
                                            class="py-4 text-right text-sm font-medium text-secondary-900 dark:text-white">
                                            $500.00
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-4 text-sm text-secondary-900 dark:text-white">Monthly Hosting</td>
                                        <td class="py-4 text-center text-sm text-secondary-600 dark:text-secondary-400">
                                            12
                                        </td>
                                        <td class="py-4 text-right text-sm text-secondary-600 dark:text-secondary-400">
                                            $25.00
                                        </td>
                                        <td
                                            class="py-4 text-right text-sm font-medium text-secondary-900 dark:text-white">
                                            $300.00
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-4 text-sm text-secondary-900 dark:text-white">
                                            Support &amp; Maintenance
                                        </td>
                                        <td class="py-4 text-center text-sm text-secondary-600 dark:text-secondary-400">
                                            3
                                        </td>
                                        <td class="py-4 text-right text-sm text-secondary-600 dark:text-secondary-400">
                                            $150.00
                                        </td>
                                        <td
                                            class="py-4 text-right text-sm font-medium text-secondary-900 dark:text-white">
                                            $450.00
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
                                ><span class="font-medium text-secondary-900 dark:text-white">$2750.00</span>
                            </div>
                            <div class="flex justify-between py-2 text-sm">
                                <span class="text-secondary-600 dark:text-secondary-400">Tax (10%):</span
                                ><span class="font-medium text-secondary-900 dark:text-white">$275.00</span>
                            </div>
                            <div
                                class="flex justify-between py-3 border-t-2 border-secondary-200 dark:border-secondary-700">
                                <span class="text-lg font-bold text-secondary-900 dark:text-white">Total:</span
                                ><span class="text-lg font-bold text-primary-600 dark:text-primary-400">$3025.00</span>
                            </div>
                        </div>
                    </div>
                    <div class="pt-8 border-t border-secondary-200 dark:border-secondary-700">
                        <h4 class="text-sm font-semibold text-secondary-900 dark:text-white uppercase mb-2">Notes</h4>
                        <p class="text-sm text-secondary-600 dark:text-secondary-400">
                            Thank you for your business. Payment is due within 15 days. Please include invoice number
                            with payment.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
