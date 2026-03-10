<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import useStatusMessage from '../../Composables/useStatusMessage';
import { reactive, computed } from 'vue';

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();

const props = defineProps({
    locations: Array,
    categories: Array,
});

const form = useForm({
    location_id: '',
    category_id: '',
    name: '',
    package: '',
    discount: '',
});

const packageOptionsMap = {
    'Closed Office': ['Monthly', 'Daily'],
    'Closed Offices': ['Monthly', 'Daily'],
    'Dedicated Desk': ['Premium', 'Standard'],
    'Dedicated Desks': ['Premium', 'Standard'],
    'Hot Desk': ['Daily'],
    'Hot Desks': ['Daily'],
    'Virtual Office': ['Premium', 'Standard'],
    'Virtual Offices': ['Premium', 'Standard'],
};

// compute available packages based on selected category name
const availablePackages = computed(() => {
    const selectedCategory = props.categories.find(c => c.id === form.category_id);

    console.log('s', selectedCategory);

    if (!selectedCategory) return [];
    return packageOptionsMap[selectedCategory.name] || [];
});

const submit = () => {
    form.post(route('admin.discounts.store'), {
        onSuccess: () => {
            message.value = 'Discounts has been Saved Successfully.';
            status.value = 'success';

            setTimeout(() => {
                router.reload({ preserveScroll: true });
                router.visit(route('admin.discounts.index'));
            }, 2000);
        },
        onError: errors => {
            message.value = Object.values(errors).join('\n');
            status.value = 'deleted';
        },
    });
};
</script>

<template>
    <Head title="Create Discount" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Discounts</h2>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-4xl p-6 mx-auto space-y-6">
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-2xl font-medium text-black">Add Discount</h3>

                        <Link
                            :href="route('admin.discounts.index')"
                            class="inline-block px-3 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
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
                        <div class="grid grid-col-1 sm:grid-cols-2 gap-6">
                            <!-- Location -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Locations</label>
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

                            <!-- Category -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Categories</label>
                                <select
                                    v-model="form.category_id"
                                    class="w-full px-3 py-2 border rounded">
                                    <option value="">Select Category</option>
                                    <option
                                        v-for="cate in categories"
                                        :key="cate.id"
                                        :value="cate.id">
                                        {{ cate.name }}
                                    </option>
                                </select>
                                <div
                                    v-if="form.errors.category_id"
                                    class="text-sm text-red-600">
                                    {{ form.errors.category_id }}
                                </div>
                            </div>

                            <!-- Package -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Office Package</label>
                                <select
                                    v-model="form.package"
                                    class="w-full max-h-40 overflow-y-auto border rounded p-2">
                                    <option value="">Select Package</option>
                                    <option
                                        v-for="pkg in availablePackages"
                                        :key="pkg"
                                        :value="pkg">
                                        {{ pkg }}
                                    </option>
                                </select>
                                <div
                                    v-if="form.errors.package"
                                    class="text-sm text-red-600">
                                    {{ form.errors.package }}
                                </div>
                            </div>

                            <!-- Discount -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Discount (%)</label>
                                <input
                                    v-model="form.discount"
                                    type="number"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.discount"
                                    class="text-sm text-red-600">
                                    {{ form.errors.discount }}
                                </div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="w-full pt-4 md:col-span-2">
                            <button
                                type="submit"
                                class="block w-full px-3 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60"
                                :disabled="form.processing">
                                Add Discount
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
