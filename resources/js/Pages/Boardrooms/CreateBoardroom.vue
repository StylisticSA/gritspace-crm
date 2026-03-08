<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import useStatusMessage from '../../Composables/useStatusMessage';

const form = useForm({
    boardroom_name: '',
    location_id: '',
    seats: '',
    hourly_price: null,
    daily_price: null,
    amenities: [],
});

const props = defineProps({
    locations: Array,
    amenities: Array,
});

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();

const currentDate = new Date();
const currentYear = currentDate.getFullYear();
const currentMonth = String(currentDate.getMonth() + 1).padStart(2, '0');

const minDate = `${currentYear}-${currentMonth}-01`;

const submit = () => {
    form.post(route('admin.boardrooms.store'), {
        onSuccess: () => {
            message.value = 'A boardroom has been created successfully.';
            status.value = 'success';

            setTimeout(() => {
                router.visit(route('admin.boardrooms'), {
                    preserveState: true,
                    preserveScroll: true,
                });
                router.reload({ preserveScroll: true });
            }, 3000);
        },
        onError: errors => {
            message.value = Object.values(errors).join('\n');
            status.value = 'deleted';
        },
    });
};
</script>

<template>
    <Head title="Add Boardroom" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Boardrooms</h2>
        </template>

        <div class="py-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-4xl p-6 mx-auto space-y-6">
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-2xl font-medium text-black">Add Boardroom</h3>

                        <Link
                            :href="route('admin.boardrooms')"
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
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Boardroom Name -->
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

                            <!-- Seats -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Seats</label>
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
                                class="block w-full px-3 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain"
                                :disabled="form.processing">
                                Add Boardroom
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
