<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    coffee: Object,
    users: Object,
    locations: Object,
    can: Object,
});

const form = useForm({
    user_id: props.coffee.user_id,
    location_id: props.coffee.location_id ?? null,
    type: 'coffee',
    amount: props.coffee.amount,
    date: props.coffee.date,
});

const successMessage = ref(null);

const submit = () => {
    form.put(route('admin.coffee.update', props.coffee.id), {
        onSuccess: () => {
            successMessage.value = 'Updated successfully!';

            setTimeout(() => {
                successMessage.value = null;
                router.visit(route('admin.coffee.index'));
            }, 2000);
        },
    });
};
</script>

<template>
    <Head title="Edit Coffee" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Coffee</h2>
        </template>

        <div class="p-2 py-10">
            <div class="max-w-5xl mx-auto bg-white sm:px-6 lg:px-10">
                <div class="max-w-4xl p-8 mx-auto space-y-6">
                    <!-- Search Filter -->
                    <div class="flex flex-col gap-3 mb-10 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-2xl font-medium text-black">Edit Coffee</h3>

                        <Link
                            :href="route('admin.coffee.index')"
                            class="inline-block px-2 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
                            Back
                        </Link>
                    </div>

                    <form
                        @submit.prevent="submit"
                        class="mt-10 space-y-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label class="block font-medium text-md">User</label>
                                <select
                                    v-model="form.user_id"
                                    class="w-full px-3 py-2 border rounded">
                                    <option value="">Select User</option>
                                    <option
                                        v-for="user in users"
                                        :key="user.id"
                                        :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                                <div
                                    v-if="form.errors.user_id"
                                    class="text-sm text-red-600">
                                    {{ form.errors.user_id }}
                                </div>
                            </div>

                            <div>
                                <label class="block font-medium text-md">Location</label>
                                <select
                                    v-model="form.user_id"
                                    class="w-full px-3 py-2 border rounded">
                                    <option value="">Select Locations</option>
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
                            <!-- Color Column -->
                            <div>
                                <label
                                    for="color_amount"
                                    class="block mb-2 text-gray-900 text-md"
                                    >Amount:</label
                                >
                                <input
                                    type="number"
                                    id="amount"
                                    v-model="form.amount"
                                    class="w-full px-3 py-2 border border-gray-300 rounded bg-gray-50" />
                                <div
                                    v-if="form.errors.amount"
                                    class="text-sm text-red-600">
                                    {{ form.errors.amount }}
                                </div>
                            </div>

                            <div>
                                <label
                                    for="date"
                                    class="block mb-2 text-gray-900 text-md"
                                    >Date</label
                                >
                                <input
                                    type="date"
                                    id="date"
                                    v-model="form.date"
                                    class="w-full px-3 py-2 border border-gray-300 rounded bg-gray-50" />
                                <div
                                    v-if="form.errors.date"
                                    class="text-sm text-red-600">
                                    {{ form.errors.date }}
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="w-full pt-2 md:col-span-2">
                            <button
                                type="submit"
                                class="block w-full px-3 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60"
                                :disabled="form.processing">
                                Update Printed
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
