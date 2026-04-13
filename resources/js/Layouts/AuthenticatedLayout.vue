<script setup>
import { ref, computed, onMounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

const page = usePage();
const can = page.props.can || {};
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
                                    >Dashboard</NavLink
                                >

                                <NavLink
                                    :href="route('booking.offices')"
                                    v-if="can['view book offices']"
                                    >Grit Spaces</NavLink
                                >

                                <NavLink
                                    :href="route('booking.boardrooms')"
                                    v-if="can['view book boardrooms']"
                                    >Boardrooms</NavLink
                                >

                                <NavLink
                                    :href="route('virtual.home')"
                                    v-if="can['view book boardrooms']"
                                    >Virtual Office</NavLink
                                >
                            </div>
                        </div>

                        <!-- Right Section: User Avatar -->
                        <div class="items-center hidden space-x-2 sm:flex">
                            <div class="items-center hidden space-x-6 sm:flex">
                                <!-- Calendars -->
                                <Dropdown
                                    align="right"
                                    width="48"
                                    v-if="can['view book offices']">
                                    <template #trigger>
                                        <button
                                            class="flex items-center gap-1 text-sm font-medium text-gray-600 transition hover:text-gray-800 focus:outline-none">
                                            Calendars
                                            <svg
                                                class="w-4 h-4"
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

                                <!-- Bookings -->
                                <Dropdown
                                    align="right"
                                    width="48"
                                    v-if="can['view book offices']">
                                    <template #trigger>
                                        <button
                                            class="flex items-center gap-1 text-sm font-medium text-gray-600 transition hover:text-gray-800 focus:outline-none">
                                            Bookings
                                            <svg
                                                class="w-4 h-4"
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
                                            v-if="can['view book extras']"
                                            >Closed Offices</DropdownLink
                                        >

                                        <DropdownLink
                                            :href="route('bookingdedicated.show')"
                                            v-if="can['view book extras']"
                                            >Dedicated Desks</DropdownLink
                                        >

                                        <DropdownLink
                                            :href="route('bookinghotdesk.show')"
                                            v-if="can['view book extras']"
                                            >Hot Desks</DropdownLink
                                        >
                                        <DropdownLink
                                            :href="route('bookingvirtual.show')"
                                            v-if="can['view book extras']"
                                            >Virtual Offices</DropdownLink
                                        >
                                        <DropdownLink
                                            :href="route('bookingboardroom.show')"
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
                                                    align="right"
                                                    width="48">
                                                    <template #trigger>
                                                        <div
                                                            class="flex items-center justify-between w-full px-4 py-2 text-sm text-gray-700 transition cursor-pointer hover:bg-gray-50">
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
                                                            >Closed Offices</DropdownLink
                                                        >
                                                        <DropdownLink
                                                            v-if="can['view dedicated desks']"
                                                            :href="route('admin.dedicateddesk')"
                                                            >Dedicated Desks</DropdownLink
                                                        >
                                                        <DropdownLink
                                                            v-if="can['view hot desks']"
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
                                                            class="flex items-center justify-between w-full px-4 py-2 text-sm text-gray-700 transition cursor-pointer hover:bg-gray-50">
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
                                                        <DropdownLink :href="route('admin.locations')"
                                                            >Locations</DropdownLink
                                                        >
                                                        <DropdownLink :href="route('admin.categories')"
                                                            >Categories</DropdownLink
                                                        >

                                                        <DropdownLink :href="route('admin.amenities')"
                                                            >Amenities</DropdownLink
                                                        >
                                                        <DropdownLink :href="route('admin.extra.index')"
                                                            >Extras Settings</DropdownLink
                                                        >
                                                        <DropdownLink :href="route('admin.parking.index')"
                                                            >Parking</DropdownLink
                                                        >
                                                        <DropdownLink :href="route('admin.discounts.index')"
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
                                                            class="flex items-center justify-between w-full px-4 py-2 text-sm text-gray-700 transition cursor-pointer hover:bg-gray-50">
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
                                                        <DropdownLink :href="route('admin.clientinfor.index')"
                                                            >Clients Information</DropdownLink
                                                        >
                                                        <DropdownLink :href="route('admin.clientrates.index')"
                                                            >Discouted Rates</DropdownLink
                                                        >
                                                        <DropdownLink :href="route('admin.agreement.index')"
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
                                                            class="flex items-center justify-between w-full px-4 py-2 text-sm text-gray-700 transition cursor-pointer hover:bg-gray-50">
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
                                                        <DropdownLink :href="route('admin.coffee.index')"
                                                            >Coffee</DropdownLink
                                                        >
                                                        <DropdownLink :href="route('admin.printing.index')"
                                                            >Printing</DropdownLink
                                                        >
                                                    </template>
                                                </Dropdown>
                                                <Dropdown
                                                    nested
                                                    align="right"
                                                    width="48">
                                                    <template #trigger>
                                                        <div
                                                            v-if="can['view book extras']"
                                                            class="flex items-center justify-between w-full px-4 py-2 text-sm text-gray-700 transition cursor-pointer hover:bg-gray-50">
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
                                                            >Free</DropdownLink
                                                        >
                                                        <DropdownLink
                                                            v-if="can['manage settings']"
                                                            :href="route('admin.boardroom_hours.index')"
                                                            >Normal</DropdownLink
                                                        >
                                                    </template>
                                                </Dropdown>
                                            </template>

                                            <!-- Notes -->

                                            <DropdownLink
                                                v-if="can['manage settings']"
                                                :href="route('admin.notes.index')"
                                                >Notes</DropdownLink
                                            >

                                            <DropdownLink
                                                v-if="can['manage settings']"
                                                :href="route('admin.invoices.index')"
                                                >Invoices</DropdownLink
                                            >
                                            <DropdownLink
                                                v-if="can['manage settings']"
                                                :href="route('admin.manage.user')"
                                                >Manage</DropdownLink
                                            >
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
                            <div class="mb-1">
                                <ResponsiveNavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                    >Dashboard</ResponsiveNavLink
                                >

                                <ResponsiveNavLink :href="route('booking.offices')">Grit Spaces</ResponsiveNavLink>
                                <ResponsiveNavLink :href="route('booking.boardrooms')">Boardrooms</ResponsiveNavLink>
                                <ResponsiveNavLink :href="route('booking.extras')">Virtual Offices</ResponsiveNavLink>
                            </div>

                            <Dropdown
                                align="right"
                                width="48"
                                v-if="can['view book offices']">
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
                            <template v-if="can['manage settings']">
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
