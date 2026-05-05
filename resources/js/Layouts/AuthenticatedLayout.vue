<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

const page = usePage();
const can = page.props.can || {};

const bookingRoutes = [
    'bookingclosed.show',
    'bookingdedicated.show',
    'bookinghotdesk.show',
    'bookingvirtual.show',
    'bookingboardroom.show',
];

const activeGroups = {
    settings: ['booking.offices', 'booking.boardrooms', 'virtual.home'],
    products: [
        'admin.closedoffices',
        'admin.dedicateddesk',
        'admin.help-desks',
        'admin.virtual-offices',
        'admin.boardrooms',
    ],
    system: [
        'admin.locations',
        'admin.categories',
        'admin.amenities',
        'admin.extra.index',
        'admin.parking.index',
        'admin.discounts.index',
    ],
    clients: ['admin.clientinfor.index', 'admin.clientrates.index', 'admin.agreement.index'],
    extras: ['admin.coffee.index', 'admin.printing.index'],
    hours: ['admin.hours.index', 'admin.boardroom_hours.index'],
};

function isActive(group) {
    return activeGroups[group].some(r => route().current(r));
}
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100">
                <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        <div class="flex items-center gap-10">
                            <Link
                                v-if="!can['manage settings']"
                                :href="route('dashboard')"
                                class="flex items-center">
                                <ApplicationLogo class="block w-auto h-12 text-gray-800 fill-current" />
                            </Link>

                            <Link
                                v-if="can['manage settings']"
                                :href="route('admin.dashboard')"
                                class="flex items-center">
                                <ApplicationLogo class="block w-auto h-12 text-gray-800 fill-current" />
                            </Link>

                            <!-- Navigation Links -->
                            <div class="items-center hidden space-x-6 sm:flex">
                                <!-- Dropdown: Spaces -->
                                <NavLink
                                    v-if="!can['manage settings']"
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                    >Dashboard</NavLink
                                >
                                <NavLink
                                    :href="route('admin.dashboard')"
                                    v-if="can['manage settings']"
                                    :active="route().current('admin.dashboard')"
                                    :class="route().current('admin.dashboard') ? 'text-primary border-bluemain/60' : ''"
                                    >Dashboard</NavLink
                                >

                                <Dropdown
                                    align="left"
                                    width="48"
                                    v-if="can['menu offices']">
                                    <template #trigger>
                                        <button
                                            class="inline-flex items-center px-4 py-2 text-sm transition duration-150 ease-in-out focus:outline-none"
                                            :class="
                                                route().current('booking.offices') ||
                                                route().current('booking.boardrooms') ||
                                                route().current('virtual.home')
                                                    ? 'bg-primary rounded text-white font-semibold'
                                                    : 'text-gray-500 font-medium hover:text-gray-700 hover:bg-gray-100 rounded-md'
                                            ">
                                            Bookings
                                            <svg
                                                class="w-4 h-4 ml-1"
                                                fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </template>
                                    <template #content>
                                        <DropdownLink
                                            :href="route('booking.offices')"
                                            v-if="can['menu offices']"
                                            :active="route().current('booking.offices')"
                                            >Grit Spaces</DropdownLink
                                        >

                                        <DropdownLink
                                            :href="route('booking.boardrooms')"
                                            v-if="can['menu offices']"
                                            :active="route().current('booking.boardrooms')"
                                            >Boardrooms</DropdownLink
                                        >

                                        <DropdownLink
                                            :href="route('virtual.home')"
                                            v-if="can['menu offices']"
                                            :active="route().current('virtual.home')"
                                            >Virtual Office</DropdownLink
                                        >
                                    </template>
                                </Dropdown>

                                <NavLink
                                    :href="route('booking.offices')"
                                    v-if="can['view book offices']"
                                    :active="route().current('booking.offices')">
                                    Grit Spaces
                                </NavLink>

                                <NavLink
                                    :href="route('booking.boardrooms')"
                                    v-if="can['view book boardrooms']"
                                    :active="route().current('booking.boardrooms')">
                                    Boardrooms
                                </NavLink>

                                <NavLink
                                    :href="route('virtual.home')"
                                    v-if="can['view book boardrooms']"
                                    :active="route().current('virtual.home')">
                                    Virtual Office
                                </NavLink>
                            </div>
                        </div>

                        <!-- Right Section: User Avatar -->
                        <div class="items-center hidden space-x-2 sm:flex">
                            <div class="items-center hidden space-x-6 sm:flex">
                                <!-- Calendars -->
                                <Dropdown
                                    align="left"
                                    width="48"
                                    v-if="can['view book extras']">
                                    <template #trigger>
                                        <button
                                            class="inline-flex items-center px-4 py-2 text-sm transition duration-150 ease-in-out focus:outline-none"
                                            :class="
                                                route().current('calendar.*')
                                                    ? 'bg-primary rounded text-white font-semibold'
                                                    : 'text-gray-500 font-medium hover:text-gray-700 hover:bg-gray-100 rounded-md'
                                            ">
                                            Calendars
                                            <svg
                                                class="w-4 h-4 ml-1"
                                                fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </template>
                                    <template #content>
                                        <DropdownLink
                                            :href="route('calendar.closed')"
                                            :active="route().current('calendar.closed')"
                                            v-if="can['view book extras']"
                                            >Closed Offices</DropdownLink
                                        >

                                        <DropdownLink
                                            :href="route('calendar.dedicated')"
                                            :active="route().current('calendar.dedicated')"
                                            v-if="can['view book extras']"
                                            >Dedicated Desks</DropdownLink
                                        >

                                        <DropdownLink
                                            :href="route('calendar.hotdesk')"
                                            :active="route().current('calendar.hotdesk')"
                                            v-if="can['view book extras']"
                                            >Hot Desks</DropdownLink
                                        >

                                        <DropdownLink
                                            :href="route('calendar.boardroom')"
                                            :active="route().current('calendar.boardroom')"
                                            v-if="can['view book extras']"
                                            >Boardrooms</DropdownLink
                                        >
                                    </template>
                                </Dropdown>

                                <!-- Bookings -->
                                <Dropdown
                                    align="right"
                                    width="48"
                                    v-if="can['view book extras']">
                                    <template #trigger>
                                        <button
                                            class="inline-flex items-center px-4 py-2 text-sm transition duration-150 ease-in-out focus:outline-none"
                                            :class="
                                                route().current('bookingclosed.show') ||
                                                route().current('bookingdedicated.show') ||
                                                route().current('bookinghotdesk.show') ||
                                                route().current('bookingvirtual.show') ||
                                                route().current('bookingboardroom.show')
                                                    ? 'bg-primary rounded text-white font-semibold'
                                                    : 'text-gray-500 font-medium hover:text-gray-700 hover:bg-gray-100 rounded-md'
                                            ">
                                            Bookings
                                            <svg
                                                class="w-4 h-4 ml-1"
                                                fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </template>
                                    <template #content>
                                        <DropdownLink
                                            :href="route('bookingclosed.show')"
                                            :active="route().current('bookingclosed.show')"
                                            v-if="can['view book extras']"
                                            >Closed Offices</DropdownLink
                                        >

                                        <DropdownLink
                                            :href="route('bookingdedicated.show')"
                                            :active="route().current('bookingdedicated.show')"
                                            v-if="can['view book extras']"
                                            >Dedicated Desks</DropdownLink
                                        >

                                        <DropdownLink
                                            :href="route('bookinghotdesk.show')"
                                            :active="route().current('bookinghotdesk.show')"
                                            v-if="can['view book extras']"
                                            >Hot Desks</DropdownLink
                                        >

                                        <DropdownLink
                                            :href="route('bookingvirtual.show')"
                                            :active="route().current('bookingvirtual.show')"
                                            v-if="can['view book extras']"
                                            >Virtual Offices</DropdownLink
                                        >

                                        <DropdownLink
                                            :href="route('bookingboardroom.show')"
                                            :active="route().current('bookingboardroom.show')"
                                            v-if="can['view book extras']"
                                            >Boardrooms</DropdownLink
                                        >
                                    </template>
                                </Dropdown>

                                <!-- User -->
                                <Dropdown
                                    align="right"
                                    width="48">
                                    <template #trigger>
                                        <div class="flex items-center space-x-1">
                                            <div class="text-sm font-medium text-primary">
                                                {{ $page.props.auth.user.name }}
                                            </div>
                                            <button
                                                class="flex items-center overflow-hidden transition rounded-full focus:outline-none hover:ring-2 hover:ring-gray-300">
                                                <img
                                                    src="/files_grits/user.png"
                                                    alt="User Avatar"
                                                    class="object-cover w-5 h-5 rounded-full" />
                                            </button>
                                        </div>
                                    </template>

                                    <template #content>
                                        <div class="space-y-1">
                                            <div class="text-sm text-gray-700 transition hover:bg-gray-50">
                                                <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                                            </div>
                                            <div class="text-sm text-gray-700 transition hover:bg-gray-50">
                                                <DropdownLink :href="route('companydetail.index')"
                                                    >Company Details</DropdownLink
                                                >
                                            </div>

                                            <div
                                                v-if="!can['manage settings']"
                                                class="text-sm text-gray-700 transition hover:bg-gray-50">
                                                <ResponsiveNavLink :href="route('user.invoice')"
                                                    >Invoices</ResponsiveNavLink
                                                >
                                            </div>
                                            <div class="text-sm text-gray-700 transition hover:bg-gray-50">
                                                <DropdownLink
                                                    :href="route('logout')"
                                                    method="post"
                                                    as="button"
                                                    >Log Out</DropdownLink
                                                >
                                            </div>
                                        </div>
                                    </template>
                                </Dropdown>

                                <!-- Settings -->
                                <Dropdown
                                    align="right"
                                    width="48"
                                    v-if="can['manage settings']">
                                    <template #trigger>
                                        <div class="flex items-center space-x-1">
                                            <button
                                                :class="
                                                    route().current('booking.offices') ||
                                                    route().current('booking.boardrooms') ||
                                                    route().current('virtual.home')
                                                        ? 'bg-primary rounded text-white font-semibold'
                                                        : 'text-gray-500 font-medium hover:text-gray-700 hover:bg-gray-100 rounded-md'
                                                "
                                                class="flex items-center overflow-hidden transition rounded-full focus:outline-none hover:ring-2 hover:ring-gray-300">
                                                <img
                                                    src="/files_grits/cog.png"
                                                    alt="Cog Avatar"
                                                    class="object-cover w-5 h-5 rounded-full" />
                                            </button>
                                        </div>
                                    </template>

                                    <template #content>
                                        <div class="space-y-1">
                                            <template v-if="can['manage settings']">
                                                <!-- products -->
                                                <Dropdown
                                                    nested
                                                    align="left"
                                                    width="48">
                                                    <template #trigger>
                                                        <div
                                                            :class="
                                                                route().current('admin.closedoffices') ||
                                                                route().current('admin.dedicateddesk') ||
                                                                route().current('admin.help-desks') ||
                                                                route().current('admin.virtual-offices') ||
                                                                route().current('admin.boardrooms')
                                                                    ? 'bg-primary rounded text-white font-semibold'
                                                                    : 'text-gray-500 font-medium hover:text-gray-300 hover:bg-gray-100 rounded-md'
                                                            "
                                                            class="flex items-center justify-between w-full px-4 py-2 text-sm text-gray-700 transition cursor-pointer hover:bg-gray-700">
                                                            Product Settings
                                                            <svg
                                                                class="w-4 h-4"
                                                                fill="currentColor"
                                                                viewBox="0 0 20 20">
                                                                <path
                                                                    fill-rule="evenodd"
                                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                        </div>
                                                    </template>
                                                    <template #content>
                                                        <DropdownLink
                                                            v-if="can['view closed offices']"
                                                            :href="route('admin.closedoffices')"
                                                            :active="route().current('admin.closedoffices')"
                                                            >Closed Offices</DropdownLink
                                                        >
                                                        <DropdownLink
                                                            v-if="can['view dedicated desks']"
                                                            :href="route('admin.dedicateddesk')"
                                                            :active="route().current('admin.dedicateddesk')"
                                                            >Dedicated Desks</DropdownLink
                                                        >
                                                        <DropdownLink
                                                            v-if="can['view hot desks']"
                                                            :href="route('admin.help-desks')"
                                                            :active="route().current('admin.help-desks')"
                                                            >Hot Desks</DropdownLink
                                                        >
                                                        <DropdownLink
                                                            v-if="can['view virtual offices']"
                                                            :href="route('admin.virtual-offices')"
                                                            :active="route().current('admin.virtual-offices')"
                                                            >Virtual Offices</DropdownLink
                                                        >
                                                        <DropdownLink
                                                            v-if="can['view boardrooms']"
                                                            :href="route('admin.boardrooms')"
                                                            :active="route().current('admin.boardrooms')"
                                                            >Boardrooms</DropdownLink
                                                        >
                                                    </template>
                                                </Dropdown>

                                                <!-- System Settings -->
                                                <Dropdown
                                                    nested
                                                    align="right"
                                                    width="48">
                                                    <template #trigger>
                                                        <div
                                                            v-if="can['view book extras']"
                                                            class="flex items-center justify-between w-full px-4 py-2 text-sm text-gray-700 transition cursor-pointer hover:bg-gray-700"
                                                            :class="
                                                                route().current('admin.locations') ||
                                                                route().current('admin.categories') ||
                                                                route().current('admin.amenities') ||
                                                                route().current('admin.extra.index') ||
                                                                route().current('admin.parking.index') ||
                                                                route().current('admin.discounts.index')
                                                                    ? 'bg-primary rounded text-white font-semibold'
                                                                    : 'text-500 font-medium hover:text-gray-300 hover:bg-gray-100 rounded-md'
                                                            ">
                                                            System Settings
                                                            <svg
                                                                class="w-4 h-4"
                                                                fill="currentColor"
                                                                viewBox="0 0 20 20">
                                                                <path
                                                                    fill-rule="evenodd"
                                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                        </div>
                                                    </template>
                                                    <template #content>
                                                        <DropdownLink
                                                            :href="route('admin.locations')"
                                                            :active="route().current('admin.locations')"
                                                            >Locations</DropdownLink
                                                        >
                                                        <DropdownLink
                                                            :href="route('admin.categories')"
                                                            :active="route().current('admin.categories')"
                                                            >Categories</DropdownLink
                                                        >

                                                        <DropdownLink
                                                            :href="route('admin.amenities')"
                                                            :active="route().current('admin.amenities')"
                                                            >Amenities</DropdownLink
                                                        >
                                                        <DropdownLink
                                                            :href="route('admin.extra.index')"
                                                            :active="route().current('admin.extra.index')"
                                                            >Extras Settings</DropdownLink
                                                        >
                                                        <DropdownLink
                                                            :href="route('admin.parking.index')"
                                                            :active="route().current('admin.parking.index')"
                                                            >Parking</DropdownLink
                                                        >
                                                        <DropdownLink
                                                            :href="route('admin.discounts.index')"
                                                            :active="route().current('admin.discounts.index')"
                                                            >Boardroom Discounts</DropdownLink
                                                        >
                                                    </template>
                                                </Dropdown>

                                                <!-- clients -->
                                                <Dropdown
                                                    nested
                                                    align="right"
                                                    width="48">
                                                    <template #trigger>
                                                        <div
                                                            v-if="can['view book extras']"
                                                            class="flex items-center justify-between w-full px-4 py-2 text-sm text-gray-700 transition cursor-pointer hover:bg-gray-700"
                                                            :class="
                                                                route().current('admin.clientinfor.index') ||
                                                                route().current('admin.clientrates.index') ||
                                                                route().current('admin.agreement.index')
                                                                    ? 'bg-primary rounded text-white font-semibold'
                                                                    : 'text-gray-500 font-medium hover:text-gray-300 hover:bg-gray-700 rounded-md'
                                                            ">
                                                            Clients
                                                            <svg
                                                                class="w-4 h-4"
                                                                fill="currentColor"
                                                                viewBox="0 0 20 20">
                                                                <path
                                                                    fill-rule="evenodd"
                                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                        </div>
                                                    </template>
                                                    <template #content>
                                                        <DropdownLink
                                                            :href="route('admin.clientinfor.index')"
                                                            :active="route().current('admin.clientinfor.index')"
                                                            >Clients Information</DropdownLink
                                                        >
                                                        <DropdownLink
                                                            :href="route('admin.clientrates.index')"
                                                            :active="route().current('admin.clientrates.index')"
                                                            >Discouted Rates</DropdownLink
                                                        >
                                                        <DropdownLink
                                                            :href="route('admin.agreement.index')"
                                                            :active="route().current('admin.agreement.index')"
                                                            >Agreements</DropdownLink
                                                        >
                                                    </template>
                                                </Dropdown>

                                                <!-- Extras -->
                                                <Dropdown
                                                    nested
                                                    align="right"
                                                    width="48">
                                                    <template #trigger>
                                                        <div
                                                            v-if="can['view book extras']"
                                                            class="flex items-center justify-between w-full px-4 py-2 text-sm text-gray-700 transition cursor-pointer hover:bg-gray-700"
                                                            :class="
                                                                route().current('admin.coffee.index') ||
                                                                route().current('admin.printing.index')
                                                                    ? 'bg-primary rounded text-white font-semibold'
                                                                    : 'text-gray-500 font-medium hover:text-gray-300 hover:bg-gray-100 rounded-md'
                                                            ">
                                                            Extras
                                                            <svg
                                                                class="w-4 h-4"
                                                                fill="currentColor"
                                                                viewBox="0 0 20 20">
                                                                <path
                                                                    fill-rule="evenodd"
                                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                        </div>
                                                    </template>
                                                    <template #content>
                                                        <DropdownLink
                                                            :href="route('admin.coffee.index')"
                                                            :active="route().current('admin.coffee.index')"
                                                            >Coffee</DropdownLink
                                                        >
                                                        <DropdownLink
                                                            :href="route('admin.printing.index')"
                                                            :active="route().current('admin.printing.index')"
                                                            >Printing</DropdownLink
                                                        >
                                                    </template>
                                                </Dropdown>

                                                <!-- hours -->
                                                <Dropdown
                                                    nested
                                                    align="right"
                                                    width="48">
                                                    <template #trigger>
                                                        <div
                                                            v-if="can['view book extras']"
                                                            class="flex items-center justify-between w-full px-4 py-2 text-sm text-gray-700 transition cursor-pointer hover:bg-gray-700"
                                                            :class="
                                                                route().current('admin.hours.index') ||
                                                                route().current('admin.boardroom_hours.index')
                                                                    ? 'bg-primary rounded text-white font-semibold'
                                                                    : 'text-gray-500 font-medium hover:text-gray-300 hover:bg-gray-100 rounded-md'
                                                            ">
                                                            Hours
                                                            <svg
                                                                class="w-4 h-4"
                                                                fill="currentColor"
                                                                viewBox="0 0 20 20">
                                                                <path
                                                                    fill-rule="evenodd"
                                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                        </div>
                                                    </template>
                                                    <template #content>
                                                        <DropdownLink
                                                            v-if="can['manage settings']"
                                                            :href="route('admin.hours.index')"
                                                            :active="route().current('admin.hours.index')"
                                                            >Free</DropdownLink
                                                        >
                                                        <DropdownLink
                                                            v-if="can['manage settings']"
                                                            :href="route('admin.boardroom_hours.index')"
                                                            :active="route().current('admin.boardroom_hours.index')"
                                                            >Normal</DropdownLink
                                                        >
                                                    </template>
                                                </Dropdown>
                                            </template>

                                            <DropdownLink
                                                v-if="can['manage settings']"
                                                :href="route('admin.notes.index')"
                                                :active="route().current('admin.notes.index')"
                                                class="flex items-center justify-between w-full px-4 py-2 text-sm text-gray-300 transition cursor-pointer hover:bg-gray-700"
                                                :class="
                                                    route().current('admin.notes.index')
                                                        ? 'bg-primary rounded text-white font-semibold'
                                                        : 'text-gray-500 hover:text-white hover:bg-gray-700 rounded-md'
                                                "
                                                >Notes</DropdownLink
                                            >

                                            <DropdownLink
                                                v-if="can['manage settings']"
                                                :href="route('admin.invoices.index')"
                                                :active="route().current('admin.invoices.index')"
                                                class="flex items-center justify-between w-full px-4 py-2 text-sm text-gray-300 transition cursor-pointer hover:bg-gray-700"
                                                :class="
                                                    route().current('admin.invoices.index')
                                                        ? 'bg-primary rounded text-white font-semibold'
                                                        : 'text-gray-500 hover:text-white hover:bg-gray-700 rounded-md'
                                                "
                                                >Invoices</DropdownLink
                                            >

                                            <DropdownLink
                                                v-if="can['manage settings']"
                                                :href="route('admin.manage.user')"
                                                :active="route().current('admin.manage.user')"
                                                class="flex items-center justify-between w-full px-4 py-2 text-sm transition cursor-pointer hover:bg-gray-700"
                                                :class="
                                                    route().current('admin.manage.user')
                                                        ? 'bg-primary rounded text-white font-semibold'
                                                        : 'text-gray-500 hover:text-white hover:bg-gray-700 rounded-md'
                                                ">
                                                Manage
                                            </DropdownLink>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger Menu (Mobile) -->
                        <div class="relative w-full sm:hidden flex items-center justify-end">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="p-2 text-gray-400 rounded-md hover:bg-gray-100 hover:text-gray-600 focus:outline-none">
                                <svg
                                    class="w-6 h-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path
                                        v-if="!showingNavigationDropdown"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                    <path
                                        v-else
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mobile Menu -->
                <div :class="[showingNavigationDropdown ? 'block' : 'hidden', 'sm:hidden fixed inset-0 z-50 bg-white']">
                    <div class="flex flex-col h-screen">
                        <div class="px-4 py-2 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <!-- Logo Block -->
                                <Link
                                    :href="route('dashboard')"
                                    class="flex items-center">
                                    <ApplicationLogo class="block w-auto h-12 text-gray-800 fill-current" />
                                </Link>

                                <!-- Close Button -->
                                <button
                                    @click="showingNavigationDropdown = false"
                                    aria-label="Close menu"
                                    class="p-2 transition rounded hover:bg-gray-100">
                                    <img
                                        src="/files_grits/close-x.svg"
                                        alt="Close"
                                        class="w-5 h-5 text-gray-500 hover:text-gray-700" />
                                </button>
                            </div>
                        </div>

                        <!-- Middle Content (Scrollable Items) -->
                        <div class="flex-grow px-4 py-4 space-y-3 overflow-y-auto">
                            <Dropdown
                                align="right"
                                width="48"
                                v-if="can['menu offices']">
                                <template #trigger>
                                    <div
                                        class="flex items-center justify-between w-full px-4 py-2 text-base font-medium text-gray-700 transition cursor-pointer hover:bg-gray-50">
                                        Offices
                                        <svg
                                            class="w-5 h-5"
                                            fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path
                                                fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </template>
                                <template #content>
                                    <DropdownLink
                                        :href="route('booking.offices')"
                                        v-if="can['menu offices']"
                                        >Grit Spaces</DropdownLink
                                    >

                                    <DropdownLink
                                        :href="route('booking.boardrooms')"
                                        v-if="can['menu offices']"
                                        >Boardrooms</DropdownLink
                                    >

                                    <DropdownLink
                                        :href="route('virtual.home')"
                                        v-if="can['menu offices']"
                                        >Virtual Office</DropdownLink
                                    >
                                </template>
                            </Dropdown>

                            <div
                                class="mb-1"
                                v-if="can['view book offices']">
                                <ResponsiveNavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                    :class="route().current('dashboard') ? 'text-primary border-bluemain/60' : ''"
                                    >Dashboard</ResponsiveNavLink
                                >

                                <ResponsiveNavLink
                                    :href="route('booking.offices')"
                                    :active="route().current('booking.offices')"
                                    :class="route().current('booking.offices') ? 'text-primary border-bluemain/60' : ''"
                                    >Grit Spaces</ResponsiveNavLink
                                >

                                <ResponsiveNavLink
                                    :href="route('booking.boardrooms')"
                                    :active="route().current('booking.boardrooms')"
                                    :class="
                                        route().current('booking.boardrooms') ? 'text-primary border-bluemain/60' : ''
                                    "
                                    >Boardrooms</ResponsiveNavLink
                                >

                                <ResponsiveNavLink
                                    :href="route('booking.extras')"
                                    :active="route().current('booking.extras')"
                                    :class="route().current('booking.extras') ? 'text-primary border-bluemain/60' : ''"
                                    >Virtual Offices</ResponsiveNavLink
                                >
                            </div>

                            <Dropdown
                                align="right"
                                width="48">
                                <template #trigger>
                                    <div
                                        class="flex items-center justify-between w-full px-4 py-2 text-base font-medium text-gray-700 transition cursor-pointer hover:bg-gray-50">
                                        Calendars
                                        <svg
                                            class="w-5 h-5"
                                            fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path
                                                fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </template>
                                <template #content>
                                    <DropdownLink
                                        :href="route('calendar.closed')"
                                        v-if="can['view book extras']"
                                        >Closed Offices</DropdownLink
                                    >

                                    <DropdownLink
                                        :href="route('calendar.dedicated')"
                                        v-if="can['view book extras']"
                                        >Dedicated Desks</DropdownLink
                                    >

                                    <DropdownLink
                                        :href="route('calendar.hotdesk')"
                                        v-if="can['view book extras']"
                                        >Hot Desks</DropdownLink
                                    >

                                    <DropdownLink
                                        :href="route('calendar.boardroom')"
                                        v-if="can['view book extras']"
                                        >Boardrooms</DropdownLink
                                    >
                                </template>
                            </Dropdown>

                            <Dropdown
                                align="right"
                                width="48">
                                <template #trigger>
                                    <div
                                        class="flex items-center justify-between w-full px-4 py-2 text-base font-medium text-gray-700 transition cursor-pointer hover:bg-gray-50">
                                        Bookings
                                        <svg
                                            class="w-5 h-5"
                                            fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path
                                                fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </template>

                                <template #content>
                                    <DropdownLink
                                        :href="route('bookingclosed.show')"
                                        v-if="can['view book extras']">
                                        Closed Offices
                                    </DropdownLink>

                                    <DropdownLink
                                        :href="route('bookingdedicated.show')"
                                        v-if="can['view book extras']">
                                        Dedicated Desks
                                    </DropdownLink>

                                    <DropdownLink
                                        :href="route('bookinghotdesk.show')"
                                        v-if="can['view book extras']">
                                        Hot Desks
                                    </DropdownLink>

                                    <DropdownLink
                                        :href="route('bookingvirtual.show')"
                                        v-if="can['view book extras']">
                                        Virtual Offices
                                    </DropdownLink>

                                    <DropdownLink
                                        :href="route('bookingboardroom.show')"
                                        v-if="can['view book extras']">
                                        Boardrooms
                                    </DropdownLink>
                                </template>
                            </Dropdown>

                            <!-- admins Settings -->
                            <template
                                v-if="can['manage settings']"
                                class="border-t-2">
                                <Dropdown
                                    nested
                                    align="right"
                                    width="48">
                                    <template #trigger>
                                        <div
                                            class="flex items-center justify-between w-full px-4 py-2 text-base font-medium text-gray-600 transition cursor-pointer text-start hover:bg-gray-50">
                                            Product Settings
                                            <svg
                                                class="w-5 h-5"
                                                fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </template>
                                    <template #content>
                                        <DropdownLink
                                            v-if="can['view closed offices']"
                                            :href="route('admin.closedoffices')"
                                            >Closed Offices</DropdownLink
                                        >
                                        <DropdownLink
                                            v-if="can['view dedicated desks']"
                                            :href="route('admin.dedicateddesk')"
                                            >Dedicated Desks</DropdownLink
                                        >
                                        <DropdownLink
                                            v-if="can['view help desks']"
                                            :href="route('admin.help-desks')"
                                            >Hot Desks</DropdownLink
                                        >
                                        <DropdownLink
                                            v-if="can['view virtual offices']"
                                            :href="route('admin.virtual-offices')"
                                            >Virtual Offices</DropdownLink
                                        >
                                        <DropdownLink
                                            v-if="can['view boardrooms']"
                                            :href="route('admin.boardrooms')"
                                            >Boardrooms</DropdownLink
                                        >
                                    </template>
                                </Dropdown>

                                <!-- System Settings -->
                                <Dropdown
                                    nested
                                    align="right"
                                    width="48">
                                    <template #trigger>
                                        <div
                                            v-if="can['view book extras']"
                                            class="flex items-center justify-between w-full px-4 py-2 text-base font-medium text-gray-700 transition cursor-pointer hover:bg-gray-50">
                                            System Settings
                                            <svg
                                                class="w-5 h-5"
                                                fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </template>
                                    <template #content>
                                        <DropdownLink :href="route('admin.locations')">Locations</DropdownLink>
                                        <DropdownLink :href="route('admin.categories')">Categories</DropdownLink>

                                        <DropdownLink :href="route('admin.amenities')">Amenities</DropdownLink>
                                        <DropdownLink :href="route('admin.extra.index')">Extra's</DropdownLink>
                                        <DropdownLink :href="route('admin.parking.index')">Parking</DropdownLink>
                                    </template>
                                </Dropdown>

                                <Dropdown
                                    nested
                                    align="right"
                                    width="48">
                                    <template #trigger>
                                        <div
                                            v-if="can['view book extras']"
                                            class="flex items-center justify-between w-full px-4 py-2 text-base font-medium text-gray-700 transition cursor-pointer hover:bg-gray-50">
                                            Clients
                                            <svg
                                                class="w-5 h-5"
                                                fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </template>
                                    <template #content>
                                        <DropdownLink :href="route('admin.clientinfor.index')"
                                            >Clients Information</DropdownLink
                                        >
                                        <DropdownLink :href="route('admin.clientrates.index')"
                                            >Clients Rates</DropdownLink
                                        >
                                        <DropdownLink :href="route('admin.agreement.index')">Agreements</DropdownLink>
                                    </template>
                                </Dropdown>

                                <!-- Extras -->
                                <Dropdown
                                    nested
                                    align="right"
                                    width="48">
                                    <template #trigger>
                                        <div
                                            v-if="can['view book extras']"
                                            class="flex items-center justify-between w-full px-4 py-2 text-base font-medium text-gray-700 transition cursor-pointer hover:bg-gray-50">
                                            Extras
                                            <svg
                                                class="w-5 h-5"
                                                fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </template>
                                    <template #content>
                                        <DropdownLink :href="route('admin.coffee.index')">Coffee</DropdownLink>
                                        <DropdownLink :href="route('admin.printing.index')">Printing</DropdownLink>
                                    </template>
                                </Dropdown>

                                <Dropdown
                                    nested
                                    align="right"
                                    width="48">
                                    <template #trigger>
                                        <div
                                            v-if="can['view book extras']"
                                            class="flex items-center justify-between w-full px-4 py-2 text-base font-medium text-gray-700 transition cursor-pointer hover:bg-gray-50">
                                            Hours
                                            <svg
                                                class="w-5 h-5"
                                                fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </template>
                                    <template #content>
                                        <DropdownLink :href="route('admin.coffee.index')">Free</DropdownLink>
                                        <DropdownLink :href="route('admin.printing.index')">Normal</DropdownLink>
                                    </template>
                                </Dropdown>

                                <ResponsiveNavLink
                                    v-if="can['manage settings']"
                                    :href="route('admin.notes.index')"
                                    >Notes</ResponsiveNavLink
                                >

                                <ResponsiveNavLink
                                    v-if="can['manage settings']"
                                    :href="route('admin.manage.user')"
                                    >Manage</ResponsiveNavLink
                                >

                                <ResponsiveNavLink
                                    v-if="can['manage settings']"
                                    :href="route('admin.invoices.index')"
                                    >Invoices</ResponsiveNavLink
                                >
                            </template>

                            <ResponsiveNavLink :href="route('profile.edit')">Profile</ResponsiveNavLink>
                        </div>

                        <!-- Anchored Bottom Item (Item 3) -->
                        <div class="px-4 py-1 bg-white border-t border-gray-300">
                            <div class="text-sm font-medium text-gray-700">
                                <Dropdown
                                    align="center"
                                    width="48">
                                    <template #trigger>
                                        <div
                                            class="flex items-center justify-between w-full px-4 py-1 text-sm font-medium text-gray-700 transition rounded-md cursor-pointer hover:bg-gray-100">
                                            <div class="flex space-x-2">
                                                <img
                                                    src="/files_grits/user.png"
                                                    alt="User Avatar"
                                                    class="object-cover w-5 h-5 rounded-full" />
                                                <span>{{ $page.props.auth.user.name }}</span>
                                            </div>
                                            <div class="text-sm text-gray-700 transition hover:bg-gray-50">
                                                <DropdownLink
                                                    :href="route('logout')"
                                                    method="post"
                                                    as="button"
                                                    class="inline-flex items-center text-xs font-semibold text-white rounded bg-bluemain hover:bg-bluemain/60 focus:bg-bluemain focus:ring-bluemain focus:ring-offset-2 active:bg-bluemain/60"
                                                    >Log Out</DropdownLink
                                                >
                                            </div>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                v-if="$slots.header"
                class="shadow">
                <div class="px-4 py-6 mx-auto text-lg max-w-7xl sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
