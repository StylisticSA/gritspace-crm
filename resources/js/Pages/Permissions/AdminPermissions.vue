<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    permissions: Object,
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
const PermissionToDelete = ref(null);

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
        route('admin.permissions'),
        { search: value },
        {
            preserveState: true,
            replace: true,
        }
    );
});

const confirmDelete = id => {
    showModal.value = true;
    PermissionToDelete.value = id;
};

const deletePermission = () => {
    if (PermissionToDelete.value) {
        router.delete(route('admin.permission.destroy', PermissionToDelete.value), {
            preserveScroll: true,
            onSuccess: () => {
                successMessage.value = 'User access deleted successfully.';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
            onFinish: () => {
                showModal.value = false;
                PermissionToDelete.value = null;
            },
        });
    }
};

const formatLabel = label => {
    if (label === '&laquo; Previous') return 'Prev';
    if (label === 'Next &raquo;') return 'Next';
    return label;
};

function getRoleColor(role) {
    switch (role.toLowerCase()) {
        case 'Create':
            return 'bg-green-100 text-red-800';
        case 'Edit':
            return 'bg-yellow-100 text-yellow-800';
        case 'Delete':
            return 'bg-red-100 text-green-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}
</script>

<template>
    <Head title="User Access Admin" />

    <AuthenticatedLayout>
        <!-- Success Notification -->

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Permissions</h2>
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
                                :href="route('admin.permission.create')"
                                class="inline-block px-2 py-2 text-lg font-medium text-white rounded bg-primary hover:bg-bluemain/60">
                                + Add Permissions
                            </Link>

                            <Link
                                :href="route('admin.roles')"
                                class="inline-block px-2 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
                                Roles
                            </Link>

                            <Link
                                :href="route('admin.manage.user')"
                                class="inline-block px-2 py-2 text-lg font-medium text-white rounded bg-muted hover:bg-bluemain/60">
                                Manage
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
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="permission in permissions.data"
                                    :key="permission.id">
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ permission.id }}</td>

                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        {{ capitalize(permission.name) }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        <div class="flex space-x-1">
                                            <button
                                                v-if="can['edit permissions'] || can['manage settings']"
                                                @click="$inertia.visit(route('admin.permission.edit', permission.id))"
                                                class="px-3 py-1 text-sm text-white rounded bg-bluemain hover:bg-bluemain/60">
                                                Edit
                                            </button>
                                            <button
                                                v-if="can['delete permissions'] || can['manage settings']"
                                                @click="confirmDelete(permission.id)"
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
                            <span class="font-medium">{{ permissions.from }}</span>
                            to
                            <span class="font-medium">{{ permissions.to }}</span>
                            of
                            <span class="font-medium">{{ permissions.total }}</span> results
                        </div>

                        <div class="flex space-x-1">
                            <template
                                v-for="(link, index) in permissions.links"
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
                            <p class="mb-6">Are you sure you want to delete this role? This action cannot be undone.</p>
                            <div class="flex justify-end space-x-3">
                                <button
                                    @click="showModal = false"
                                    class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded hover:bg-gray-200">
                                    Cancel
                                </button>
                                <button
                                    @click="deletePermission"
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
