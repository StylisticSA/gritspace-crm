<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watchEffect, nextTick, watch } from 'vue';
import FormattedDate from '@/Components/FormatDate.vue';
import GlobalNoteModal from '@/Components/Modals/NoteModal.vue';
import useStatusMessage from '../../../Composables/useStatusMessage';

const props = defineProps({
    clients: Object,
    users: Object,
    filters: Object,
    can: Object,
    flash: Object,
    errors: Object,
});

const showNoteModal = ref(false);
const search = ref(props.filters.search ?? '');
const showModal = ref(false);
const ClientDelete = ref(null);
const showInfoModal = ref(false);
const companyInfo = ref(null);
const modalBody = ref(null);

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();
const modalActive = computed(() => showInfoModal.value || showModal.value);

const confirmDelete = id => {
    showModal.value = true;
    ClientDelete.value = id;
};

const viewInfoModal = client => {
    companyInfo.value = client;
    showInfoModal.value = true;
};

const closeInfoModal = () => {
    companyInfo.value = null;
    showInfoModal.value = false;

    router.visit(route('admin.clientinfor.index'), {
        preserveScroll: true,
    });
};

const formatLabel = label => {
    if (label === '&laquo; Previous') return 'Prev';
    if (label === 'Next &raquo;') return 'Next';
    return label;
};

const deleteClient = () => {
    if (ClientDelete.value) {
        router.delete(route('admin.clientinfor.destroy', ClientDelete.value), {
            preserveScroll: true,
            onSuccess: () => {
                message.value = props.flash.error;
                status.value = 'deleted';

                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
            onFinish: () => {
                showModal.value = false;
                ClientDelete.value = null;
            },
        });
    }
};

const approveClient = id => {
    if (!id) return;

    router.put(route('admin.clientinfor.approve', id), {
        preserveScroll: true,
        onSuccess: () => {
            message.value = 'Client Approved successfully.';
            status.value = 'success';
        },
    });
};

const deactiveClient = id => {
    if (!id) return;

    router.put(
        route('admin.clientinfor.deactive', id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                message.value = 'Client Deactivated successfully.';
                status.value = 'deleted';

                setTimeout(() => {
                    router.visit(route('admin.clientinfor.index'));
                }, 2000);
            },
        }
    );
};

const groupedRates = computed(() => {
    const base = {
        closed: [],
        dedicated: [],
        hotdesk: [],
        virtual: [],
        boardroom: [],
    };

    if (!companyInfo.value?.rates?.length) return {};

    companyInfo.value.rates.forEach(rate => {
        const type = rate.type?.toLowerCase();
        if (base[type]) {
            base[type].push(rate);
        }
    });

    return Object.fromEntries(Object.entries(base).filter(([_, rates]) => rates.length > 0));
});

watch(modalActive, active => {
    const body = document.body;
    if (active) {
        body.classList.add('overflow-hidden');
    } else {
        body.classList.remove('overflow-hidden');
    }
});

