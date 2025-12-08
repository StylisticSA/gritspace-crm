<script setup>
import { useForm } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import { ref, watch, computed, reactive } from 'vue';
import Calendar from 'primevue/calendar';
import { addMonths, addYears, eachDayOfInterval } from 'date-fns';
import StatusFeedback from '@/Components/StatusFeedback.vue';

const props = defineProps({
    officeId: Number,
    pricingOptions: Object,
    availablePlans: Array,
    buttonName: String,
    categoryName: String,
    selectedPlan: String,
    categoryId: Number,
    bookedDates: {
        type: Array,
        default: () => [],
    },
    parking: Object,
    location: String,
});

const dailyPlans = ['daily'];
const today = new Date();
const successMessage = ref(null);
const bookingConflict = ref(null);

const form = useForm({
    office_id: props.officeId,
    plan: props.selectedPlan || props.availablePlans[0] || '',
    selected_dates: [],
    weekdays_count: 0,
    start_date: null,
    months: 2,
    end_date: null,
    total_price: 0,
    parking: 0,
    category_id: props.categoryId,
});

console.log('loc', props.location);

const unitPrice = computed(() => props.pricingOptions[form.plan] || 0);

const weekdaysCount = computed(
    () =>
        form.selected_dates.filter(date => {
            const day = new Date(date).getDay();
            return day !== 0 && day !== 6;
        }).length
);

const disabledWeekends = computed(() => {
    if (!dailyPlans.includes(form.plan)) return [];
    const future = addYears(today, 2);
    const allDates = eachDayOfInterval({ start: today, end: future });
    return allDates.filter(d => [0, 6].includes(d.getDay()));
});

watch([() => form.start_date, () => form.months, () => form.plan], () => {
    if (!dailyPlans.includes(form.plan) && form.start_date) {
        const start = new Date(form.start_date);
        const end = addMonths(start, form.months);
        end.setDate(start.getDate());
        form.end_date = end.toISOString().split('T')[0];
        form.total_price = Number(form.months) * Number(unitPrice.value) + Number(form.parking);
    }
});

watch([() => form.plan, () => form.selected_dates, () => form.parking], () => {
    if (dailyPlans.includes(form.plan)) {
        form.weekdays_count = weekdaysCount.value;
        const sorted = [...form.selected_dates].sort((a, b) => new Date(a) - new Date(b));
        form.start_date = sorted[0] ? new Date(sorted[0]).toISOString().split('T')[0] : null;
        form.end_date = sorted[sorted.length - 1]
            ? new Date(sorted[sorted.length - 1]).toISOString().split('T')[0]
            : null;
        form.total_price = Number(unitPrice.value) * weekdaysCount.value;
    } else {
        form.total_price = Number(form.months) * Number(unitPrice.value) + Number(form.parking);
    }
});

watch(
    () => form.parking,
    newPrice => {
        if (!dailyPlans.includes(form.plan)) {
            form.total_price = Number(form.months) * Number(unitPrice.value) + Number(newPrice);
        }
    }
);

watch(
    () => form.plan,
    newPlan => {
        if (dailyPlans.includes(newPlan)) {
            form.parking = 0;
        }
    }
);

const currencyFormatter = new Intl.NumberFormat('en-ZA', {
    style: 'currency',
    currency: 'ZAR',
});

const submit = () => {
    form.post(route('bookingclosed.store'), {
        preserveScroll: true,
        onError: errors => {
            bookingConflict.value = errors.booking_conflict ?? null;
        },
        onSuccess: () => {
            successMessage.value = 'Closed Office booked successfully!';
            bookingConflict.value = null;

            setTimeout(() => {
                successMessage.value = null;
                Inertia.visit(route('bookingclosed.show'));
            }, 1500);
        },
    });
};
</script>

