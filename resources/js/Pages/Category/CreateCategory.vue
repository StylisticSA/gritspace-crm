<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    offers_level: '',
});
</script>

<template>
    <Head title="Create Category" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Categories</h2>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-4xl p-6 mx-auto space-y-6">
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-2xl font-medium text-black">Add Category</h3>

                        <Link
                            :href="route('admin.categories')"
                            class="inline-block px-3 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
                            Back
                        </Link>
                    </div>

                    <form
                        @submit.prevent="form.post(route('admin.categories.store'))"
                        class="space-y-6">
                        <div class="grid grid-cols-1 gap-6">
                            <!-- Office Name -->
                            <div>
                                <label class="text-lg font-medium text-gray-700">Category Name</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.name"
                                    class="text-sm text-red-600">
                                    {{ form.errors.name }}
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="inline-flex items-center space-x-2">
                                <input
                                    type="checkbox"
                                    v-model="form.offers_level"
                                    class="w-5 h-5 text-primary form-checkbox" />
                                <span class="text-lg text-gray-700"
                                    >Offers different service levels (Standard/Premium)</span
                                >
                            </label>
                            <div
                                v-if="form.errors.offers_level"
                                class="text-sm text-red-600">
                                {{ form.errors.offers_level }}
                            </div>
                        </div>
                        <div class="w-full pt-4 md:col-span-2">
                            <button
                                type="submit"
                                class="block w-full px-3 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60"
                                :disabled="form.processing">
                                Create Category
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
