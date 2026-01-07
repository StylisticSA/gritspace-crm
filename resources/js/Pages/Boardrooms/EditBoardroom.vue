<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    locations: Array,
    amenities: Array,
    boardrooms: Object,
});

// Extract selected amenity IDs from the boardroom
const amenitiesSelected = props.boardrooms.amenities ? props.boardrooms.amenities.map(a => a.id) : [];

const form = useForm({
    boardroom_name: props.boardrooms.boardroom_name,
    location_id: props.boardrooms.location_id,
    seats: props.boardrooms.seats,
    hourly_price: props.boardrooms.hourly_price,
    daily_price: props.boardrooms.daily_price,
    amenities: amenitiesSelected,
});

const submit = () => {
    form.put(route('admin.boardrooms.update', props.boardrooms.id), {
        onSuccess: () => {
            router.visit(route('admin.boardrooms'));
        },
    });
};
</script>

<template>
    <Head title="Edit Boardroom" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Boardroom</h2>
        </template>

        <div class="p-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-4xl p-6 mx-auto space-y-6">
                    <!-- Header -->
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-2xl font-medium text-black">Edit Boardroom</h3>
                        <Link
                            :href="route('admin.boardrooms')"
                            class="inline-block px-3 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain">
                            Back
                        </Link>
                    </div>

                    <!-- Form -->
                    <form
                        @submit.prevent="submit"
                        class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Name -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Boardroom Name</label>
                                <input
                                    v-model="form.boardroom_name"
                                    type="text"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.boardroom_name"
                                    class="text-sm text-red-600">
                                    {{ form.errors.boardroom_name }}
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
                                        v-for="loc in props.locations"
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

                            <!-- Seats -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Number of Seats</label>
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

                            <!-- Hourly Price -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Hourly Price</label>
                                <input
                                    v-model="form.hourly_price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full px-3 py-2 border rounded"
                                    placeholder="e.g. 50.00" />
                                <div
                                    v-if="form.errors.hourly_price"
                                    class="text-sm text-red-600">
                                    {{ form.errors.hourly_price }}
                                </div>
                            </div>

                            <!-- Daily Price -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Daily Price</label>
                                <input
                                    v-model="form.daily_price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full px-3 py-2 border rounded"
                                    placeholder="e.g. 200.00" />
                                <div
                                    v-if="form.errors.daily_price"
                                    class="text-sm text-red-600">
                                    {{ form.errors.daily_price }}
                                </div>
                            </div>
                        </div>

                        <!-- Amenities -->
                        <div>
                            <label class="block mb-1 text-lg font-medium text-gray-700">Amenities</label>
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

                        <!-- Submit Button -->
                        <div class="w-full pt-2 md:col-span-2">
                            <button
                                type="submit"
                                class="block w-full px-3 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain"
                                :disabled="form.processing">
                                Update boardroom
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
