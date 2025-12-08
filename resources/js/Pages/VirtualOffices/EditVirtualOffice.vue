<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    virtualoffices: Object,
    locations: Array,
    amenities: Array,
});

const amenitiesSelected = props.virtualoffices.amenities ? props.virtualoffices.amenities.map(a => a.id) : [];

const form = useForm({
    virtualoffice_name: props.virtualoffices.virtualoffice_name,
    location_id: props.virtualoffices.location_id,

    price: props.virtualoffices.price,
    amenities: amenitiesSelected,
});

const submit = () => {
    form.put(route('admin.virtual-office.update', props.virtualoffices.id), {
        onSuccess: () => {
            router.visit(route('admin.virtual-offices'));
        },
    });
};
</script>

<template>
    <Head title="Edit Virtual Office" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Virtual Office</h2>
        </template>

        <div class="p-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-4xl p-6 mx-auto space-y-6">
                    <!-- Search Filter -->
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-2xl font-medium text-black">Edit Virtual Office</h3>

                        <Link
                            :href="route('admin.virtual-offices')"
                            class="inline-block px-4 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
                            Back
                        </Link>
                    </div>
                    <form
                        @submit.prevent="submit"
                        class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Name -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Virtual Office Name</label>
                                <input
                                    v-model="form.virtualoffice_name"
                                    type="text"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.virtualoffice_name"
                                    class="text-sm text-red-600">
                                    {{ form.errors.virtualoffice_name }}
                                </div>
                            </div>

                            <!-- Location -->
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

                            <!-- Price -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Price (Monthly)</label>
                                <input
                                    v-model="form.price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full px-3 py-2 border rounded"
                                    placeholder="e.g. 2000.00" />
                                <div
                                    v-if="form.errors.price"
                                    class="text-sm text-red-600">
                                    {{ form.errors.price }}
                                </div>
                            </div>
                        </div>

                        <!-- Amenities -->
                        <div class="md:col-span-2">
                            <label class="block mb-3 text-lg font-medium text-gray-700">Amenities</label>
                            <div class="grid grid-cols-3 gap-2">
                                <label
                                    v-for="amenity in props.amenities"
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
                        <!-- submit button -->
                        <div class="w-full pt-4 md:col-span-2">
                            <button
                                type="submit"
                                class="block w-full px-4 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60"
                                :disabled="form.processing">
                                Update Virtual Office
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
