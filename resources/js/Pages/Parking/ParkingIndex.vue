<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { format } from 'date-fns';
import availabilityModal from '../../Components/Modals/AvailabilityModal.vue';
import useStatusMessage from '../../Composables/useStatusMessage';

const props = defineProps({
    parking: Object,
    filters: Object,
    can: Object,
});

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();

const search = ref(props.filters.search ?? '');
const showModal = ref(false);
const amenityToDelete = ref(null);

watch(search, value => {
    router.get(
        route('admin.parking.index'),
        { search: value },
        {
            preserveState: true,
            replace: true,
        }
    );
});

const confirmDelete = id => {
    showModal.value = true;
    amenityToDelete.value = id;
};

const deleteparking = () => {
    if (amenityToDelete.value) {
        router.delete(route('admin.parking.destroy', amenityToDelete.value), {
            preserveScroll: true,
            onSuccess: () => {
                message.value = 'The document has been updated.';
                status.value = 'success';

                window.scrollTo({ top: 0, behavior: 'smooth' });
                setTimeout(() => {
                    router.visit(route('admin.parking.index'));
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

const formatDate = dateStr => {
    return dateStr ? format(new Date(dateStr), 'dd MMM yyyy') : '';
};

const showAvailModal = ref(false);
const selectedOfficeId = ref(null);
const actionOffice = ref(null);

const setOffice = id => {
    selectedOfficeId.value = id;

    const officeList = props.parking?.data || [];
    const match = officeList.find(o => o.id === id);

    if (match) {
        actionOffice.value = {
            is_available: match.is_available,
            available_dates: match.available_dates,
        };
    } else {
        actionOffice.value = null;
    }

    showAvailModal.value = true;
};

const availabilityText = parking => {
    const label = parking.is_available ? 'Available From' : 'Not Available Up';
    const date = parking.available_dates ? formatDate(parking.available_dates) : '—';
    return `${label}<br>${date}`;
};
</script>

<template>
    <Head title="parking settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Parking</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="p-2">
                    <template v-if="showMessage">
                        <div :class="messageClass">
                            {{ messageText }}
                        </div>
                    </template>
                    <!-- Search Filter -->
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <Link
                            :href="route('admin.parking.create')"
                            class="inline-block px-2 py-2 text-lg font-medium text-white rounded bg-primary hover:bg-bluemain/60">
                            + Add Parking
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
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">ID</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Location</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Name</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Price</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Available On</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="park in parking.data"
                                    :key="park.id">
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ park.id }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ park.location?.name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ park.name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ park.price }}</td>
                                    <td
                                        class="px-6 py-4 text-sm text-gray-800"
                                        v-html="availabilityText(park)" />

                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        <div class="flex space-x-1">
                                            <button
                                                @click="
                                                    setOffice(park.id);
                                                    showAvailModal = true;
                                                "
                                                class="px-2 py-1 text-sm text-white rounded bg-primary hover:bg-primary/60">
                                                Action
                                            </button>
                                            <button
                                                v-if="can['manage settings']"
                                                @click="$inertia.visit(route('admin.parking.edit', park.id))"
                                                class="px-2 py-1 text-sm text-white rounded bg-bluemain hover:bg-bluemain/60">
                                                Edit
                                            </button>
                                            <button
                                                v-if="can['delete amenities']"
                                                @click="confirmDelete(park.id)"
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
                            <span class="font-medium">{{ parking.from }}</span>
                            to
                            <span class="font-medium">{{ parking.to }}</span>
                            of
                            <span class="font-medium">{{ parking.total }}</span> results
                        </div>

                        <div class="flex space-x-1">
                            <template
                                v-for="(link, index) in parking.links"
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
                                Are you sure you want to delete this parking settings? This action cannot be undone.
                            </p>
                            <div class="flex justify-end space-x-3">
                                <button
                                    @click="showModal = false"
                                    class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded hover:bg-gray-200">
                                    Cancel
                                </button>
                                <button
                                    @click="deleteparking"
                                    class="px-4 py-2 text-sm text-white bg-red-600 rounded hover:bg-red-700">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <availabilityModal
                :offices="actionOffice"
                :officeid="selectedOfficeId"
                :show="showAvailModal"
                :can="can"
                route-name="admin.parking.availability"
                redirect-route="admin.parking.index"
                :onClose="() => (showAvailModal = false)" />
        </div>
    </AuthenticatedLayout>
</template>
