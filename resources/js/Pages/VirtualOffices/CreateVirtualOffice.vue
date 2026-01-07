<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    location_id: '',
    virtualoffice_name: '',
    //address: '',
    //discount: '',
    price: '',
    duration: '',
    amenities: [],
});

const props = defineProps({
    locations: Array,
    amenities: Array,
    can: Object,
});
</script>

<template>
    <Head title="Add an Virtual Office" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Virtual Offices</h2>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-4xl p-6 mx-auto space-y-6">
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-2xl font-medium text-black">Add Virtual Office</h3>

                        <Link
                            :href="route('admin.virtual-offices')"
                            class="inline-block px-4 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain">
                            Back
                        </Link>
                    </div>

                    <form
                        @submit.prevent="form.post(route('admin.virtual-office.store'))"
                        class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Name -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700"
                                    >Virtual Office Service Level</label
                                >
                                <select
                                    v-model="form.virtualoffice_name"
                                    class="w-full px-3 py-2 border rounded">
                                    <option
                                        disabled
                                        value="">
                                        Select type
                                    </option>
                                    <option value="Premium">Premium</option>
                                    <option value="Standard">Standard</option>
                                </select>
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

                            <!-- Discount -->
                            <!-- <div>
                                <label class="block text-lg font-medium text-gray-700">Discount</label>
                                <input
                                    v-model="form.discount"
                                    type="number"
                                    step="1"
                                    min="0"
                                    class="w-full px-3 py-2 border rounded"
                                    placeholder="e.g. 2" />
                                <div
                                    v-if="form.errors.discount"
                                    class="text-sm text-red-600">
                                    {{ form.errors.discount }}
                                </div>
                            </div> -->

                            <!-- Address -->
                            <!-- <div>
                                <label class="block text-lg font-medium text-gray-700">Address</label>
                                <input
                                    v-model="form.address"
                                    type="text"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.address"
                                    class="text-sm text-red-600">
                                    {{ form.errors.address }}
                                </div>
                            </div> -->
                        </div>

                        <!-- Amenities -->
                        <div>
                            <label class="block mb-3 text-lg font-medium text-gray-700">Amenities</label>
                            <div class="grid grid-cols-3 gap-2">
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
                                Add Virtual Office
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
