<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import useStatusMessage from '../../Composables/useStatusMessage';

const props = defineProps({
    banking: Object,
    filters: Object,
    can: Object,
});

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();

const search = ref(props.filters.search ?? '');
watch(search, value => {
    router.get(
        route('admin.banking'),
        { search: value },
        {
            preserveState: true,
            replace: true,
        }
    );
});

const showModal = ref(false);
const bankingToDelete = ref(null);

const confirmDelete = id => {
    showModal.value = true;
    bankingToDelete.value = id;
};

const deletebanking = () => {
    if (bankingToDelete.value) {
        router.delete(route('admin.banking.destroy', bankingToDelete.value), {
            preserveScroll: true,
            onSuccess: () => {
                message.value = 'Banking Details have been Updated Successfully.';
                status.value = 'success';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
            onFinish: () => {
                showModal.value = false;
                bankingToDelete.value = null;
            },
        });
    }
};

const formatLabel = label => {
    if (label === '&laquo; Previous') return 'Prev';
    if (label === 'Next &raquo;') return 'Next';
    return label;
};
</script>

<template>
    <Head title="Banking Admin" />

    <AuthenticatedLayout>
        <!-- Success Notification -->

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Banking Details</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="p-2">
                    <!-- Search Filter -->
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="space-x-2">
                            <Link
                                v-if="can['manage settings']"
                                :href="route('admin.banking.create')"
                                class="inline-block px-2 py-2 text-lg font-medium text-white rounded bg-primary hover:bg-bluemain/60">
                                + Add Banking
                            </Link>
                            <Link
                                v-if="can['manage settings']"
                                :href="route('admin.invoices.index')"
                                class="inline-block px-2 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
                                Invoices
                            </Link>
                        </div>

                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search..."
                            class="w-full px-4 py-2 text-sm border border-gray-300 rounded-md shadow-sm sm:w-48 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>

                    <template v-if="showMessage">
                        <div :class="messageClass">
                            {{ messageText }}
                        </div>
                    </template>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-300 divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Company Name</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Banking Name</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">
                                        Account Holder
                                    </th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">
                                        Account Number
                                    </th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Branch Code</th>

                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="banking in banking.data"
                                    :key="banking.id">
                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        {{ banking.company?.company_name || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ banking.bank_name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ banking.account_holder }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ banking.account_number }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ banking.branch_code }}</td>

                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        <div class="flex space-x-1">
                                            <button
                                                @click="$inertia.visit(route('admin.banking.edit', banking.id))"
                                                class="px-2 py-1 text-sm text-white rounded bg-bluemain hover:bg-bluemain/60">
                                                Edit
                                            </button>
                                            <button
                                                @click="confirmDelete(banking.id)"
                                                class="px-3 py-1 text-sm text-white bg-red-500 rounded hover:bg-red-600">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="flex items-center justify-between mt-4">
                        <div class="text-sm text-gray-600">
                            Showing
                            <span class="font-medium">{{ banking.from }}</span>
                            to
                            <span class="font-medium">{{ banking.to }}</span>
                            of
                            <span class="font-medium">{{ banking.total }}</span> results
                        </div>

                        <div class="flex space-x-1">
                            <template
                                v-for="(link, index) in banking.links"
                                :key="index">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-bluemain/60 hover:text-white"
                                    :class="link.active ? 'bg-bluemain text-white' : 'text-gray-700'"
                                    v-html="formatLabel(link.label)" />
                                <span
                                    v-else
                                    class="px-3 py-1 text-sm text-gray-400 border border-gray-300 rounded-md cursor-not-allowed"
                                    v-html="formatLabel(link.label)" />
                            </template>
                        </div>
                    </div>
                </div>

                <template v-if="showModal">
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                        <div class="w-full max-w-md p-6 bg-white rounded shadow">
                            <h2 class="mb-4 text-lg font-semibold">Confirm Delete</h2>
                            <p class="mb-6">
                                Are you sure you want to delete this banking? This action cannot be undone.
                            </p>
                            <div class="flex justify-end space-x-3">
                                <button
                                    @click="showModal = false"
                                    class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded hover:bg-gray-200">
                                    Cancel
                                </button>
                                <button
                                    @click="deletebanking"
                                    class="px-4 py-2 text-sm text-white bg-red-600 rounded hover:bg-red-700">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
