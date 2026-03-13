<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

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

// Unique boardrooms
const uniqueBoardrooms = computed(() => {
    const seen = new Map();
    props.events.forEach(event => {
        const id = event.extendedProps?.boardroom_id;
        const name = event.extendedProps?.boardroom;
        if (id && name && !seen.has(id)) {
            seen.set(id, { id, name });
        }
    });
    return Array.from(seen.values());
});

// Unique locations
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

// Initialize filters once with full set
const activeFilters = ref(uniqueBoardrooms.value.map(b => b.id));
const activeLocationFilters = ref(uniqueLocations.value.map(l => l.id));

const filteredEvents = computed(() =>
    props.events.filter(
        event =>
            activeFilters.value.includes(event.extendedProps?.boardroom_id) &&
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
        const isOwner = arg.event.extendedProps?.isOwner;
        const boardroomName = arg.event.extendedProps?.boardroom;

        const wrapper = document.createElement('div');
        wrapper.classList.add('fc-custom-event');
        wrapper.style.padding = '2px';
        wrapper.style.borderRadius = '2px';
        wrapper.style.overflow = 'hidden';
        wrapper.style.whiteSpace = 'nowrap';
        wrapper.style.textOverflow = 'ellipsis';
        wrapper.style.maxWidth = '100%';

        const titleEl = document.createElement('div');
        if (canManageSettings || isOwner) {
            titleEl.textContent = arg.event.title;
        } else {
            titleEl.textContent = 'Booked';
        }
        titleEl.className = 'text-xs font-semibold text-gray-800';
        wrapper.appendChild(titleEl);

        if (boardroomName) {
            const brEl = document.createElement('div');
            brEl.textContent = boardroomName;
            brEl.className = 'text-xs text-gray-600';
            wrapper.appendChild(brEl);
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
            boardroom: info.event.extendedProps?.boardroom,
            start: rawStart.toISOString().split('T')[0],
            end: info.event.endStr,
            color: info.event.backgroundColor,
        };
        showModal.value = true;
    },
});

// Grouped boardrooms by location (always from props.events)
const groupedBoardrooms = computed(() => {
    const groups = {};
    props.events.forEach(event => {
        const locName = event.extendedProps?.location;
        const boardroomId = event.extendedProps?.boardroom_id;
        const boardroomName = event.extendedProps?.boardroom;

        if (!locName || !boardroomId || !boardroomName) return;

        if (!groups[locName]) {
            groups[locName] = [];
        }

        if (!groups[locName].some(b => b.id === boardroomId)) {
            groups[locName].push({ id: boardroomId, name: boardroomName });
        }
    });
    return groups;
});
</script>

<template>
    <Head title="Boardrooms Calendar" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Boardrooms Calendar</h2>
        </template>

        <div class="py-12">
            <div class="flex flex-col gap-6 px-4 mx-auto lg:flex-row max-w-7xl lg:px-8">
                <!-- Sidebar Filters -->
                <aside
                    class="flex flex-col w-full max-h-full gap-2 p-4 overflow-auto bg-white rounded-lg shadow lg:w-1/4">
                    <h3 class="mb-4 text-lg font-semibold">Filter Boardrooms</h3>

                    <div class="mb-2">
                        <button
                            @click="
                                activeFilters = uniqueBoardrooms.map(b => b.id);
                                activeLocationFilters = uniqueLocations.map(l => l.id);
                            "
                            class="block w-full px-3 py-1 text-sm text-white rounded bg-primary">
                            All
                        </button>
                    </div>

                    <div
                        v-for="(rooms, location) in groupedBoardrooms"
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
                                    v-for="room in rooms"
                                    :key="room.id"
                                    class="flex items-center gap-2 mb-1">
                                    <input
                                        type="checkbox"
                                        :value="room.id"
                                        v-model="activeFilters"
                                        class="text-primary form-checkbox" />
                                    <span class="text-gray-700">{{ room.name }}</span>
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
                        {{ selectedEvent.boardroom }}
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
