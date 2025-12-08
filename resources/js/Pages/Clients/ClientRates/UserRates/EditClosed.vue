<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import { ref } from 'vue';
import StatusFeedback from '@/Components/StatusFeedback.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    clientRate: Object,
    closedoffices: Array,
    boardrooms: Array,
    virtuals: Array,
    hotdesks: Array,
    dedicated: Array,
    can: Object,
});

const successMessage = ref(null);
const bookingConflict = ref(null);

const form = useForm({
    closed_office_id: props.clientRate?.space_id ?? '',
    office_name: props.clientRate.office_name ?? '',
    start_date: props.clientRate?.start_date ?? '',
    end_date: props.clientRate?.end_date ?? '',
    price: props.clientRate?.price ?? '',
    // discount_active: props.clientRate?.discount_active ?? 0,
});

const updateClosedOfficeName = () => {
    const selected = props.closedoffices.find(o => o.id === form.closed_office_id);
    form.office_name = selected?.office_name ?? '';
};

const submit = () => {
    form.put(route('clientrates.updateCompany', props.clientRate.id), {
        preserveScroll: true,
        onError: errors => {
            bookingConflict.value = errors.booking_conflict ?? null;
        },
        onSuccess: () => {
            successMessage.value = 'Client Rate updated successfully!';
            bookingConflict.value = null;
        },
    });
};
</script>

