<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    locations: Object,
});

const form = useForm({
    location_id: '',
    name: '',
    price: '',
});

const names = ref([
    { id: 2, name: 'Black' },
    { id: 3, name: 'Coffee' },
    { id: 1, name: 'Color' },
]);
</script>

<template>
    <Head title="Create a Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Create a Setting</h2>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-4xl p-6 mx-auto space-y-6">
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-2xl font-medium text-black">Add a Setting</h3>

                        <Link
                            :href="route('admin.extra.index')"
                            class="inline-block px-3 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
                            Back
                        </Link>
                    </div>

                    <form
                        @submit.prevent="form.post(route('admin.extra.store'))"
                        class="space-y-6">
                        <div class="grid grid-cols-1 gap-6">
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
                            <!-- Office Name -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Name</label>
                                <select
                                    v-model="form.name"
                                    class="w-full px-3 py-2 border rounded">
                                    <option value="">Select Name</option>
                                    <option
                                        v-for="name in names"
                                        :key="name.id"
                                        :value="name.name">
                                        {{ name.name }}
                                    </option>
                                </select>
                                <div
                                    v-if="form.errors.name"
                                    class="text-sm text-red-600">
                                    {{ form.errors.name }}
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
                                Add Setting
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
