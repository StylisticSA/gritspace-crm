<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const form = useForm({
    category_name: '',
    pricing_type: '',
    rate: '',
});

const props = defineProps({
    categories: Array,
});
</script>

<template>
    <Head title="Create an Office" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Service Levels</h2>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-4xl p-6 mx-auto space-y-6">
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-2xl font-medium text-black">Add Service Level</h3>

                        <Link
                            :href="route('admin.offices_rates')"
                            class="inline-block px-3 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
                            Back
                        </Link>
                    </div>

                    <form
                        @submit.prevent="form.post(route('admin.offices_rates.store'))"
                        class="space-y-6">
                        <!-- Categories -->
                        <div>
                            <label class="block text-lg font-medium text-gray-700">Category Name</label>
                            <select
                                v-model="form.category_name"
                                class="w-full px-3 py-2 border rounded">
                                <option value="">Select category</option>
                                <option
                                    v-for="loc in categories"
                                    :key="loc.id"
                                    :value="loc.name">
                                    {{ loc.name }}
                                </option>
                            </select>
                            <div
                                v-if="form.errors.category_name"
                                class="text-sm text-red-600">
                                {{ form.errors.category_name }}
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Office Name -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Level</label>
                                <select
                                    v-model="form.pricing_type"
                                    class="w-full px-3 py-2 border rounded">
                                    <option
                                        value=""
                                        disabled>
                                        Select a level
                                    </option>
                                    <option value="Standard">Standard</option>
                                    <option value="Premium">Premium</option>
                                </select>
                                <div
                                    v-if="form.errors.pricing_type"
                                    class="text-sm text-red-600">
                                    {{ form.errors.pricing_type }}
                                </div>
                            </div>

                            <!-- Office Type -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Price (Monthly)</label>
                                <input
                                    v-model="form.rate"
                                    type="number"
                                    step="1"
                                    min="0"
                                    class="w-full px-3 py-2 border rounded"
                                    placeholder="e.g. 2" />
                                <div
                                    v-if="form.errors.rate"
                                    class="text-sm text-red-600">
                                    {{ form.errors.rate }}
                                </div>
                            </div>
                        </div>
                        <div class="w-full pt-4 md:col-span-2">
                            <button
                                type="submit"
                                class="block w-full px-3 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60"
                                :disabled="form.processing">
                                Create Service Levels
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
