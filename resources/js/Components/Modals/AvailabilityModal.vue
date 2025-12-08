<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import useStatusMessage from './../../Composables/useStatusMessage';

const props = defineProps({
    offices: Object,
    officeid: Number,
    show: Boolean,
    onClose: Function,
    can: Object,
    routeName: String,
    redirectRoute: String,
});

const { message, status, showMessage, messageText, messageClass } = useStatusMessage();

const form = useForm({
    is_available: null,
    available_dates: null,
});

console.log('o', props.offices);
// reactive URL holder
const submitUrl = ref('');

watch(
    [() => props.routeName, () => props.officeid],
    ([routeName, officeid]) => {
        if (!officeid || !routeName) {
            submitUrl.value = '';
            return;
        }

        // Pass the correct parameter key for each named route
        if (routeName === 'admin.closed.availability') {
            submitUrl.value = route(routeName, { closed: officeid });
        } else if (routeName === 'admin.hotdesk.availability') {
            submitUrl.value = route(routeName, { hotdesk: officeid });
        } else if (routeName === 'admin.dedicated.availability') {
            submitUrl.value = route(routeName, { dedicated: officeid });
        } else if (routeName === 'admin.boardroom.availability') {
            submitUrl.value = route(routeName, { boardroom: officeid });
        } else {
            // fallback: use given name and id
            submitUrl.value = route(routeName, officeid);
        }

        console.log('Resolved submitUrl:', submitUrl.value);
    },
    { immediate: true }
);

const submit = () => {
    form.put(submitUrl.value, {
        preserveScroll: true,
        onSuccess: () => {
            message.value = 'Availability Updated Successfully.';
            status.value = 'success';

            setTimeout(() => {
                router.visit(route(props.redirectRoute));
                router.reload({ preserveScroll: true });
            }, 2000);
        },
    });
};

// sync form values with offices prop
watch(
    () => props.offices,
    office => {
        if (office) {
            form.is_available = office.is_available;
            form.available_dates = office.available_dates;
        }
    },
    { immediate: true }
);

// reset when modal closes
watch(
    () => props.show,
    visible => {
        if (!visible) {
            form.is_available = false;
            form.available_dates = null;
        }
    }
);
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 overflow-y-auto bg-black/60">
        <div class="w-full max-w-2xl p-4 bg-white rounded shadow-lg sm:p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-2xl sm:text-2xl">Availability</h2>

                <button
                    @click="props.onClose"
                    class="text-sm text-black">
                    Close
                </button>
            </div>
            <hr class="my-2 border-gray-300" />

            <template v-if="showMessage">
                <div :class="messageClass">
                    {{ messageText }}
                </div>
            </template>

            <form
                @submit.prevent="submit"
                class="my-10 space-y-6">
                <div>
                    <label class="block mb-2 text-lg font-medium">Availability</label>

                    <div class="flex gap-6">
                        <label class="flex items-center gap-2 text-sm text-bluemain">
                            <input
                                type="radio"
                                :value="1"
                                v-model="form.is_available"
                                class="text-primary" />
                            Available
                        </label>

                        <label class="flex items-center gap-2 text-sm text-bluemain">
                            <input
                                type="radio"
                                :value="0"
                                v-model="form.is_available"
                                class="text-primary" />
                            Unavailable
                        </label>
                    </div>

                    <div
                        v-if="form.errors.is_available"
                        class="text-sm text-red-600">
                        {{ form.errors.is_available }}
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 mb-5">
                    <div>
                        <label class="block mb-2 text-lg font-medium">When will it be available?</label>
                        <input
                            v-model="form.available_dates"
                            type="date"
                            class="w-full px-3 py-2 border rounded" />
                        <div
                            v-if="form.errors.available_dates"
                            class="text-sm text-red-600">
                            {{ form.errors.available_dates }}
                        </div>
                    </div>
                </div>

                <hr class="my-5 border-gray-300" />

                <div class="w-full pt-4 md:col-span-2">
                    <button
                        type="submit"
                        class="block w-full px-3 py-2 text-lg text-white rounded bg-bluemain hover:bg-bluemain/60"
                        :disabled="form.processing">
                        Availability Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
