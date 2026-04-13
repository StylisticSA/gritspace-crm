<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { format } from 'date-fns';
import availabilityModal from '../../Components/Modals/AvailabilityModal.vue';
import useStatusMessage from '../../Composables/useStatusMessage';

const props = defineProps({
    helpDesks: Object,
    filters: Object,
    can: Object,
});

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();

const showModal = ref(false);
const helpdeskToDelete = ref(null);
const search = ref(props.filters.search ?? '');

watch(search, value => {
    router.get(
        route('admin.help-desks'),
        { search: value },
        {
            preserveState: true,
            replace: true,
        }
    );
});

const confirmDelete = id => {
    showModal.value = true;
    helpdeskToDelete.value = id;
};

const deletehelpdesk = () => {
    if (helpdeskToDelete.value) {
        router.delete(route('admin.help-desk.destroy', helpdeskToDelete.value), {
            preserveScroll: true,
            onSuccess: () => {
                message.value = 'A Help Desk has been deleted successfully.';
                status.value = 'deleted';

                setTimeout(() => {
                    router.visit(route('admin.help-desks'));
                    router.reload({ preserveScroll: true });
                }, 2000);
            },
        });
    }
};

const formatLabel = label => {
    if (label === '&laquo; Previous') return 'Prev';
    if (label === 'Next &raquo;') return 'Next';
    return label;
};

const showHotModal = ref(false);
const selectedHotDeskId = ref(null);
const actionHotDesk = ref(null);

const setOffice = id => {
    selectedHotDeskId.value = id;

    const officeList = props.helpDesks?.data || [];
    const match = officeList.find(o => o.id === id);

    if (match) {
        actionHotDesk.value = {
            is_available: match.is_available,
            available_dates: match.available_dates,
        };
    } else {
        actionHotDesk.value = null;
    }

    showHotModal.value = true;
};

const availabilityText = hotdesk => {
    const label = hotdesk.is_available ? 'Available' : 'Unavailable until';
    const date = hotdesk.available_dates ? formatDate(hotdesk.available_dates) : 'None';
    return `${label}<br>${date}`;
};

const formatDate = dateStr => {
    return dateStr ? format(new Date(dateStr), 'dd MMM yyyy') : '';
};
</script>

<template>
    <Head title="Hot Desk Admin" />

    <AuthenticatedLayout>
        <!-- Success Notification -->

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Hot Desks</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="p-2">
                    <!-- Search Filter -->
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <Link
                            v-if="can['create hot desks']"
                            :href="route('admin.help-desk.create')"
                            class="inline-block px-3 py-2 text-lg font-medium text-white rounded bg-primary hover:bg-bluemain/60">
                            + Add Hot Desk
                        </Link>
                        <div></div>

                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search..."
                            class="w-full px-4 py-2 text-sm border border-gray-300 rounded-md shadow-sm sm:w-48 focus:outline-none focus:ring-2 focus:ring-bluemain/60" />
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-300 divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Name</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Location</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Price</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Free Hours</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Availability</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="helpdesk in helpDesks.data"
                                    :key="helpdesk.id">
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ helpdesk.help_desk_name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ helpdesk.location.name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ helpdesk.price }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ helpdesk.free_boardroom_hours }}</td>
                                    <td
                                        class="px-6 py-4 text-sm text-gray-800"
                                        v-html="availabilityText(helpdesk)" />

                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        <div class="flex space-x-1">
                                            <button
                                                v-if="can['create hot desks']"
                                                @click="
                                                    setOffice(helpdesk.id);
                                                    showHotModal = true;
                                                "
                                                class="px-2 py-1 text-sm text-white rounded bg-primary hover:bg-primary/60">
                                                Action
                                            </button>

                                            <button
                                                v-if="can['edit hot desks']"
                                                @click="$inertia.visit(route('admin.help-desk.edit', helpdesk.id))"
                                                class="px-2 py-1 text-sm text-white rounded bg-bluemain hover:bg-bluemain/60">
                                                Edit
                                            </button>
                                            <button
                                                v-if="can['delete hot desks']"
                                                @click="confirmDelete(helpdesk.id)"
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
                            <span class="font-medium">{{ helpDesks?.from }}</span>
                            to
                            <span class="font-medium">{{ helpDesks?.to }}</span>
                            of
                            <span class="font-medium">{{ helpDesks?.total }}</span> results
                        </div>

                        <div class="flex space-x-1">
                            <template
                                v-for="(link, index) in helpDesks?.links"
                                :key="index">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-bluemain/60 bg-bluemain hover:text-white"
                                    :class="link.active ? 'bg-bluemain-700 text-white' : 'text-gray-700'"
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
                            <template v-if="showMessage">
                                <div :class="messageClass">
                                    {{ messageText }}
                                </div>
                            </template>
                            <p class="mb-6">
                                Are you sure you want to delete this help desk? This action cannot be undone.
                            </p>
                            <div class="flex justify-end space-x-3">
                                <button
                                    @click="showModal = false"
                                    class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded hover:bg-gray-200">
                                    Cancel
                                </button>
                                <button
                                    @click="deletehelpdesk"
                                    class="px-4 py-2 text-sm text-white bg-red-600 rounded hover:bg-red-700">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <availabilityModal
                :offices="actionHotDesk"
                :officeid="selectedHotDeskId"
                :show="showHotModal"
                :can="can"
                route-name="admin.hotdesk.availability"
                redirect-route="admin.help-desks"
                :onClose="() => (showHotModal = false)" />
        </div>
    </AuthenticatedLayout>
</template>
