<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    notificationsSummary: Object,
    adminSummary: Object,
    notificationsTotal: Number,
    adminTotal: Number,
    can: Object,
    officeid: Number,
});

const open = ref(false);
const dropdownRef = ref(null);

function handleClickOutside(event) {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        open.value = false;
    }
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function goTo(model) {
    let routeName = '';
    let params = {};

    switch (model) {
        case 'closed':
            routeName = 'bookingclosed.show';
            break;
        case 'hotdesk':
            routeName = 'bookinghotdesk.show';
            break;
        case 'dedicated':
            routeName = 'bookingdedicated.show';
            break;
        case 'boardroom':
            routeName = 'bookingboardroom.show';
            break;
        case 'virtual':
            routeName = 'bookingvirtual.show';
            break;
        default:
            routeName = `admin.${model}.availability`;
    }

    router.visit(route(routeName, params));
}
</script>
<template>
    <div
        ref="dropdownRef"
        class="relative">
        <!-- Bell button -->
        <button
            @click="open = !open"
            aria-label="Notifications"
            class="relative p-2 rounded-lg hover:bg-secondary-100 dark:hover:bg-secondary-800 transition-colors outline-none">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="w-5 h-5 text-secondary-700 dark:text-secondary-300"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round">
                <path d="M10.268 21a2 2 0 0 0 3.464 0"></path>
                <path
                    d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326"></path>
            </svg>

            <!-- Badge -->
            <span
                class="absolute top-1 right-1 flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-red-600 rounded-full">
                {{ notificationsTotal + adminTotal }}
            </span>
        </button>

        <!-- Dropdown -->
        <div
            v-if="open"
            @click.stop
            class="absolute mt-2 w-screen sm:w-80 origin-top rounded-xl bg-white shadow-lg border border-secondary-200/50 overflow-hidden z-50 left-0 sm:left-auto sm:right-0">
            <!-- Header -->
            <div class="px-4 py-3 border-b border-secondary-200 dark:border-secondary-700">
                <h3 class="text-sm font-semibold text-secondary-900 text-bluemain">All Notifications</h3>
            </div>

            <!-- Items -->
            <div class="max-h-96 overflow-y-auto p-4 grid grid-cols-2 gap-4">
                <!-- Admin notifications -->
                <template v-if="can['manage settings']">
                    <div
                        v-if="adminTotal === 0"
                        class="col-span-2 text-center text-sm text-secondary-500 dark:text-secondary-400 py-6">
                        There is nothing
                    </div>
                    <button
                        v-for="(count, model) in adminSummary"
                        v-show="count > 0"
                        :key="'admin-' + model"
                        class="rounded-lg shadow-sm border border-secondary-200 dark:border-secondary-700 bg-white dark:bg-secondary-700/40 p-4 hover:shadow-md transition text-left w-full"
                        @click="goTo(model)">
                        <p class="text-sm font-semibold text-secondary-900 text-bluemain truncate">
                            {{ capitalize(model) }}
                        </p>
                        <p class="text-xs text-secondary-600 dark:text-secondary-400 mt-1">{{ count }} Pending</p>
                    </button>
                </template>

                <!-- User notifications -->
                <template v-else>
                    <div
                        v-if="notificationsTotal === 0"
                        class="col-span-2 text-center text-sm text-secondary-500 dark:text-secondary-400 py-6">
                        There is nothing
                    </div>
                    <button
                        v-for="(count, model) in notificationsSummary"
                        v-show="count > 0"
                        :key="'user-' + model"
                        class="rounded-lg shadow-sm border border-secondary-200 dark:border-secondary-700 bg-white dark:bg-secondary-700/40 p-4 hover:shadow-md transition text-left w-full"
                        @click="goTo(model)">
                        <p class="text-sm font-semibold text-secondary-900 text-bluemain truncate">
                            {{ capitalize(model) }}
                        </p>
                        <p class="text-xs text-secondary-600 dark:text-secondary-400 mt-1">{{ count }} items</p>
                    </button>
                </template>
            </div>

            <!-- Footer -->
            <div class="px-4 py-3 border-t border-secondary-200 dark:border-secondary-700">
                <div
                    v-if="can['manage settings']"
                    class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium transition-colors">
                    Waiting to be Approved
                </div>
                <div
                    v-else
                    class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium transition-colors">
                    Waiting for Payment
                </div>
            </div>
        </div>
    </div>
</template>
