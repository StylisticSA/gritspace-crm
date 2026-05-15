<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

import FullCalendar from '@fullcalendar/vue3';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

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

// Filters
const activeFilters = ref(uniqueBoardrooms.value.map(b => b.id));
const activeLocationFilters = ref(uniqueLocations.value.map(l => l.id));

const filteredEvents = computed(() =>
    props.events.filter(
        event =>
            activeFilters.value.includes(event.extendedProps?.boardroom_id) &&
            activeLocationFilters.value.includes(event.extendedProps?.location_id)
    )
);

// Calendar Options — only daily timeline
const calendarOptions = ref({
    plugins: [timeGridPlugin, interactionPlugin],
    initialView: 'timeGridDay',
    weekends: false,
    height: 800,
    events: filteredEvents,
    editable: false,
    selectable: true,
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'timeGridDay,timeGridWeek',
    },
    slotMinTime: '04:00:00',
    slotMaxTime: '23:00:00',

    eventContent(arg) {
        const isOwner = arg.event.extendedProps?.isOwner;
        const boardroomName = arg.event.extendedProps?.boardroom;

        const wrapper = document.createElement('div');
        wrapper.classList.add('fc-custom-event');

        const titleEl = document.createElement('div');
        titleEl.textContent = canManageSettings || isOwner ? arg.event.extendedProps?.user : 'Booked';
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

    eventClick(info) {
        selectedEvent.value = {
            boardroom: info.event.extendedProps?.boardroom,
            user: info.event.extendedProps?.user,
            timeLabel: info.event.extendedProps?.timeLabel,
            start: info.event.startStr,
            end: info.event.endStr,
        };
        showModal.value = true;
    },
});

// Grouped boardrooms
const groupedBoardrooms = computed(() => {
    const groups = {};
    props.events.forEach(event => {
        const locName = event.extendedProps?.location;
        const boardroomId = event.extendedProps?.boardroom_id;
        const boardroomName = event.extendedProps?.boardroom;

        if (!locName || !boardroomId || !boardroomName) return;

        if (!groups[locName]) groups[locName] = [];
        if (!groups[locName].some(b => b.id === boardroomId)) {
            groups[locName].push({ id: boardroomId, name: boardroomName });
        }
    });
    return groups;
});
</script>

<template>
    <Head title="Boardrooms Occupied" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Boardrooms Occupied</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="flex flex-col gap-2 mb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-col space-y-2 sm:flex-row sm:space-x-2 sm:space-y-0">
                        <Link
                            :href="route('calendar.boardroom')"
                            class="inline-block px-3 py-2 text-base sm:text-lg font-medium text-white rounded bg-primary hover:bg-bluemain/60">
                            Boardrooms Calendar
                        </Link>
                    </div>
                </div>
            </div>
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