<template>
    <StatusFeedback
        :conflict="bookingConflict"
        :success="successMessage" />

    <form
        @submit.prevent="submit"
        class="pt-5 space-y-4">
        <!-- Plan Selector -->
        <div>
            <label class="block font-semibold">Plan</label>
            <select
                v-model="form.plan"
                class="w-full px-3 py-2 border rounded"
                required>
                <option
                    v-for="plan in availablePlans"
                    :key="plan"
                    :value="plan">
                    {{ plan.charAt(0).toUpperCase() + plan.slice(1) }}
                </option>
            </select>
        </div>

        <!-- Daily Booking -->
        <div
            v-if="dailyPlans.includes(form.plan)"
            class="space-y-4">
            <div>
                <label class="block font-semibold">Select Dates</label>
                <Calendar
                    v-model="form.selected_dates"
                    selectionMode="multiple"
                    :minDate="today"
                    :disabledDates="[...disabledWeekends, ...bookedDates.map(d => new Date(d))]"
                    :disabledDays="[0, 6]"
                    :manualInput="false"
                    dateFormat="yy-mm-dd"
                    showIcon
                    class="w-full" />
            </div>
            <div>
                <label class="block font-semibold">Weekdays Selected</label>
                <div class="px-3 py-2 border rounded bg-gray-50">
                    {{ weekdaysCount }} {{ weekdaysCount === 1 ? 'day' : 'days' }}
                </div>
            </div>
        </div>

        <!-- Monthly Booking -->
        <div
            v-else
            class="space-y-4">
            <div>
                <label class="block font-semibold">Start Date</label>
                <Calendar
                    v-model="form.start_date"
                    :minDate="today"
                    dateFormat="yy-mm-dd"
                    showIcon
                    :manualInput="false"
                    class="w-full" />
            </div>
            <span
                v-if="form.errors.start_date"
                class="text-sm text-red-600">
                {{ form.errors.start_date }}
            </span>

            <div>
                <label class="block font-semibold">Duration (Months)</label>
                <select
                    v-model.number="form.months"
                    class="w-full px-3 py-2 border rounded"
                    required>
                    <option
                        v-for="m in 11"
                        :key="m"
                        :value="m + 1">
                        {{ m + 1 }} {{ m + 1 === 1 ? 'Month' : 'Months' }}
                    </option>
                </select>
            </div>

            <div v-if="location && location.trim().toLowerCase() === 'the village'">
                <label class="block font-semibold">Dedicated Parking</label>
                <div class="grid w-full grid-cols-1 gap-1 mt-3 sm:grid-cols-1 md:grid-cols-3">
                    <!-- Dynamic options from DB -->
                    <label
                        v-for="(option, key) in parking"
                        :key="key"
                        class="flex items-center gap-2 p-2 cursor-pointer">
                        <input
                            type="radio"
                            :value="option.price"
                            v-model.number="form.parking"
                            class="border-gray-300 rounded shadow-sm text-primary focus:ring-bluemain/60 form-radio" />
                        <span>{{ option.name }} - {{ option.price }}</span>
                    </label>

                    <!-- Static option: None -->
                    <label class="flex items-center gap-2 p-2 cursor-pointer">
                        <input
                            type="radio"
                            value="0"
                            v-model.number="form.parking"
                            class="border-gray-300 rounded shadow-sm text-primary focus:ring-bluemain/60 form-radio" />
                        <span>None</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block font-semibold">End Date</label>
                <input
                    type="text"
                    v-model="form.end_date"
                    class="w-full px-3 py-2 border-0 rounded bg-green-50"
                    readonly
                    tabindex="-1"
                    @focus="e => e.target.blur()" />
            </div>
        </div>

        <!-- Total Price -->
        <div>
            <label class="block font-semibold">Total Price</label>
            <input
                type="text"
                :value="currencyFormatter.format(form.total_price)"
                readonly
                tabindex="-1"
                @focus="e => e.target.blur()"
                class="w-full px-3 py-2 bg-gray-100 border-0 rounded cursor-default select-none" />
        </div>

        <!-- Submit -->
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
