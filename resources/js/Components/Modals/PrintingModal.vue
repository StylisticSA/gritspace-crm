<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, watch, nextTick } from 'vue';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const props = defineProps({
    users: Object,
    user: Number,
    print: Array,
    printBlack: Array,
    printColor: Array,
    monthly: Number,
    printBlackTotal: Number,
    printColorTotal: Number,
    location: Number,
    locations: Object,
    show: Boolean,
    onClose: Function,
    can: Object,
});

const form = useForm({
    user_id: props.user,
    location_id: props.location ?? null,
    type: 'printing',
    color_amount: 0,
    black_amount: 0,
    date: new Date().toISOString().slice(0, 10),
});

const successMessage = ref(null);
const amountColorError = ref(null);
const amountBlackError = ref(null);

const scrollContainer = ref(null);

const submit = () => {
    amountColorError.value = null;
    amountBlackError.value = null;

    if (Number(form.black_amount || 0) === 0 && Number(form.color_amount || 0) === 0) {
        amountBlackError.value = 'Enter at least one black print amount';
        amountColorError.value = 'Enter at least one color print amount';
        return;
    }

    form.post(route('printing.store'), {
        preserveScroll: true,
        onSuccess: () => {
            nextTick(() => {
                if (scrollContainer.value) {
                    scrollContainer.value.scrollTop = 0;
                }
            });

            successMessage.value = 'Updated successfully!';
            setTimeout(() => {
                props.onClose();
                successMessage.value = null;
            }, 2000);
        },
    });
};

const incrementColorPrint = () => {
    if (form.color_amount === '') form.color_amount = 0;
    form.color_amount = Math.min(Number(form.color_amount) + 1);
};

const decrementColorPrint = () => {
    if (form.color_amount === '') form.color_amount = 0;
    form.color_amount = Math.max(Number(form.color_amount) - 1, 0);
};

const incrementBlackPrint = () => {
    if (form.black_amount === '') form.black_amount = 0;
    form.black_amount = Math.min(Number(form.black_amount) + 1);
};

const decrementBlackPrint = () => {
    if (form.black_amount === '') form.black_amount = 0;
    form.black_amount = Math.max(Number(form.black_amount) - 1, 0);
};

const monthName = ref(new Date().toLocaleString('default', { month: 'long' }));

watch(
    () => props.show,
    isOpen => {
        if (isOpen) {
            form.color_amount = 0;
            form.black_amount = 0;
            form.clearErrors && form.clearErrors('color_amount');
            form.clearErrors && form.clearErrors('black_amount');
        }
    }
);
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 overflow-y-auto bg-black/60">
        <div
            ref="scrollContainer"
            class="w-full max-w-4xl max-h-[80vh] overflow-y-auto p-4 bg-white rounded shadow-lg sm:p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-2xl sm:text-2xl">Update Pages Printed</h2>

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
                class="mt-5 space-y-6">
                <div>
                    <h3 class="mb-5 text-xl">Color</h3>
                    <hr class="my-2 border-gray-300" />
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div class="text-center border-r border-gray-200">
                            <label
                                for="counter"
                                class="block my-3 text-center text-gray-900 text-md">
                                Choose Quantity:
                            </label>
                            <div class="flex flex-col items-center my-5 space-y-1">
                                <button
                                    type="button"
                                    @click="incrementColorPrint"
                                    class="text-lg font-bold text-gray-900 bg-gray-100 border border-gray-300 rounded w-11 h-11 hover:bg-primary/60 focus:ring-gray-100 focus:ring-2 focus:outline-none dark:text-black">
                                    +
                                </button>

                                <input
                                    type="text"
                                    id="counter"
                                    v-model="form.color_amount"
                                    class="text-sm font-medium text-center border border-gray-300 w-14 text-whitegray-900 h-11 bg-gray-50 focus:ring-primary/60 focus:border-primary/60 dark:bg-gray-700 dark:text-white"
                                    required />

                                <p
                                    v-if="amountColorError"
                                    class="mt-1 text-sm text-red-600">
                                    {{ amountColorError }}
                                </p>

                                <button
                                    type="button"
                                    @click="decrementColorPrint"
                                    class="text-lg font-bold text-gray-900 bg-gray-100 border border-gray-300 rounded w-11 h-11 hover:bg-primary/60 focus:ring-gray-100 focus:ring-2 focus:outline-none dark:text-black">
                                    –
                                </button>
                            </div>
                        </div>
                        <hr class="my-2 border-gray-300 md:hidden" />
                        <div>
                            <div>
                                <h3 class="block my-3 text-xl text-center text-gray-900">Todays</h3>
                                <p class="block text-6xl font-semibold text-center text-black">{{ printColor }}</p>
                            </div>
                            <hr class="my-5 border-gray-300" />
                            <div class="mt-5">
                                <h3 class="block my-3 text-center text-gray-900 text-md">Total - {{ monthName }}</h3>
                                <p class="block text-3xl font-semibold text-center text-black">{{ printColorTotal }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="my-2 border-gray-300" />
                <div>
                    <h3 class="mb-5 text-xl">Black and White</h3>
                    <hr class="my-2 border-gray-300" />
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div class="text-center border-r border-gray-200">
                            <label
                                for="counter"
                                class="block my-3 text-center text-gray-900 text-md">
                                Choose Quantity:
                            </label>
                            <div class="flex flex-col items-center my-5 space-y-1">
                                <button
                                    type="button"
                                    @click="incrementBlackPrint"
                                    class="text-lg font-bold text-gray-900 bg-gray-100 border border-gray-300 rounded w-11 h-11 hover:bg-primary/60 focus:ring-gray-100 focus:ring-2 focus:outline-none dark:text-black">
                                    +
                                </button>

                                <input
                                    type="text"
                                    id="counter"
                                    v-model="form.black_amount"
                                    class="text-sm font-medium text-center border border-gray-300 w-14 text-whitegray-900 h-11 bg-gray-50 focus:ring-primary/60 focus:border-primary/60 dark:bg-gray-700 dark:text-white"
                                    required />

                                <p
                                    v-if="amountBlackError"
                                    class="mt-1 text-sm text-red-600">
                                    {{ amountBlackError }}
                                </p>

                                <button
                                    type="button"
                                    @click="decrementBlackPrint"
                                    class="text-lg font-bold text-gray-900 bg-gray-100 border border-gray-300 rounded w-11 h-11 hover:bg-primary/60 focus:ring-gray-100 focus:ring-2 focus:outline-none dark:text-black">
                                    –
                                </button>
                            </div>
                        </div>
                        <hr class="my-2 border-gray-300 md:hidden" />
                        <div>
                            <div>
                                <h3 class="block my-3 text-xl text-center text-gray-900">Todays</h3>
                                <p class="block text-6xl font-semibold text-center text-black">{{ printBlack }}</p>
                            </div>
                            <hr class="my-5 border-gray-300" />
                            <div class="mt-5">
                                <h3 class="block mb-3 text-center text-gray-900 text-md">Total - {{ monthName }}</h3>
                                <p class="block text-3xl font-semibold text-center text-black">{{ printBlackTotal }}</p>
                            </div>
                        </div>
                    </div>
                </div>
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

                <div>
                    <input
                        type="hidden"
                        name="location_id"
                        v-model="form.location_id" />
                </div>

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
