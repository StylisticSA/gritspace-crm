<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import useStatusMessage from '../../Composables/useStatusMessage';

const props = defineProps({
    helpDesks: Object,
    locations: Array,
    amenities: Array,
});

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();
const amenitiesSelected = props.helpDesks.amenities ? props.helpDesks.amenities.map(a => a.id) : [];

const form = useForm({
    help_desk_name: props.helpDesks.help_desk_name,
    location_id: props.helpDesks.location_id,
    price: props.helpDesks.price,
    duration: props.helpDesks.duration,
    discount: props.helpDesks.discount,
    desks: props.helpDesks.desks,
    amenities: amenitiesSelected,
});

const submit = () => {
    form.put(route('admin.help-desk.update', props.helpDesks.id), {
        onSuccess: () => {
            message.value = 'Hot Desk Edited Successfully.';
            status.value = 'success';

            setTimeout(() => {
                router.reload({ preserveScroll: true });
                router.visit(route('admin.help-desks'));
            }, 2000);
        },
    });
};
</script>

<template>
    <Head title="Edit Category" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Hot Desks</h2>
        </template>

        <div class="p-2">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="max-w-4xl p-6 mx-auto space-y-6">
                    <!-- Search Filter -->
                    <div class="flex flex-col gap-3 mb-4 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="inline-block py-2 text-2xl font-medium text-black">Edit Hot Desk</h3>

                        <Link
                            :href="route('admin.help-desks')"
                            class="inline-block px-2 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60">
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
                        <div class="grid grid-cols-2 gap-6">
                            <!-- Office Name -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Hot Desk Name</label>
                                <input
                                    v-model="form.help_desk_name"
                                    type="text"
                                    class="w-full px-3 py-2 border rounded" />
                                <div
                                    v-if="form.errors.help_desk_name"
                                    class="text-sm text-red-600">
                                    {{ form.errors.help_desk_name }}
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

                            <!-- Price -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Price</label>
                                <input
                                    v-model="form.price"
                                    type="number"
                                    step="1"
                                    min="0"
                                    class="w-full px-3 py-2 border rounded"
                                    placeholder="e.g. 2" />
                                <div
                                    v-if="form.errors.price"
                                    class="text-sm text-red-600">
                                    {{ form.errors.price }}
                                </div>
                            </div>

                            <!-- Duration -->
                            <div>
                                <label class="block text-lg font-medium text-gray-700">Duration</label>
                                <select
                                    v-model.number="form.duration"
                                    class="w-full px-3 py-2 border rounded">
                                    <option
                                        disabled
                                        value="">
                                        Please select a duration
                                    </option>
                                    <option :value="0.5">Half Day</option>
                                    <option :value="1">1 Day</option>
                                    <option :value="5">5 Days</option>
                                    <option :value="10">10 Days</option>
                                    <option :value="20">20 Days</option>
                                </select>
                                <div
                                    v-if="form.errors.duration"
                                    class="text-sm text-red-600">
                                    {{ form.errors.duration }}
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block mb-1 text-lg font-medium text-gray-700">Amenities</label>
                            <div class="grid grid-cols-3 gap-2">
                                <label
                                    v-for="amenity in amenities"
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

                        <div class="w-full pt-2 md:col-span-2">
                            <button
                                type="submit"
                                class="block w-full px-3 py-2 text-lg font-medium text-white rounded bg-bluemain hover:bg-bluemain/60"
                                :disabled="form.processing">
                                Update Hot Desk
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
