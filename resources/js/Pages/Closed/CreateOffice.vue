<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { watch, computed } from 'vue';
import useStatusMessage from '../../Composables/useStatusMessage';

const form = useForm({
    office_name: '',
    category_id: '',
    location_id: '',
    seats: '',
    monthly_rate: '',
    daily_rate: '',
    pricing_id: '',
    amenities: [],
    pricing_type: [],
});

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();

const props = defineProps({
    locations: Array,
    categories: Array,
    pricings: Array,
    amenities: Array,
});

const isDedicatedDesk = computed(() => {
    const selected = props.categories.find(c => c.id === form.category_id);
    return selected?.name === 'Dedicated Desk';
});

watch(
    () => form.category_id,
    () => {
        if (!isDedicatedDesk.value) {
            form.pricing_type = [];
        }
    }
);

const submit = () => {
    form.post(route('admin.closedoffices.store'), {
        onSuccess: () => {
            message.value = 'Closed Office Saved Successfully.';
            status.value = 'success';

            setTimeout(() => {
                router.visit(route('admin.closedoffices'));
                router.reload({ preserveScroll: true });
            }, 2000);
        },
    });
};
</script>

<template>
    <Head title="Create an Office" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Closed Office</h2>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-4xl p-6 mx-auto space-y-6">
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-2xl font-medium text-black">Add Closed Office</h3>

                        <Link
                            :href="route('admin.closedoffices')"
                            class="inline-block px-4 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
                            Back
                        </Link>
                    </div>

                    <template v-if="showMessage">
                        <div :class="messageClass">
                            {{ messageText }}
                        </div>
                    </template>

                    <form
                        @submit.prevent="submit"
                        class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Office Name</label>
                                <input
                                    v-model="form.office_name"
                                    type="text"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.office_name"
                                    class="text-sm text-red-600">
                                    {{ form.errors.office_name }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-lg font-medium text-gray-700">Category</label>
                                <select
                                    v-model="form.category_id"
                                    class="w-full px-3 py-2 border rounded">
                                    <option value="">Select category</option>
                                    <option
                                        v-for="loc in categories"
                                        :key="loc.id"
                                        :value="loc.id">
                                        {{ loc.name }}
                                    </option>
                                </select>
                                <div
                                    v-if="form.errors.category_id"
                                    class="text-sm text-red-600">
                                    {{ form.errors.category_id }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-lg font-medium text-gray-700">Location</label>
                                <select
                                    v-model="form.location_id"
                                    class="w-full px-3 py-2 border rounded">
                                    <option value="">Select Location</option>
                                    <option
                                        v-for="loc in locations"
                                        :key="loc.id"
                                        :value="loc.id">
                                        {{ loc.name }}
                                    </option>
                                </select>
                                <div
                                    v-if="form.errors.location_id"
                                    class="text-sm text-red-600">
                                    {{ form.errors.location_id }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-lg font-medium text-gray-700">Seats</label>
                                <input
                                    v-model="form.seats"
                                    type="number"
                                    step="1"
                                    min="0"
                                    class="w-full px-3 py-2 border rounded"
                                    placeholder="e.g. 2" />
                                <div
                                    v-if="form.errors.seats"
                                    class="text-sm text-red-600">
                                    {{ form.errors.seats }}
                                </div>
                            </div>

                            <div v-if="!isDedicatedDesk">
                                <label class="block text-lg font-medium text-gray-700">Monthly Rate</label>
                                <input
                                    v-model="form.monthly_rate"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full px-3 py-2 border rounded"
                                    placeholder="e.g. 2000.00" />
                                <div
                                    v-if="form.errors.monthly_rate"
                                    class="text-sm text-red-600">
                                    {{ form.errors.monthly_rate }}
                                </div>
                            </div>

                            <div v-if="isDedicatedDesk">
                                <label class="block text-lg font-medium text-gray-700">Pricing Type</label>
                                <div class="flex flex-col space-y-2">
                                    <div
                                        v-for="pricing in pricings"
                                        :key="pricing.id"
                                        class="flex items-center">
                                        <input
                                            type="checkbox"
                                            v-model="form.pricing_type"
                                            :value="pricing.rate"
                                            :id="pricing.id"
                                            class="border-gray-300 rounded shadow-sm text-primary focus:ring-bluemain/60 form-checkbox" />
                                        <label
                                            :for="pricing.id"
                                            class="ml-2 text-sm">
                                            {{ pricing.category_name }} - {{ pricing.pricing_type }} -
                                            {{
                                                pricing.rate
                                                    ? `R ${Number(pricing.rate).toLocaleString('en-ZA', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
                                                    : 'None'
                                            }}
                                        </label>
                                    </div>
                                </div>
                                <div
                                    v-if="form.errors.pricing_type"
                                    class="text-sm text-red-600">
                                    {{ form.errors.pricing_type }}
                                </div>
                            </div>

                            <div v-if="!isDedicatedDesk">
                                <label class="block text-lg font-medium text-gray-700">Daily Rate</label>
                                <input
                                    v-model="form.daily_rate"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full px-3 py-2 border rounded"
                                    placeholder="e.g. 250.00" />
                                <div
                                    v-if="form.errors.daily_rate"
                                    class="text-sm text-red-600">
                                    {{ form.errors.daily_rate }}
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-lg font-medium text-gray-700">Amenities</label>
                            <div class="grid w-full grid-cols-3 gap-2 mt-3">
                                <label
                                    v-for="amenity in amenities"
                                    :key="amenity.id"
                                    class="flex items-center space-x-2">
                                    <input
                                        type="checkbox"
                                        :value="amenity.id"
                                        v-model="form.amenities"
                                        class="border-gray-300 rounded shadow-sm text-primary focus:ring-bluemain/60 form-checkbox" />
                                    <span class="text-sm">{{ amenity.amenity_name }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="w-full pt-4 md:col-span-2">
                            <button
                                type="submit"
                                class="block w-full px-4 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60"
                                :disabled="form.processing">
                                Add Closed Office
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
