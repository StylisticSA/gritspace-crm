<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const props = defineProps({
    users: Object,
    user: Number,
    coffee: Array,
    monthly: Number,
    location: Number,
    locations: Object,
    show: Boolean,
    onClose: Function,
    can: Object,
});

const form = useForm({
    user_id: props.user,
    location_id: props.location ?? null,
    type: 'coffee',
    amount: 0,
    date: new Date().toISOString().slice(0, 10),
});

const successMessage = ref(null);
const amountError = ref(null);

const submit = () => {
    amountError.value = null;

    if (Number(form.amount) === 0) {
        amountError.value = 'Amount must be greater than 0';
        return;
    }

    form.post(route('coffee.store'), {
        preserveScroll: true,
        onSuccess: () => {
            successMessage.value = 'Updated successfully!';

            setTimeout(() => {
                props.onClose();
                successMessage.value = null;
            }, 2000);
        },
    });
};

const incrementCoffee = () => {
    if (form.amount === '') form.amount = 0;
    form.amount = Math.min(Number(form.amount) + 1);
};

const decrementCoffee = () => {
    if (form.amount === '' || Number(form.amount) <= 1) {
        form.amount = 1;
    } else {
        form.amount = Number(form.amount) - 1;
    }
};

const monthName = ref(new Date().toLocaleString('default', { month: 'long' }));

watch(
    () => props.show,
    isOpen => {
        if (isOpen) {
            form.amount = 0;
            form.clearErrors && form.clearErrors('amount');
        }
    }
);
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 overflow-y-auto bg-black/60">
        <div class="w-full max-w-xl max-h-[80vh] overflow-y-auto p-4 bg-white rounded shadow-lg sm:p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-2xl sm:text-2xl">Update Coffee</h2>

                <button
                    @click="props.onClose"
                    class="text-sm text-green-800 underline">
                    Close
                </button>
            </div>
            <hr class="my-2 border-gray-300" />

            <div
                v-if="successMessage"
                class="px-4 py-2 mb-4 text-green-700 bg-green-100 border border-green-300 rounded">
                {{ successMessage }}
            </div>

            <form
                @submit.prevent="submit"
                class="mt-10 space-y-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div class="text-center border-r border-gray-200">
                        <label
                            for="counter"
                            class="block mb-2 text-xl text-center text-gray-900">
                            Choose Quantity:
                        </label>
                        <div class="flex flex-col items-center my-10 space-y-3">
                            <button
                                type="button"
                                @click="incrementCoffee"
                                class="text-lg font-bold text-gray-900 bg-gray-100 border border-gray-300 rounded w-11 h-11 hover:bg-primary/60 focus:ring-gray-100 focus:ring-2 focus:outline-none dark:text-black">
                                +
                            </button>

                            <input
                                type="text"
                                id="counter"
                                v-model="form.amount"
                                class="text-sm font-medium text-center border border-gray-300 w-14 text-whitegray-900 h-11 bg-gray-50 focus:ring-primary/60 focus:border-primary/60 dark:bg-gray-700 dark:text-white"
                                required />
                            <p
                                v-if="amountError"
                                name="counter"
                                class="mt-1 text-sm text-red-600">
                                {{ amountError }}
                            </p>

                            <button
                                type="button"
                                @click="decrementCoffee"
                                class="text-lg font-bold text-gray-900 bg-gray-100 border border-gray-300 rounded w-11 h-11 hover:bg-primary/60 focus:ring-gray-100 focus:ring-2 focus:outline-none dark:text-black">
                                –
                            </button>
                        </div>
                    </div>

                    <hr class="my-2 border-gray-300 md:hidden" />
                    <div>
                        <div>
                            <h3 class="block mb-3 text-xl text-center text-gray-900">Todays</h3>
                            <p class="block text-6xl font-semibold text-center text-black">{{ coffee }}</p>
                        </div>
                        <hr class="my-5 border-gray-300" />
                        <div class="mt-5">
                            <h3 class="block mb-3 text-center text-gray-900 text-md">Total - {{ monthName }}</h3>
                            <p class="block text-3xl font-semibold text-center text-black">{{ monthly }}</p>
                        </div>
                    </div>
                </div>

                <!-- User Selection -->
                <div v-if="can['manage settings']">
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
                <input
                    v-else
                    type="hidden"
                    v-model="form.user_id" />

                <!-- Location Selection -->
                <input
                    type="hidden"
                    v-model="form.location_id" />

                <hr class="my-5 border-gray-300" />

                <div class="flex flex-col gap-3 sm:flex-row sm:justify-between">
                    <button
                        type="submit"
                        class="w-full px-4 py-2 text-sm text-white bg-green-700 rounded sm:w-auto hover:bg-bluemain/60"
                        :disabled="form.processing">
                        Save
                    </button>

                    <button
                        type="button"
                        @click="props.onClose"
                        class="w-full px-4 py-2 text-sm text-white rounded sm:w-auto bg-muted">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
