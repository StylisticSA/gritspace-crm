<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { format } from 'date-fns';
import CloseHoursModal from '../../Components/Modals/Hours/CloseHoursModal.vue';

const props = defineProps({
    boardhours: Object,
    Users: Object,
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
const hoursDelete = ref(null);
const showHoursCloseModal = ref(false);

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
        route('admin.boardrom_hours.index'),
        { search: value },
        {
            preserveState: true,
            replace: true,
        }
    );
});

const confirmDelete = id => {
    showModal.value = true;
    hoursDelete.value = id;
};

const deleteamenity = () => {
    if (hoursDelete.value) {
        router.delete(route('admin.boardroom_hours.destroy', hoursDelete.value), {
            preserveScroll: true,
            onSuccess: () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
            onFinish: () => {
                showModal.value = false;
                hoursDelete.value = null;
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
    <Head title="Hours Admin" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Boardrooms Hours</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Success Notification -->
                <template v-if="showMessage">
                    <div class="p-3 mb-4 text-green-800 bg-green-100 rounded">
                        {{ successMessage || flashMessage || '✔️ Success' }}
                    </div>
                </template>

                <div class="p-2">
                    <!-- Search Filter -->
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <Link
                            v-if="can['manage settings']"
                            :href="route('admin.hours.create')"
                            class="inline-block px-2 py-2 text-lg font-medium text-white rounded bg-primary hover:bg-bluemain/60">
                            + Add hours
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
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">User</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Boardroom</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Used Hours</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Status</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Stated At</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="cofe in boardhours.data"
                                    :key="cofe.id">
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ cofe.user.name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        {{ cofe.boardroom?.boardroom_name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-800 text-center">{{ cofe.hours_used }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        <span
                                            :class="{
                                                'px-2 py-1 rounded text-xs font-semibold capitalize': true,
                                                'bg-yellow-100 text-yellow-800': cofe.status === 'in_progress',
                                                'bg-green-100 text-green-800': cofe.status === 'closed',
                                                'bg-gray-100 text-gray-800': !['in_progress', 'closed'].includes(
                                                    cofe.status
                                                ),
                                            }">
                                            {{
                                                cofe.status === 'in_progress'
                                                    ? 'In Progress'
                                                    : cofe.status === 'closed'
                                                      ? 'Closed'
                                                      : 'N/A'
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ formatDate(cofe.created_at) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        <div class="flex space-x-1">
                                            <button
                                                v-if="can['manage settings'] && cofe.status != 'closed'"
                                                @click="showHoursCloseModal = true"
                                                class="px-2 py-1 text-sm text-white rounded bg-bluemain hover:bg-bluemain/60">
                                                Action
                                            </button>
                                            <!-- <button
                                                v-if="can['manage settings']"
                                                @click="$inertia.visit(route('admin.hours.edit', cofe.id))"
                                                class="px-2 py-1 text-sm text-white rounded bg-bluemain hover:bg-bluemain/60">
                                                Edit
                                            </button> -->
                                            <button
                                                v-if="can['manage settings']"
                                                @click="confirmDelete(cofe.id)"
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
                            <span class="font-medium">{{ boardhours.from }}</span>
                            to
                            <span class="font-medium">{{ boardhours.to }}</span>
                            of
                            <span class="font-medium">{{ boardhours.total }}</span> results
                        </div>

                        <div class="flex space-x-1">
                            <template
                                v-for="(link, index) in boardhours.links"
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
                                Are you sure you want to delete this Boardroom Hour? This action cannot be undone.
                            </p>
                            <div class="flex justify-end space-x-3">
                                <button
                                    @click="showModal = false"
                                    class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded hover:bg-gray-200">
                                    Cancel
                                </button>
                                <button
                                    @click="deleteamenity"
                                    class="px-4 py-2 text-sm text-white bg-red-600 rounded hover:bg-red-700">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            <CloseHoursModal
                :type="3"
                :can="can"
                :show="showHoursCloseModal"
                :onClose="() => (showHoursCloseModal = false)" />
        </div>
    </AuthenticatedLayout>
</template>
