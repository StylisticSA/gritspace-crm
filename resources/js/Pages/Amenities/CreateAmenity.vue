<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const form = useForm({
    amenity_name: '',
    description: '',
    price: '',
});
</script>

<template>
    <Head title="Create an Office" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Amenities</h2>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-4xl p-6 mx-auto space-y-6">
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-2xl font-medium text-black">Add Amenity</h3>

                        <Link
                            :href="route('admin.amenities')"
                            class="inline-block px-3 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
                            Back
                        </Link>
                    </div>

                    <form
                        @submit.prevent="form.post(route('admin.amenities.store'))"
                        class="space-y-6">
                        <div class="grid grid-cols-1 gap-6">
                            <!-- Office Name -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Amenity Name</label>
                                <input
                                    v-model="form.amenity_name"
                                    type="text"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.amenity_name"
                                    class="text-sm text-red-600">
                                    {{ form.errors.amenity_name }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-lg font-medium text-gray-700">Description</label>
                                <input
                                    v-model="form.description"
                                    type="text"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.description"
                                    class="text-sm text-red-600">
                                    {{ form.errors.description }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-lg font-medium text-gray-700">Price</label>
                                <input
                                    v-model.number="form.price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full px-3 py-2 border rounded"
                                    placeholder="e.g. 15" />
                                <div
                                    v-if="form.errors.price"
                                    class="text-sm text-red-600">
                                    {{ form.errors.price }}
                                </div>
                            </div>
                        </div>
                        <div class="w-full pt-4 md:col-span-2">
                            <button
                                type="submit"
                                class="block w-full px-3 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60"
                                :disabled="form.processing">
                                Add Amenity
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
