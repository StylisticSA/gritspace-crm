<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import FormattedDate from '@/Components/FormatDate.vue';

const props = defineProps({
    officesRates: Object,
    filters: Object,
    can: Object,
});

console.log('can', props.can);

const page = usePage();
const search = ref(props.filters.search ?? '');
const successMessage = ref(null);
const flashMessage = computed(() => page.props?.flash?.success || null);
const showMessage = computed(() => {
    return !!(flashMessage.value?.trim?.() || successMessage.value?.trim?.());
});
const showModal = ref(false);
const officeRateDelete = ref(null);

watch(showMessage, msg => {
    if (msg) {
        setTimeout(() => {
            successMessage.value = null;
        }, 3000);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
});

watch(search, value => {
    router.get(
        route('admin.offices_rates'),
        { search: value },
        {
            preserveState: true,
            replace: true,
        }
    );
});

const confirmDelete = id => {
    showModal.value = true;
    officeRateDelete.value = id;
};

const deleteOffice = () => {
    if (officeRateDelete.value) {
        router.delete(route('admin.offices_rates.destroy', officeRateDelete.value), {
            preserveScroll: true,
            onSuccess: () => {
                successMessage.value = 'Office deleted successfully.';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
            onFinish: () => {
                showModal.value = false;
                officeRateDelete.value = null;
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
    <Head title="Offices Rates Admin" />

    <AuthenticatedLayout>
        <!-- Success Notification -->

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Service Levels</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <template v-if="showMessage">
                    <div class="p-3 mb-4 text-green-800 bg-green-100 rounded">
                        {{ successMessage || flashMessage || '✔️ Success' }}
                    </div>
                </template>

                <div class="p-2">
                    <!-- Search Filter -->
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <Link
                            v-if="can['create office rates']"
                            :href="route('admin.offices_rates.create')"
                            class="inline-block px-3 py-2 text-lg font-medium text-white rounded bg-primary hover:bg-bluemain/60">
                            + Add Service Levels
                        </Link>
                        <div></div>

                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search..."
                            class="w-full px-4 py-2 text-sm border border-gray-300 rounded-md shadow-sm sm:w-48 focus:outline-none focus:ring-2 focus:ring-blumain" />
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-300 divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">ID</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Category Name</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Service Level</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Price</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Date Created</th>

                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="rates in officesRates.data"
                                    :key="rates.id">
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ rates.id }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        {{ rates.category_name.charAt(0).toUpperCase() + rates.category_name.slice(1) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ rates.pricing_type }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ rates.rate }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        <FormattedDate :date="rates.created_at" />
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        <div class="flex space-x-1">
                                            <button
                                                v-if="can['edit office rates']"
                                                @click="$inertia.visit(route('admin.offices_rates.edit', rates.id))"
                                                class="px-2 py-1 text-sm text-white rounded bg-bluemain hover:bg-bluemain/60">
                                                Edit
                                            </button>
                                            <button
                                                v-if="can['delete office rates']"
                                                @click="confirmDelete(rates.id)"
                                                class="px-2 py-1 text-sm text-white bg-red-500 rounded hover:bg-red-600">
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
                            <span class="font-medium">{{ officesRates.from }}</span>
                            to
                            <span class="font-medium">{{ officesRates.to }}</span>
                            of
                            <span class="font-medium">{{ officesRates.total }}</span> results
                        </div>

                        <div class="flex space-x-1">
                            <template
                                v-for="(link, index) in officesRates.links"
                                :key="index">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-bluemain/60 bg-bluemain hover:text-white"
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
                                Are you sure you want to delete this office? This action cannot be undone.
                            </p>
                            <div class="flex justify-end space-x-3">
                                <button
                                    @click="showModal = false"
                                    class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded hover:bg-gray-200">
                                    Cancel
                                </button>
                                <button
                                    @click="deleteOffice"
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
