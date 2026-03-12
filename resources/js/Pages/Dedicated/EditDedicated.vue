<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { watch, computed } from 'vue';
import useStatusMessage from '../../Composables/useStatusMessage';

const props = defineProps({
    office: Object,
    locations: Array,
    pricings: Array,
    amenities: Array,
    categories: Array,
});

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();
const amenitiesSelected = props.office.amenities ? props.office.amenities.map(a => a.id) : [];

const form = useForm({
    office_name: props.office.office_name,
    category_id: props.office.category_id,
    seats: props.office.seats,
    monthly_rate: props.office.monthly_rate,
    daily_rate: props.office.daily_rate,
    location_id: props.office.location_id,
    free_boardroom_hours: props.office.free_boardroom_hours,
    pricing_type: [props.office.price_premium, props.office.price_standard],
    amenities: amenitiesSelected,
});

// Set default category if only one exists
if (props.categories.length === 1 && !form.category_id) {
    form.category_id = props.categories[0].id;
}

const isDedicatedDesk = computed(() => {
    const selected = props.categories.find(c => c.id === form.category_id);
    return selected?.name?.toLowerCase().includes('dedicated desk');
});

watch(
    () => form.category_id,
    () => {
        if (!isDedicatedDesk.value) {
            form.pricing_type = [];
        }
    }
);

const cleanPricingType = () => {
    form.pricing_type = form.pricing_type.filter(value => value !== null);
};

const submit = () => {
    cleanPricingType();
    form.put(route('admin.dedicateddesk.update', props.office.id), {
        onSuccess: () => {
            message.value = 'Dedicated Desk Updated Successfully.';
            status.value = 'success';

            setTimeout(() => {
                router.reload({ preserveScroll: true });
                router.visit(route('admin.dedicateddesk'));
            }, 2000);
        },
    });
};
</script>

<template>
    <Head title="Edit Office" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Dedicated Desks</h2>
        </template>

        <div class="p-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-4xl p-6 mx-auto space-y-6">
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-2xl font-medium text-black">Edit Dedicated Desk</h3>

                        <Link
                            :href="route('admin.dedicateddesk')"
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
                                <label class="block text-lg font-medium text-gray-700">Categories</label>
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
                                <label class="block text-lg font-medium text-gray-700">Premium Monthly Rate</label>
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

                            <div>
                                <label class="block text-lg font-medium text-gray-700">Standard Monthly Rate</label>
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

                            <div>
                                <label class="block text-lg font-medium text-gray-700">Free Discounts (%)</label>
                                <input
                                    v-model="form.free_boardroom_hours"
                                    type="number"
                                    step="1"
                                    min="0"
                                    class="w-full px-3 py-2 border rounded"
                                    placeholder="0 %" />
                                <div
                                    v-if="form.errors.free_boardroom_hours"
                                    class="text-sm text-red-600">
                                    {{ form.errors.free_boardroom_hours }}
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block mb-1 text-lg font-medium text-gray-700">Amenities</label>
                            <div class="grid grid-cols-3 gap-2">
                                <label
                                    v-for="amenity in amenities"
                                    :key="amenity.id"
                                    class="flex items-center space-x-2">
                                    <input
                                        type="checkbox"
                                        :value="Number(amenity.id)"
                                        v-model="form.amenities"
                                        class="border-gray-300 rounded shadow-sm text-primary focus:ring-bluemain/60 form-checkbox" />
                                    <span class="text-sm">{{ amenity.amenity_name }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="w-full pt-2 md:col-span-2">
                            <button
                                type="submit"
                                class="block w-full px-4 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60"
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
