<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import useStatusMessage from '../../../Composables/useStatusMessage';

const props = defineProps({
    agreements: Object,
    locations: Array,
    filters: Object,
    can: Object,
});

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();

const search = ref(props.filters.search ?? '');
const showModal = ref(false);
const showApproveModal = ref(false);
const agreeToDelete = ref(null);

watch(search, value => {
    router.get(
        route('admin.agreements'),
        { search: value },
        {
            preserveState: true,
            replace: true,
        }
    );
});

const confirmDelete = id => {
    showModal.value = true;
    agreeToDelete.value = id;
};

const deleteagree = () => {
    if (agreeToDelete.value) {
        router.delete(route('admin.agreement.destroy', agreeToDelete.value), {
            preserveScroll: true,
            onSuccess: () => {
                message.value = 'An agree has been deleted successfully.';
                status.value = 'deleted';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
            onFinish: () => {
                showModal.value = false;
                agreeToDelete.value = null;
            },
        });
    }
};

const formatLabel = label => {
    if (label === '&laquo; Previous') return 'Prev';
    if (label === 'Next &raquo;') return 'Next';
    return label;
};

const agreementToEdit = ref(null);

const approveModal = agreement => {
    agreementToEdit.value = agreement?.id;
    showApproveModal.value = true;
};

const approve = () => {
    const id = agreementToEdit.value;
    if (!id) return;

    const payload = {
        status: 'approved',
    };

    router.put(route('admin.agreement.approve', id), payload, {
        preserveScroll: true,
        onSuccess: () => {
            message.value = 'Agreement approved successfully.';
            status.value = 'success';

            setTimeout(() => {
                showApproveModal.value = false;
            }, 1500);
        },
        onError: () => {
            message.value = 'Failed to approve booking.';
            status.value = 'deleted';
        },
    });
};

const pending = () => {
    const id = agreementToEdit.value;
    if (!id) return;

    const payload = {
        status: 'pending',
    };

    router.put(route('admin.agreement.pending', id), payload, {
        preserveScroll: true,
        onSuccess: () => {
            message.value = 'Agreement status is now on Pending.';
            status.value = 'pending';
            setTimeout(() => {
                showApproveModal.value = false;
            }, 1500);
        },
        onError: () => {
            message.value = 'Failed to approve booking.';
            status.value = 'deleted';
        },
    });
};

import useInactivityMonitor from '../../../Composables/useActivityMonitor';

const { showWarning } = useInactivityMonitor({
    timeoutMs: 600000,
    warnMs: 540000,
});
</script>

<template>
    <Head title="Agreements Uploads" />

    <AuthenticatedLayout>
        <!-- Success Notification -->

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Agreements Uploads</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <template v-if="showWarning">
                    <div class="fixed p-4 text-red-800 bg-red-100 rounded shadow bottom-4 right-4">
                        You’ve been inactive. You’ll be logged out soon for security.
                    </div>
                </template>

                <div class="p-2">
                    <!-- Search Filter -->
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <Link
                                v-if="can['create agreements']"
                                :href="route('admin.agreements.create')"
                                class="inline-block px-2 py-2 text-lg font-medium text-white rounded bg-primary hover:bg-bluemain/60">
                                + Add Agreements Uploads
                            </Link>
                            <Link
                                :href="route('admin.clientinfor.index')"
                                class="inline-block px-2 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
                                Clients Information
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
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Username</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Location</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">File</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Status</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="agree in agreements.data"
                                    :key="agree.id">
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ agree.id }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ agree.user?.name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ agree.location?.name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        <a
                                            v-if="agree.agreement"
                                            :href="agree.agreement"
                                            target="_blank"
                                            class="underline text-primary">
                                            View User Agreement
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span
                                            :class="{
                                                'px-2 py-1 rounded text-xs font-semibold capitalize': true,
                                                'bg-yellow-100 text-yellow-800': agree.status === 'pending',
                                                'bg-green-100 text-green-800': agree.status === 'approved',
                                                'bg-red-100 text-red-700': agree.status === 'rejected',
                                            }">
                                            {{ agree.status ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        <div class="flex space-x-1">
                                            <button
                                                v-if="can['manage settings']"
                                                @click="approveModal(agree)"
                                                class="px-2 py-1 text-sm text-white rounded bg-primary hover:bg-primary/60">
                                                Action
                                            </button>
                                            <button
                                                @click="$inertia.visit(route('admin.agreement.edit', agree.id))"
                                                class="px-3 py-1 text-sm text-white rounded bg-bluemain hover:bg-bluemain/60">
                                                Edit
                                            </button>
                                            <button
                                                v-if="can['manage settings']"
                                                @click="confirmDelete(agree.id)"
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
                            <span class="font-medium">{{ agreements.from }}</span>
                            to
                            <span class="font-medium">{{ agreements.to }}</span>
                            of
                            <span class="font-medium">{{ agreements.total }}</span> results
                        </div>

                        <div class="flex space-x-1">
                            <template
                                v-for="(link, index) in agreements.links"
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
                                Are you sure you want to delete this agree? This action cannot be undone.
                            </p>
                            <div class="flex justify-end space-x-3">
                                <button
                                    @click="showModal = false"
                                    class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded hover:bg-gray-200">
                                    Cancel
                                </button>
                                <button
                                    @click="deleteagree"
                                    class="px-4 py-2 text-sm text-white bg-red-600 rounded hover:bg-red-700">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <template v-if="showApproveModal">
                    <div class="fixed inset-0 z-50 flex items-center justify-center p-5 bg-black bg-opacity-50">
                        <div class="w-full max-w-xl p-6 bg-white rounded shadow">
                            <h2 class="mb-4 text-xl font-medium">Approve the Agreement</h2>

                            <template v-if="showMessage">
                                <div :class="messageClass">
                                    {{ messageText }}
                                </div>
                            </template>

                            <hr class="my-5 border-gray-300" />
                            <p>Please make sure you have cross checked the document, and you are satisfied.</p>
                            <hr class="my-5 border-gray-300" />

                            <div class="flex justify-between space-x-3">
                                <!--  @click="deleteagree" -->
                                <div class="space-x-2">
                                    <button
                                        v-if="can['manage settings']"
                                        @click="approve(agreements)"
                                        class="px-4 py-2 text-sm text-white bg-green-600 rounded hover:bg-green-700">
                                        Approve
                                    </button>
                                    <button
                                        v-if="can['manage settings']"
                                        @click="pending(agreements)"
                                        class="px-4 py-2 text-sm text-white rounded bg-bluemain hover:bg-bluemain/60">
                                        Pending
                                    </button>
                                </div>

                                <button
                                    @click="showApproveModal = false"
                                    class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded hover:bg-gray-200">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
