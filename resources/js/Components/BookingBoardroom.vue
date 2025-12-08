<script setup>
import { useForm } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import { watch, computed, ref } from 'vue';
import Calendar from 'primevue/calendar';
import { differenceInCalendarMonths, isBefore } from 'date-fns';
import StatusFeedback from '@/Components/StatusFeedback.vue';

const props = defineProps({
    boardroomId: Number,
    pricingOptions: Object,
    availablePlans: Array,
    buttonName: String,
    selectedPlan: String,
});

const dailyPlans = ['daily'];
const hourlyPlans = ['hourly'];
const today = new Date();
const successMessage = ref(null);
const bookingConflict = ref(null);

const selectedDateTimes = ref({});

const form = useForm({
    boardroom_id: props.boardroomId,
    plan: props.selectedPlan || props.availablePlans[0] || '',
    selected_dates: [],
    selected_times: {},
    months: 0,
    selected_price: 0,
});

const unitPrice = computed(() => props.pricingOptions[form.plan] || 0);

const weekdaysCount = computed(() => {
    return form.selected_dates.filter(date => {
        const day = new Date(date).getDay();
        return day !== 0 && day !== 6;
    }).length;
});

watch(
    () => form.plan,
    () => {
        form.selected_dates = [];
        form.start_date = '';
        form.end_date = '';
        form.months = 0;
        form.selected_price = 0;
        selectedDateTimes.value = {};
    }
);

watch(
    () => ({
        plan: form.plan,
        selected_dates: form.selected_dates,
        start_date: form.start_date,
        end_date: form.end_date,
        months: form.months,
        selected_times: selectedDateTimes.value,
    }),
    () => {
        if (!form.plan) return;

        if (hourlyPlans.includes(form.plan)) {
            const totalHours = Object.values(selectedDateTimes.value).reduce(
                (sum, slots) => sum + (Array.isArray(slots) ? slots.length : 0),
                0
            );
            form.selected_price = unitPrice.value * totalHours;
        } else if (dailyPlans.includes(form.plan)) {
            form.selected_price = unitPrice.value * weekdaysCount.value;
        }
    },
    { immediate: true, deep: true }
);

const timeSlots = Array.from({ length: 16 }, (_, i) => {
    const hour = i + 6;
    return `${hour.toString().padStart(2, '0')}:00`;
});

const toggleTimeSlot = (dateKey, time) => {
    if (!selectedDateTimes.value[dateKey]) {
        selectedDateTimes.value[dateKey] = [];
    }

    const index = selectedDateTimes.value[dateKey].indexOf(time);
    if (index > -1) {
        selectedDateTimes.value[dateKey].splice(index, 1);
    } else {
        selectedDateTimes.value[dateKey].push(time);
    }
};

watch(weekdaysCount, count => {
    form.months = count;
});

const submit = () => {
    form.selected_times = selectedDateTimes.value;
    form.post(route('bookingboardroom.store'), {
        onError: errors => {
            bookingConflict.value = errors.booking_conflict ?? null;
        },
        onSuccess: () => {
            successMessage.value = 'Boardroom Booking created successfully!';
            bookingConflict.value = null;

            setTimeout(() => {
                successMessage.value = null;
                Inertia.visit(route('bookingboardroom.show'));
            }, 1500);
        },
    });
};

const currencyFormatter = new Intl.NumberFormat('en-ZA', {
    style: 'currency',
    currency: 'ZAR',
});
</script>

<template>
    <StatusFeedback
        :conflict="bookingConflict"
        :success="successMessage" />

    <form
        @submit.prevent="submit"
        class="space-y-4">
        <!-- Plan -->
        <div>
            <label class="block font-semibold">Plan</label>
            <select
                v-model="form.plan"
                class="w-full px-3 py-2 border rounded">
                <option
                    v-for="plan in availablePlans"
                    :key="plan"
                    :value="plan">
                    {{ plan.charAt(0).toUpperCase() + plan.slice(1) }}
                </option>
            </select>
            <span
                v-if="form.errors.plan"
                class="text-sm text-red-600">
                {{ form.errors.plan }}
            </span>
        </div>

        <!-- Selected Dates -->
        <div>
            <label class="block font-semibold">Select Dates</label>
            <Calendar
                v-model="form.selected_dates"
                selectionMode="multiple"
                :minDate="today"
                :disabledDays="[0, 6]"
                dateFormat="dd-mm-yy"
                showIcon
                class="w-full" />
            <span
                v-if="form.errors.selected_dates"
                class="text-sm text-red-600">
                {{ form.errors.selected_dates }}
            </span>
        </div>
        <!-- Days -->
        <div>
            <label class="block font-semibold">Duration (Days)</label>
            <div class="w-full px-3 py-2 text-gray-700 rounded bg-green-50">
                {{ weekdaysCount || 'No weekdays selected yet' }}
            </div>
            <input
                type="hidden"
                v-model="form.months" />
        </div>

        <!-- Selected Times (Hourly Only) -->
        <div v-if="hourlyPlans.includes(form.plan)">
            <label class="block mb-3 font-semibold">Select hour slots(s)</label>
            <span
                v-if="form.errors.selected_times"
                class="text-sm text-red-600">
                {{ form.errors.selected_times }}
            </span>

            <div
                v-for="date in form.selected_dates"
                :key="date.toISOString()"
                class="space-y-2">
                <div class="font-normal">
                    {{ new Date(date).toDateString() }}
                </div>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="slot in timeSlots"
                        :key="slot"
                        type="button"
                        class="px-2 py-1 text-sm border rounded"
                        :class="{
                            'bg-primary xt-white':
                                selectedDateTimes[new Date(date).toISOString().split('T')[0]]?.includes(slot),
                            'bg-gray-100':
                                !selectedDateTimes[new Date(date).toISOString().split('T')[0]]?.includes(slot),
                        }"
                        @click="toggleTimeSlot(new Date(date).toISOString().split('T')[0], slot)">
                        {{ slot }}
                    </button>
                </div>
            </div>
        </div>

        <!-- PRICE -->
        <div>
            <label class="block font-semibold">Total Price</label>
            <input
                type="text"
                :value="currencyFormatter.format(form.selected_price)"
                readonly
                tabindex="-1"
                @focus="e => e.target.blur()"
                class="w-full px-3 py-2 bg-gray-100 border-0 rounded cursor-default select-none" />
            <input
                type="hidden"
                v-model="form.selected_price" />
        </div>

        <!-- SUBMIT -->
        <div>
            <button
                :disabled="form.processing"
                type="submit"
                class="px-4 py-2 text-sm text-white rounded bg-primary hover:bg-bluemain">
                Enquire {{ buttonName }}
            </button>
        </div>
    </form>
</template>