<template>
    <Head title="Edit Discounted Rate" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-semibold leading-tight text-gray-800">Edit Discounted Rate</h2>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-4xl p-6 mx-auto space-y-6">
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-xl text-black">Edit Discounted Rate</h3>
                        <div class="space-x-2">
                            <Link
                                v-if="!can['manage settings']"
                                :href="route('companydetail.index')"
                                class="inline-block px-2 py-2 text-lg text-white rounded bg-bluemain hover:bg-bluemain/60">
                                Back
                            </Link>
                            <Link
                                v-if="can['manage settings']"
                                :href="route('admin.clientrates.index')"
                                class="inline-block px-2 py-2 text-lg text-white rounded bg-bluemain hover:bg-bluemain/60">
                                Back
                            </Link>
                        </div>
                    </div>

                    <StatusFeedback
                        :conflict="bookingConflict"
                        :success="successMessage" />

                    <form
                        @submit.prevent="submit"
                        class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Offices -->
                            <div
                                class="col-span-2 mb-5"
                                v-if="props.clientRate.type === 'closed'">
                                <label class="block text-lg text-gray-700">Closed Office</label>
                                <select
                                    v-model="form.closed_office_id"
                                    @change="updateClosedOfficeName"
                                    class="w-full px-3 py-2 border rounded">
                                    <option value="">Select Closed</option>
                                    <option
                                        v-for="item in closedoffices"
                                        :key="item.id"
                                        :value="item.id">
                                        {{ item.office_name }}
                                    </option>
                                </select>

                                <input
                                    type="hidden"
                                    v-model="form.office_name" />

                                <div
                                    v-if="form.errors?.closed_office_id"
                                    class="text-sm text-red-600">
                                    {{ form.errors.closed_office_id }}
                                </div>
                            </div>

                            <div
                                class="col-span-2 mb-5"
                                v-if="props.clientRate.type === 'dedicated'">
                                <label class="block text-lg text-gray-700">Dedicated Desks</label>
                                <select
                                    v-model="form.closed_office_id"
                                    class="w-full px-3 py-2 border rounded">
                                    <option value="">Select Closed</option>
                                    <option
                                        v-for="item in dedicated"
                                        :key="item.id"
                                        :value="item.id">
                                        {{ item.office_name }}
                                    </option>
                                </select>

                                <div
                                    v-if="form.errors?.closed_office_id"
                                    class="text-sm text-red-600">
                                    {{ form.errors.closed_office_id }}
                                </div>
                            </div>

                            <div
                                class="col-span-2 mb-5"
                                v-if="props.clientRate.type === 'virtual'">
                                <label class="block text-lg text-gray-700">Virtuals</label>
                                <select
                                    v-model="form.closed_office_id"
                                    class="w-full px-3 py-2 border rounded">
                                    <option value="">Select Closed</option>
                                    <option
                                        v-for="item in virtuals"
                                        :key="item.id"
                                        :value="item.id">
                                        {{ item.virtualoffice_name }}
                                    </option>
                                </select>

                                <div
                                    v-if="form.errors?.closed_office_id"
                                    class="text-sm text-red-600">
                                    {{ form.errors.closed_office_id }}
                                </div>
                            </div>

                            <div
                                class="col-span-2 mb-5"
                                v-if="props.clientRate.type === 'boardroom'">
                                <label class="block text-lg text-gray-700">Boardrooms</label>
                                <select
                                    v-model="form.closed_office_id"
                                    class="w-full px-3 py-2 border rounded">
                                    <option value="">Select Closed</option>
                                    <option
                                        v-for="item in boardrooms"
                                        :key="item.id"
                                        :value="item.id">
                                        {{ item.boardroom_name }}
                                    </option>
                                </select>

                                <div
                                    v-if="form.errors?.closed_office_id"
                                    class="text-sm text-red-600">
                                    {{ form.errors.closed_office_id }}
                                </div>
                            </div>

                            <div
                                class="col-span-2 mb-5"
                                v-if="props.clientRate.type === 'hotdesk'">
                                <label class="block text-lg text-gray-700">Hot Desks</label>
                                <select
                                    v-model="form.closed_office_id"
                                    class="w-full px-3 py-2 border rounded">
                                    <option value="">Select Closed</option>
                                    <option
                                        v-for="item in hotdesks"
                                        :key="item.id"
                                        :value="item.id">
                                        {{ item.help_desk_name }}
                                    </option>
                                </select>

                                <div
                                    v-if="form.errors?.closed_office_id"
                                    class="text-sm text-red-600">
                                    {{ form.errors.closed_office_id }}
                                </div>
                            </div>

                            <!-- Start Date -->
                            <div class="mb-5">
                                <label class="block text-lg text-gray-700">Start Date</label>
                                <input
                                    v-model="form.start_date"
                                    type="date"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors?.start_date"
                                    class="text-sm text-red-600">
                                    {{ form.errors.start_date }}
                                </div>
                            </div>

                            <!-- End Date -->
                            <div class="mb-5">
                                <label class="block text-lg text-gray-700">End Date</label>
                                <input
                                    v-model="form.end_date"
                                    type="date"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors?.end_date"
                                    class="text-sm text-red-600">
                                    {{ form.errors.end_date }}
                                </div>
                            </div>

                            <!-- Price -->
                            <div class="mb-5">
                                <label class="block text-lg text-gray-700">Price</label>
                                <input
                                    v-model="form.price"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors?.discount_price"
                                    class="text-sm text-red-600">
                                    {{ form.errors.discount_price }}
                                </div>
                            </div>

                            <!-- Discounted -->
                            <!-- <div class="mx-auto mb-5">
                                <label class="block mb-2 text-lg text-gray-700">Discounted</label>
                                <div class="flex items-center gap-6">
                                    <label class="inline-flex items-center">
                                        <input
                                            v-model="form.discount_active"
                                            type="radio"
                                            value="1"
                                            class="mr-2 border-primary text-primary" />
                                        Yes
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input
                                            v-model="form.discount_active"
                                            type="radio"
                                            value="0"
                                            class="mr-2 border-primary" />
                                        No
                                    </label>
                                </div>
                            </div> -->
                        </div>

                        <div class="w-full pt-4 md:col-span-2">
                            <button
                                type="submit"
                                class="block w-full px-3 py-2 text-lg text-white rounded bg-bluemain hover:bg-bluemain/60"
                                :disabled="form.processing">
                                Edit Client Rate
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
