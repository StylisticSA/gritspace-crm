<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted, watchEffect } from 'vue';

import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';

const props = defineProps({
    events: {
        type: Array,
        default: () => [],
    },
    can: Object,
});

const openLocations = ref({});
const selectedEvent = ref(null);
const showModal = ref(false);

onMounted(() => {
    const isMobile = window.innerWidth < 768;
    calendarOptions.value.headerToolbar = isMobile
        ? { left: 'prev,next', center: 'title', right: '' }
        : {
              left: 'prev,next today',
              center: 'title',
              right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
          };
});

const canManageSettings = props.can?.['manage settings'] ?? false;

const uniqueOffices = computed(() => {
    const seen = new Map();
    props.events.forEach(event => {
        const id = event.extendedProps?.office_id;
        const name = event.extendedProps?.office;
        if (id && name && !seen.has(id)) {
            seen.set(id, { id, name });
        }
    });
    return Array.from(seen.values());
});

const uniqueLocations = computed(() => {
    const seen = new Map();
    props.events.forEach(event => {
        const id = event.extendedProps?.location_id;
        const name = event.extendedProps?.location;
        if (id && name && !seen.has(id)) {
            seen.set(id, { id, name });
        }
    });
    return Array.from(seen.values()).sort((a, b) => a.name.localeCompare(b.name, 'en', { sensitivity: 'base' }));
});

const activeFilters = ref([]);
const activeLocationFilters = ref([]);

// Ensure filters are populated once events are loaded
watchEffect(() => {
    activeFilters.value = uniqueOffices.value.map(o => o.id);
    activeLocationFilters.value = uniqueLocations.value.map(l => l.id);
});

const filteredEvents = computed(() =>
    props.events.filter(
        event =>
            activeFilters.value.includes(event.extendedProps?.office_id) &&
            activeLocationFilters.value.includes(event.extendedProps?.location_id)
    )
);

const calendarOptions = ref({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin],
    initialView: 'dayGridMonth',
    weekends: false,
    height: 800,
    events: filteredEvents,
    eventBackgroundColor: 'transparent',
    eventBorderColor: 'transparent',
    eventDisplay: 'block',
    dayMaxEvents: 4,
    editable: true,
    selectable: true,
    headerToolbar: {},

    eventContent: function (arg) {
        const userOrBooked = arg.event.title; // will be "Booked" or user name
        const officeOrDesk =
            arg.event.extendedProps?.office ||
            arg.event.extendedProps?.helpdesk ||
            arg.event.extendedProps?.virtual_office;

        const wrapper = document.createElement('div');
        wrapper.classList.add('fc-custom-event');
        wrapper.style.padding = '2px';
        wrapper.style.borderRadius = '2px';
        wrapper.style.overflow = 'hidden';
        wrapper.style.whiteSpace = 'nowrap';
        wrapper.style.textOverflow = 'ellipsis';
        wrapper.style.maxWidth = '90%';

        const titleEl = document.createElement('div');
        titleEl.textContent = userOrBooked;
        titleEl.className = 'text-xs font-semibold text-gray-800';
        wrapper.appendChild(titleEl);

        if (officeOrDesk) {
            const officeEl = document.createElement('div');
            officeEl.textContent = officeOrDesk;
            officeEl.className = 'text-xs text-gray-600';
            wrapper.appendChild(officeEl);
        }

        return { domNodes: [wrapper] };
    },
    eventDidMount: function (info) {
        const el = info.el;
        const bg = info.event.backgroundColor;
        const border = info.event.borderColor;
        const text = info.event.textColor;

        if (bg) el.style.backgroundColor = bg;
        if (border) el.style.borderColor = border;
        if (text) el.style.color = text;
    },
    eventClick: info => {
        const rawStart = info.event.start;

        selectedEvent.value = {
            title: info.event.title,
            office: info.event.extendedProps?.office,
            start: rawStart.toISOString().split('T')[0],
            end: info.event.endStr,
            color: info.event.backgroundColor,
        };
        showModal.value = true;
    },
});

const groupedOffices = computed(() => {
    const groups = {};
    props.events.forEach(event => {
        const locName = event.extendedProps?.location;
        const officeId = event.extendedProps?.office_id;
        const officeName = event.extendedProps?.office;

        if (!locName || !officeId || !officeName) return;

        if (!groups[locName]) {
            groups[locName] = [];
        }

        if (!groups[locName].some(o => o.id === officeId)) {
            groups[locName].push({ id: officeId, name: officeName });
        }
    });
    return groups;
});
</script>
<template>
    <Head title="Closed Offices Calendar" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Closed Offices Calendar</h2>
        </template>

        <div class="py-12">
            <div class="flex flex-col gap-6 px-4 mx-auto lg:flex-row max-w-7xl lg:px-8">
                <!-- Sidebar Filters -->
                <aside
                    class="flex flex-col w-full max-h-full gap-2 p-4 overflow-auto bg-white rounded-lg shadow lg:w-1/4">
                    <h3 class="mb-4 text-lg font-semibold">Filter Offices</h3>

                    <div class="mb-2">
                        <button
                            @click="activeFilters = uniqueOffices.map(o => o.id)"
                            class="block w-full px-3 py-1 text-sm text-white rounded bg-primary">
                            All
                        </button>
                    </div>

                    <div
                        v-for="(offices, location) in groupedOffices"
                        :key="location">
                        <button
                            @click="openLocations[location] = !openLocations[location]"
                            class="flex items-center justify-between w-full px-2 py-1 text-sm font-semibold text-left bg-gray-100 rounded">
                            <span>{{ location }}</span>
                            <span>{{ openLocations[location] ? '−' : '+' }}</span>
                        </button>

                        <transition name="fade">
                            <div
                                v-if="openLocations[location]"
                                class="pl-2 mt-1">
                                <div
                                    v-for="office in offices"
                                    :key="office.id"
                                    class="flex items-center gap-2 mb-1">
                                    <input
                                        type="checkbox"
                                        :value="office.id"
                                        v-model="activeFilters"
                                        class="text-primary form-checkbox" />
                                    <span class="text-gray-700">{{ office.name }}</span>
                                </div>
                            </div>
                        </transition>
                    </div>
                </aside>

                <!-- Calendar -->
                <section class="w-full p-4 bg-white rounded shadow lg:w-3/4">
                    <FullCalendar :options="calendarOptions" />
                </section>
            </div>
        </div>

        <!-- Modal -->
        <teleport to="body">
            <div
                v-if="showModal"
                class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 overflow-auto bg-black bg-opacity-50">
                <div class="w-full max-w-md p-4 bg-white rounded-lg shadow-lg sm:p-6">
                    <h3 class="mb-4 text-lg font-semibold text-left break-words">
                        {{ selectedEvent.office }}
                    </h3>

                    <div class="mb-4 space-y-2 font-mono text-sm text-gray-700">
                        <div
                            class="flex flex-col sm:flex-row"
                            v-if="can['manage settings']">
                            <div class="mb-1 font-semibold sm:w-40 sm:mb-0">Booked By:</div>
                            <div class="flex-1 break-words">{{ selectedEvent.title }}</div>
                        </div>

                        <div class="flex flex-col sm:flex-row">
                            <div class="mb-1 font-semibold sm:w-40 sm:mb-0">Start Date:</div>
                            <div class="flex-1 break-words">{{ selectedEvent.start }}</div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button
                            @click="showModal = false"
                            class="px-4 py-2 text-sm text-white rounded sm:text-base bg-primary">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </teleport>
    </AuthenticatedLayout>
</template>
