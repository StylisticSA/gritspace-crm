<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AgreementModal from '@/Components/Modals/AgreementModal.vue';

const props = defineProps({
    clients: Object,
    agreement: Object,
    locations: Object,
});

const groupedRates = computed(() => {
    if (!props.clients?.rates) return {};
    return props.clients.rates.reduce((groups, rate) => {
        const type = rate.type || 'unknown';
        if (!groups[type]) groups[type] = [];
        groups[type].push(rate);
        return groups;
    }, {});
});

const showAgreementModal = ref(false);
</script>

<template>
    <Head title="Company Details" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between space-x-5">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Company Details</h2>
                <button
                    v-if="!agreement || agreement.status !== 'approved'"
                    @click="showAgreementModal = true"
                    class="px-2 py-2 text-lg text-white rounded bg-bluemain hover:bluemain/60">
                    Upload Agreement
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-5xl px-4 mx-auto sm:px-6 lg:px-8">
                <!-- Navigation Buttons -->
                <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-start">
                    <Link
                        v-if="!clients"
                        :href="route('companydetail.create')"
                        class="w-full px-4 py-2 text-lg font-medium text-center text-white rounded sm:w-auto bg-primary hover:bg-bluemain">
                        + Company Details
                    </Link>
                </div>

                <!-- Two-column layout -->
                <div class="grid grid-cols-1 gap-3 md:grid-cols-1">
                    <!-- Left column: Data list -->
                    <div class="p-4 bg-white shadow sm:rounded-lg sm:p-8">
                        <h3 class="mb-10 text-2xl text-bold">Company Information</h3>

                        <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                            <div class="font-semibold text-gray-700">Location:</div>
                            <div>{{ clients.location?.name ?? 'N/A' }}</div>

                            <hr class="col-span-2 my-2 border-gray-300" />

                            <div class="font-semibold text-gray-700">First Name:</div>
                            <div>{{ clients.name }}</div>

                            <div class="font-semibold text-gray-700">Surname:</div>
                            <div>{{ clients.surname }}</div>

                            <hr class="col-span-2 my-2 border-gray-300" />

                            <div class="font-semibold text-gray-700">Cell Number:</div>
                            <div>{{ clients.cell_number }}</div>

                            <div class="font-semibold text-gray-700">Email Address:</div>
                            <div class="truncate max-w-full text-gray-900">{{ clients.email_address }}</div>

                            <hr class="col-span-2 my-2 border-gray-300" />

                            <div class="font-semibold text-gray-700">Company Name:</div>
                            <div>{{ clients.company_name }}</div>

                            <div class="font-semibold text-gray-700">Company Registration Number:</div>
                            <div>{{ clients.company_registration_number }}</div>

                            <hr class="col-span-2 my-4 border-gray-300" />

                            <div class="font-semibold text-gray-700">Identity URL:</div>
                            <div>
                                <a
                                    v-if="clients.identity_path"
                                    :href="clients.identity_path"
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
                                    v-if="clients.residency_path"
                                    :href="clients.residency_path"
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

                            <div class="mt-2 font-semibold text-gray-700">Company Registration URL:</div>
                            <div>
                                <a
                                    v-if="clients.company_reg_path"
                                    :href="clients.company_reg_path"
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
                                        agreement?.status === 'approved'
                                            ? 'bg-green-600 font-semibold px-2 py-1 rounded text-white'
                                            : 'bg-yellow-600 font-semibold px-2 py-1 rounded text-white'
                                    ">
                                    {{ agreement?.status === 'approved' ? 'Approved' : 'Pending' }}
                                </span>
                            </div>

                            <div class="mt-2 font-semibold text-gray-700">Agreement File URL:</div>
                            <div class="mt-2">
                                <a
                                    v-if="agreement && agreement.agreement"
                                    :href="agreement.agreement"
                                    target="_blank"
                                    class="underline text-primary">
                                    View Agreement
                                </a>
                                <span
                                    v-else
                                    class="text-gray-500"
                                    >Not uploaded</span
                                >
                            </div>

                            <hr class="col-span-2 my-4 border-gray-300" />

                            <div class="font-semibold text-gray-700">Status:</div>
                            <div class="mb-3">
                                <span
                                    :class="
                                        (clients.approved ?? 0)
                                            ? 'bg-green-600 font-semibold px-2 py-1 rounded text-white'
                                            : 'bg-yellow-600 font-semibold px-2 py-1 rounded text-white'
                                    ">
                                    {{ (clients.approved ?? 0) ? 'Approved' : 'Pending' }}
                                </span>
                            </div>
                        </div>
                        <hr class="col-span-2 my-4 border-gray-300" />
                        <div class="w-full pt-5 mt-5 md:col-span-2">
                            <Link
                                v-if="!clients.approved"
                                :href="route('companydetail.edit', clients.id)"
                                class="block w-full px-4 py-2 text-lg font-medium text-center text-white rounded bg-bluemain hover:bg-bluemain/60">
                                Edit Company Details
                            </Link>
                        </div>
                    </div>

                    <!-- Right column: Existing content / form or other info -->
                    <div
                        class="p-4 bg-white shadow sm:rounded-lg sm:p-8"
                        v-if="!props.clients?.rates">
                        <h3 class="mb-10 text-2xl text-bold">Client Rates</h3>

                        <div
                            v-for="(rates, type) in groupedRates"
                            :key="type"
                            class="mb-10">
                            <h2 class="mb-2 text-xl font-bold capitalize">{{ type }} - Offices</h2>

                            <table class="min-w-full border border-gray-300">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2 text-left border">Office Name</th>
                                        <th class="px-4 py-2 text-left border">Start Date</th>
                                        <th class="px-4 py-2 text-left border">End Date</th>
                                        <th class="px-4 py-2 text-left border">Price</th>

                                        <th class="px-4 py-2 text-left border">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(rate, i) in rates"
                                        :key="i">
                                        <td class="px-4 py-2 border">{{ rate.office_name }}</td>
                                        <td class="px-4 py-2 border">{{ rate.start_date }}</td>
                                        <td class="px-4 py-2 border">{{ rate.end_date }}</td>
                                        <td class="px-4 py-2 border">{{ rate.price }}</td>

                                        <td class="px-4 py-2 border">
                                            <button
                                                v-if="!clients.approved"
                                                @click="$inertia.visit(route('clientrates.editCompany', rate.id))"
                                                class="px-2 py-1 text-sm text-white rounded bg-bluemain hover:bg-bluemain/60">
                                                Edit
                                            </button>
                                            <span :class="{ hidden: clients.approved === 0 ? 'hidden' : '' }">-</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <AgreementModal
                :location_id="clients.location?.id"
                :locations="locations"
                :agreement="agreement"
                :show="showAgreementModal"
                :can="can"
                :onClose="() => (showAgreementModal = false)" />
        </div>
    </AuthenticatedLayout>
</template>