watchEffect(() => {
    if (props.errors?.available) {
        message.value = props.errors.available;
        status.value = 'deleted';

        nextTick(() => {
            modalBody.value?.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
});
</script>

<template>
    <Head title="Manage Clients Admin" />

    <AuthenticatedLayout>
        <!-- Success Notification -->

        <template #header>
            <div class="flex items-center justify-between space-x-5">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Clients Information</h2>
                <button
                    @click="showNoteModal = true"
                    class="px-2 py-2 text-lg text-white rounded bg-bluemain hover:bluemain/60">
                    Add Note
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="p-2">
                    <!-- Search Filter -->
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex space-x-2">
                            <Link
                                v-if="can['add users']"
                                :href="route('admin.clientinfor.create')"
                                class="inline-block px-2 py-2 text-lg font-medium text-white rounded bg-primary hover:bg-bluemain/60">
                                + Add Client
                            </Link>
                            <Link
                                :href="route('admin.agreement.index')"
                                class="inline-block px-2 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
                                Agreements
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
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700 md:hidden">
                                        SurName
                                    </th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Email</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Cell Number</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Status</th>
                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Date Created</th>

                                    <th class="px-6 py-3 text-sm font-medium text-left text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="client in clients.data"
                                    :key="client.id">
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ client.id }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ client.name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-800 sm:hidden">{{ client.surname }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ client.email_address }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-800">{{ client.cell_number }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        <span
                                            :class="
                                                (client.approved ?? 0)
                                                    ? 'bg-green-600 font-semibold px-2 py-1 rounded text-white'
                                                    : 'bg-yellow-600 font-semibold px-2 py-1 rounded text-white'
                                            ">
                                            {{ (client.approved ?? 0) ? 'Approved' : 'Pending' }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        <FormattedDate :date="client.created_at" />
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-800">
                                        <div class="flex space-x-1">
                                            <button
                                                v-if="can['manage settings']"
                                                @click="viewInfoModal(client)"
                                                class="px-2 py-1 text-sm text-white rounded bg-primary hover:bg-bluemain/60">
                                                Action
                                            </button>
                                            <button
                                                v-if="can['manage settings']"
                                                @click="$inertia.visit(route('admin.clientinfor.edit', client.id))"
                                                class="px-2 py-1 text-sm text-white rounded bg-bluemain hover:bg-bluemain/60">
                                                Edit
                                            </button>
                                            <button
                                                v-show="!client.approved"
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

                <!-- Delete modal -->
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

                <!-- Company Information -->
                <template v-if="showInfoModal">
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                        <div class="w-full max-w-3xl mx-3 bg-white rounded-lg shadow-lg max-h-[80vh] flex flex-col">
                            <!-- Sticky Header -->
                            <div class="sticky top-0 z-10 p-6 bg-white border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                                        Clients Information
                                    </h2>
                                    <button
                                        @click="closeInfoModal"
                                        class="text-3xl leading-none text-black hover:text-gray-700">
                                        &times;
                                    </button>
                                </div>
                            </div>

                            <!-- Scrollable Content -->
                            <div
                                ref="modalBody"
                                class="px-6 py-4 overflow-y-auto"
                                style="max-height: calc(80vh - 72px)">
                                <template v-if="showMessage">
                                    <div :class="messageClass">
                                        {{ messageText }}
                                    </div>
                                </template>
                                <!-- <div
                                    v-if="props.errors?.available"
                                    class="p-3 text-red-800 bg-red-100 rounded">
                                    {{ props.errors.available }}
                                </div> -->

                                <!-- Client Info Grid -->
                                <div class="grid grid-cols-2 mt-10 gap-x-4 gap-y-2">
                                    <div class="font-semibold text-gray-700">Location:</div>
                                    <div>{{ companyInfo.location?.name ?? 'N/A' }}</div>

                                    <hr class="col-span-2 my-2 border-gray-300" />

                                    <div class="font-semibold text-gray-700">First Name:</div>
                                    <div>{{ companyInfo.name }}</div>

                                    <div class="font-semibold text-gray-700">Surname:</div>
                                    <div>{{ companyInfo.surname }}</div>

                                    <hr class="col-span-2 my-2 border-gray-300" />

                                    <div class="font-semibold text-gray-700">Cell Number:</div>
                                    <div>{{ companyInfo.cell_number }}</div>

                                    <div class="font-semibold text-gray-700">Email Address:</div>
                                    <div>{{ companyInfo.email_address }}</div>

                                    <hr class="col-span-2 my-2 border-gray-300" />

                                    <div class="font-semibold text-gray-700">Company Name:</div>
                                    <div>{{ companyInfo.company_name }}</div>

                                    <div class="font-semibold text-gray-700">Company Registration Number:</div>
                                    <div>{{ companyInfo.company_registration_number }}</div>

                                    <hr class="col-span-2 my-4 border-gray-300" />

                                    <div class="font-semibold text-gray-700">Identity URL:</div>
                                    <div>
                                        <a
                                            v-if="companyInfo.identity_path"
                                            :href="companyInfo.identity_path"
                                            target="_blank"
                                            class="underline text-primary">
                                            View Identity
                                        </a>
                                        <span
                                            v-else
                                            class="text-gray-500"
                                            >Not uploaded</span
                                        >
                                    </div>

                                    <div class="mt-2 font-semibold text-gray-700">Residency URL:</div>
                                    <div>
                                        <a
                                            v-if="companyInfo.residency_path"
                                            :href="companyInfo.residency_path"
                                            target="_blank"
                                            class="underline text-primary">
                                            View Residency
                                        </a>
                                        <span
                                            v-else
                                            class="text-gray-500"
                                            >Not uploaded</span
                                        >
                                    </div>

                                    <div class="mt-2 font-semibold text-gray-700">Company Registration URL :</div>
                                    <div>
                                        <a
                                            v-if="companyInfo.company_reg_path"
                                            :href="companyInfo.company_reg_path"
                                            target="_blank"
                                            class="underline text-primary">
                                            View Company Registration
                                        </a>
                                        <span
                                            v-else
                                            class="text-gray-500"
                                            >Not uploaded</span
                                        >
                                    </div>

                                    <hr class="col-span-2 my-4 border-gray-300" />

                                    <div class="font-semibold text-gray-700">Agreement and Policy:</div>
                                    <div>
                                        <span
                                            :class="
                                                (companyInfo.agreement ?? 0)
                                                    ? 'bg-primary font-semibold px-2 py-1 rounded text-white'
                                                    : 'bg-bluemain font-semibold px-2 py-1 rounded text-white'
                                            ">
                                            {{ (companyInfo.agreement ?? 0) ? 'Accepted' : 'Declined' }}
                                        </span>
                                    </div>

                                    <hr class="col-span-2 my-4 border-gray-300" />
                                    <div class="font-semibold text-gray-700">Status:</div>
                                    <div>
                                        <span
                                            :class="
                                                (companyInfo.approved ?? 0)
                                                    ? 'bg-green-600 font-semibold px-2 py-1 rounded text-white'
                                                    : 'bg-yellow-600 font-semibold px-2 py-1 rounded text-white'
                                            ">
                                            {{ (companyInfo.approved ?? 0) ? 'Approved' : 'Pending' }}
                                        </span>
                                    </div>
                                    <hr
                                        class="col-span-2 my-4 border-gray-300"
                                        v-if="groupedRates.length > 0" />
                                </div>

                                <!-- Client Rates -->
                                <div
                                    class="pt-5"
                                    v-if="groupedRates.length > 0">
                                    <h2 class="text-xl font-semibold leading-tight text-gray-800">Client Rates</h2>

                                    <div
                                        v-for="(rates, type) in groupedRates"
                                        :key="type"
                                        class="mb-10">
                                        <h2 class="pt-10 text-lg">
                                            Package - {{ type.charAt(0).toUpperCase() + type.slice(1) }}
                                        </h2>
                                        <hr class="col-span-2 my-4 border-gray-300" />

                                        <div class="grid grid-cols-1 gap-4 mt-10 md:grid-cols-2 lg:grid-cols-3">
                                            <div
                                                v-for="(rate, i) in rates"
                                                :key="i"
                                                class="p-4 bg-white border border-gray-300 rounded-lg shadow-sm">
                                                <h3 class="mb-2 text-lg font-semibold">{{ rate.office_name }}</h3>
                                                <hr class="col-span-2 my-4 border-gray-300" />
                                                <div class="grid grid-cols-2 text-sm text-gray-700 gap-x-6">
                                                    <div><strong>Start Date:</strong></div>
                                                    <div>{{ rate.start_date }}</div>
                                                    <div><strong>End Date:</strong></div>
                                                    <div>{{ rate.end_date }}</div>
                                                    <hr class="col-span-2 my-4 border-gray-300" />
                                                    <div><strong>Price:</strong></div>
                                                    <div>R{{ rate.price }}</div>
                                                    <hr class="col-span-2 my-4 border-gray-300" />
                                                    <div><strong>Discount:</strong></div>
                                                    <div>
                                                        <span
                                                            :class="
                                                                (rate.discount_active ?? 0)
                                                                    ? 'bg-green-600 font-semibold px-2 py-1 rounded text-white'
                                                                    : 'bg-yellow-600 font-semibold px-2 py-1 rounded text-white'
                                                            ">
                                                            {{ (rate.discount_active ?? 0) ? 'Yes' : 'No' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sticky Footer -->
                            <div class="sticky bottom-0 z-10 px-6 py-4 bg-white border-t border-gray-200">
                                <div class="flex justify-between">
                                    <button
                                        v-show="companyInfo.approved === 0"
                                        @click="approveClient(companyInfo.id)"
                                        class="px-4 py-2 text-sm text-white bg-green-700 rounded hover:bg-bluemain/60">
                                        Approve
                                    </button>
                                    <button
                                        v-show="companyInfo.approved === 1"
                                        @click="deactiveClient(companyInfo.id)"
                                        class="px-4 py-2 text-sm text-white rounded bg-primary hover:bg-bluemain/60">
                                        Deactivate
                                    </button>
                                    <button
                                        @click="closeInfoModal"
                                        class="px-4 py-2 text-sm text-white rounded bg-bluemain hover:bg-bluemain/60">
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <GlobalNoteModal
                    :users="users"
                    :show="showNoteModal"
                    :onClose="() => (showNoteModal = false)" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
