<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    office: Object,
    locations: Array,
    pricings: Array,
    amenities: Array,
    categories: Array,
});

const amenitiesSelected = props.office.amenities ? props.office.amenities.map(a => a.id) : [];

const form = useForm({
    office_name: props.office.office_name,
    category_id: props.office.category_id,
    seats: props.office.seats,
    monthly_rate: props.office.monthly_rate,
    daily_rate: props.office.daily_rate,
    location_id: props.office.location_id,
    pricing_type: [props.office.price_premium, props.office.price_standard],
    amenities: amenitiesSelected,
});

const isDedicatedDesk = computed(() => {
    const selected = props.categories.find(c => c.id === form.category_id);
    return selected?.name === 'Dedicated Desk';
});

const cleanPricingType = () => {
    form.pricing_type = form.pricing_type.filter(value => value !== null);
};

const submit = () => {
    cleanPricingType();
    form.put(route('admin.offices.update', props.office.id), {
        onSuccess: () => {
            router.visit(route('admin.offices'));
        },
    });
};
</script>

<template>
    <Head title="Edit Office" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Offices</h2>
        </template>

        <div class="p-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-4xl p-6 mx-auto space-y-6">
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-2xl font-medium text-black">Edit Office</h3>

                        <Link
                            :href="route('admin.offices')"
                            class="inline-block px-4 py-1 text-sm font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
                            Back
                        </Link>
                    </div>

                    <form
                        @submit.prevent="submit"
                        class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Office Name</label>
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
                                <label class="block text-sm font-medium text-gray-700">Categories</label>
                                <select
                                    v-model="form.category_id"
                                    class="w-full px-3 py-2 border rounded">
                                    <option value="">Select category</option>
                                    <option
                                        v-for="cat in categories"
                                        :key="cat.id"
                                        :value="cat.id">
                                        {{ cat.name }}
                                    </option>
                                </select>
                                <div
                                    v-if="form.errors.category_id"
                                    class="text-sm text-red-600">
                                    {{ form.errors.category_id }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Location</label>
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
                                <label class="block text-sm font-medium text-gray-700">Seats of the Office</label>
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
                                <label class="block text-sm font-medium text-gray-700">Monthly Rate</label>
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

                            <div v-if="!isDedicatedDesk">
                                <label class="block text-sm font-medium text-gray-700">Daily Rate</label>
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

                        <div v-if="isDedicatedDesk">
                            <label class="block text-sm font-medium text-gray-700">Pricing Type</label>
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
                                        class="form-checkbox" />
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

                        <div class="md:col-span-2">
                            <label class="block mb-1 text-sm font-medium text-gray-700">Amenities</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label
                                    v-for="amenity in amenities"
                                    :key="amenity.id"
                                    class="flex items-center space-x-2">
                                    <input
                                        type="checkbox"
                                        :value="Number(amenity.id)"
                                        v-model="form.amenities"
                                        class="border-gray-300 rounded" />
                                    <span class="text-sm">{{ amenity.amenity_name }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="w-full pt-2 md:col-span-2">
                            <button
                                type="submit"
                                class="block w-full px-4 py-1 text-sm font-medium text-white rounded bg-bluemain hover:bg-bluemain/60"
                                :disabled="form.processing">
                                Update Office
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
