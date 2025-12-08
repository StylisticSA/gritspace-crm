<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch, onMounted } from 'vue';
import FormattedDate from '@/Components/FormatDate.vue';

const props = defineProps({
    clients: Object,
    filters: Object,
    can: Object,
});

const page = usePage();
const search = ref(props.filters.search ?? '');
const successMessage = ref(null);
const flashMessage = computed(() => page.props?.flash?.success || null);
const showMessage = computed(() => {
    return !!(flashMessage.value?.trim?.() || successMessage.value?.trim?.());
});
const showModal = ref(false);
const ClientDelete = ref(null);

onMounted(() => {
    const flash = usePage().props.flash;
    if (flash.success) {
        setTimeout(() => {
            location.reload();
        }, 1200);
    }
});

const formatLabel = label => {
    if (label === '&laquo; Previous') return 'Prev';
    if (label === 'Next &raquo;') return 'Next';
    return label;
};

const confirmDelete = id => {
    showModal.value = true;
    ClientDelete.value = id;
};

const deleteClient = () => {
    if (ClientDelete.value) {
        router.delete(route('admin.clientrates.destroy', ClientDelete.value), {
            preserveScroll: true,
            onSuccess: () => {
                successMessage.value = 'Client Rate has been Deleted successfully.';
                window.scrollTo({ top: 0, behavior: 'smooth' });
                setTimeout(() => {
                    location.reload();
                }, 1500);
            },
            onFinish: () => {
                showModal.value = false;
                ClientDelete.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Discounted Rates Admin" />

    <AuthenticatedLayout>
        <!-- Success Notification -->

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Discounted Rates</h2>
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
                        <div class="flex space-x-2">
                            <Link
                                v-if="can['add users']"
                                :href="route('admin.clientrates.create')"
                                class="inline-block px-2 py-2 text-lg font-medium text-white rounded bg-primary hover:bg-bluemain/60">
                                + Add Discounted Rates
                            </Link>
                        </div>

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
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">ID</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Name</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Price</th>
                                    <!-- <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Discount</th> -->
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Start Date</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">End Date</th>

                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="client in clients.data"
                                    :key="client.id">
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ client.id }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ client.office_name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-800">R {{ client.price }}</td>
                                    <!-- <td class="px-6 py-4 text-sm text-gray-800">
                                        <span
                                            :class="
                                                (client.discount_active ?? 0)
                                                    ? 'bg-green-600 font-semibold px-2 py-1 rounded text-white'
                                                    : 'bg-yellow-600 font-semibold px-2 py-1 rounded text-white'
                                            ">
                                            {{ (client.discount_active ?? 0) ? 'Yes' : 'No' }}
                                        </span>
                                    </td> -->
                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        <FormattedDate :date="client.start_date" />
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        <FormattedDate :date="client.end_date" />
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        <div class="flex space-x-1">
                                            <button
                                                v-if="can['manage settings']"
                                                @click="$inertia.visit(route('clientrates.editCompany', client.id))"
                                                class="px-2 py-1 text-sm text-white rounded bg-bluemain hover:bg-bluemain/60">
                                                Edit
                                            </button>
                                            <button
                                                v-if="can['manage settings']"
                                                @click="confirmDelete(client.id)"
                                                class="px-2 py-1 text-sm text-white bg-red-500 rounded hover:bg-red-600">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between mt-4">
                        <div class="text-sm text-gray-600">
                            Showing
                            <span class="font-medium">{{ clients.from }}</span>
                            to
                            <span class="font-medium">{{ clients.to }}</span>
                            of
                            <span class="font-medium">{{ clients.total }}</span> results
                        </div>

                        <div class="flex space-x-1">
                            <template
                                v-for="(link, index) in clients.links"
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
                                Are you sure you want to delete this client? This action cannot be undone.
                            </p>
                            <div class="flex justify-end space-x-3">
                                <button
                                    v-if="can['manage settings']"
                                    @click="showModal = false"
                                    class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded hover:bg-gray-200">
                                    Cancel
                                </button>
                                <button
                                    v-if="can['manage settings']"
                                    @click="deleteClient"
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
