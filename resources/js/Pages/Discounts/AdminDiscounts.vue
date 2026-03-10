<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { format } from 'date-fns';
import useStatusMessage from '../../Composables/useStatusMessage';

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();

const props = defineProps({
    discounts: Object,
    filters: Object,
    can: Object,
});

const search = ref(props.filters.search ?? '');

const showModal = ref(false);
const discountToDelete = ref(null);

watch(search, value => {
    router.get(
        route('admin.discounts.index'),
        { search: value },
        {
            preserveState: true,
            replace: true,
        }
    );
});

const confirmDelete = id => {
    showModal.value = true;
    discountToDelete.value = id;
};

const deletediscount = () => {
    if (discountToDelete.value) {
        router.delete(route('admin.discount.destroy', discountToDelete.value), {
            preserveScroll: true,
            onSuccess: () => {
                message.value = 'Discounts has been deleted successfully.';
                status.value = 'success';
                window.scrollTo({ top: 0, behavior: 'smooth' });

                setTimeout(() => {
                    router.reload({ preserveScroll: true });
                    router.visit(route('admin.discounts.index'));
                }, 2000);
            },
            onFinish: () => {
                showModal.value = false;
                discountToDelete.value = null;
            },
        });
    }
};

const formatLabel = label => {
    if (label === '&laquo; Previous') return 'Prev';
    if (label === 'Next &raquo;') return 'Next';
    return label;
};

const formatDate = dateStr => {
    return dateStr ? format(new Date(dateStr), 'dd MMM yyyy') : '';
};
</script>

<template>
    <Head title="discounts Admin" />

    <AuthenticatedLayout>
        <!-- Success Notification -->

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Discounts</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <template v-if="showMessage">
                    <div :class="messageClass">
                        {{ messageText }}
                    </div>
                </template>
                <div class="p-2">
                    <!-- Search Filter -->
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <Link
                            :href="route('admin.discount.create')"
                            class="inline-block px-2 py-2 text-lg font-medium text-white rounded bg-primary hover:bg-bluemain/60">
                            + Add Discounts
                        </Link>
                        <div></div>

                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search..."
                            class="w-full px-4 py-2 text-sm border border-gray-300 rounded-md shadow-sm sm:w-48 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-300 divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Location</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Name</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Packadge</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Discount</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="discount in discounts.data"
                                    :key="discount.id">
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ discount.location?.name }}</td>

                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        {{ discount.name }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        {{ discount.package }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-800">{{ discount.discount }}</td>

                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        <div class="flex space-x-1">
                                            <button
                                                @click="$inertia.visit(route('admin.discount.edit', discount.id))"
                                                class="px-2 py-1 text-sm text-white rounded bg-bluemain hover:bg-bluemain/60">
                                                Edit
                                            </button>
                                            <button
                                                @click="confirmDelete(discount.id)"
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
                            <span class="font-medium">{{ discounts.from }}</span>
                            to
                            <span class="font-medium">{{ discounts.to }}</span>
                            of
                            <span class="font-medium">{{ discounts.total }}</span> results
                        </div>

                        <div class="flex space-x-1">
                            <template
                                v-for="(link, index) in discounts.links"
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
                                Are you sure you want to delete this discount? This action cannot be undone.
                            </p>
                            <div class="flex justify-end space-x-3">
                                <button
                                    @click="showModal = false"
                                    class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded hover:bg-gray-200">
                                    Cancel
                                </button>
                                <button
                                    @click="deletediscount"
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
