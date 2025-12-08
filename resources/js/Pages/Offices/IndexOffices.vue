<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

</script>

<template>
    <GuestLayout>
        <Head title="Services" />
        
        <!-- Header Section -->
       <section class="w-full bg-center bg-cover" style="background-image: url('/files_grits/grit_services.jpg');">
            <div class="py-24 text-center">
                <h1 class="text-4xl font-bold text-white">Offices</h1>
            </div>
        </section>

        <div class="w-full py-12">
            <div class="container mx-auto">
                <!-- Filter Section -->
                <div class="mb-10">
                <ul class="flex justify-center space-x-6">
                    <li>
                    <a href="https://parkofideas.com/picozo/demo/catalog/" class="text-blue-500 hover:text-blue-700">All</a>
                    </li>
                    <li>
                    <a href="https://parkofideas.com/picozo/demo/catalog_type/freelancers/" class="text-blue-500 hover:text-blue-700">Freelancers</a>
                    </li>
                    <li>
                    <a href="https://parkofideas.com/picozo/demo/catalog_type/company/" class="text-blue-500 hover:text-blue-700">Company</a>
                    </li>
                    <li>
                    <a href="https://parkofideas.com/picozo/demo/catalog_type/laboratory/" class="text-blue-500 hover:text-blue-700">Laboratory</a>
                    </li>
                    <li>
                    <a href="https://parkofideas.com/picozo/demo/catalog_type/meetings/" class="text-blue-500 hover:text-blue-700">Meetings</a>
                    </li>
                    <li>
                    <a href="https://parkofideas.com/picozo/demo/catalog_type/workshop/" class="text-blue-500 hover:text-blue-700">Workshop</a>
                    </li>
                </ul>
                </div>

                <!-- Catalog Grid -->
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">

                <!-- Catalog Item -->
                <div class="overflow-hidden bg-white rounded-lg shadow-lg">
                    <a href="https://parkofideas.com/picozo/demo/catalog/the-juchiat-spaces-2/?type=226">
                    <img src="/files_grits/grit_services.jpg" alt="The Juchiat Spaces" class="object-cover w-full h-56">
                    </a>
                    <div class="p-6">
                        <div class="text-xl font-semibold text-gray-800">
                            <a href="https://parkofideas.com/picozo/demo/catalog/the-juchiat-spaces-2/?type=226">The Juchiat Spaces</a>
                        </div>
                        <table class="mt-4 text-sm text-gray-600 table-auto">
                            <tbody>
                                <tr class="flex justify-end">
                                <td class="font-semibold">Work space:</td>
                                <td class="">From $250 / Month</td>
                                </tr>
                                <tr>
                                <td class="font-semibold">Separate office:</td>
                                <td>From $80 / Month</td>
                                </tr>
                                <tr>
                                <td class="font-semibold">Dedicated Desk:</td>
                                <td>From $40 / Month</td>
                                </tr>
                                <tr>
                                <td class="font-semibold">Meeting Rooms:</td>
                                <td>From $35 / Month</td>
                                </tr>
                                <tr>
                                <td class="font-semibold">Virtual Office:</td>
                                <td>From $450 / Month</td>
                                </tr>
                            </tbody>
                        </table>

                        <a href="/bookings" class="block px-4 py-2 mt-4 text-center text-white bg-gray-500 rounded-lg hover:bg-blue-700">
                            View more
                        </a>
                    </div>
                </div>

                <!-- Catalog Item -->
                <div class="overflow-hidden bg-white rounded-lg shadow-lg">
                    <a href="https://parkofideas.com/picozo/demo/catalog/the-fairway-business-park-2/?type=226">
                    <img src="/files_grits/office 2.jpg" alt="The Fairway Business Park" class="object-cover w-full h-56">
                    </a>
                    <div class="p-6">
                    <div class="text-xl font-semibold text-gray-800">
                        <a href="https://parkofideas.com/picozo/demo/catalog/the-fairway-business-park-2/?type=226">The Fairway Business Park</a>
                    </div>
                    <ul class="mt-4 space-y-2 text-sm text-gray-600">
                        <li><span class="font-semibold">Work space:</span> From $250 / Month</li>
                        <li><span class="font-semibold">Separate office:</span> From $80 / Month</li>
                        <li><span class="font-semibold">Dedicated Desk:</span> From $40 / Month</li>
                        <li><span class="font-semibold">Meeting Rooms:</span> From $35 / Month</li>
                        <li><span class="font-semibold">Virtual Office:</span> From $450 / Month</li>
                    </ul>
                    <a href="https://parkofideas.com/picozo/demo/catalog/the-fairway-business-park-2/?type=226" class="inline-block px-4 py-2 mt-4 text-white bg-blue-500 rounded-lg hover:bg-blue-700">
                        View more
                    </a>
                    </div>
                </div>

                <!-- Catalog Item -->
                <div class="overflow-hidden bg-white rounded-lg shadow-lg">
                    <a href="https://parkofideas.com/picozo/demo/catalog/the-juchiat-spaces/?type=226">
                    <img src="/files_grits/grit_services.jpg" alt="The Juchiat Spaces" class="object-cover w-full h-56">
                    </a>
                    <div class="p-6">
                    <div class="text-xl font-semibold text-gray-800">
                        <a href="https://parkofideas.com/picozo/demo/catalog/the-juchiat-spaces/?type=226">The Juchiat Spaces</a>
                    </div>
                    <ul class="mt-4 space-y-2 text-sm text-gray-600">
                        <li><span class="font-semibold">Work space:</span> From $250 / Month</li>
                        <li><span class="font-semibold">Separate office:</span> From $80 / Month</li>
                        <li><span class="font-semibold">Dedicated Desk:</span> From $40 / Month</li>
                        <li><span class="font-semibold">Meeting Rooms:</span> From $35 / Month</li>
                        <li><span class="font-semibold">Virtual Office:</span> From $450 / Month</li>
                    </ul>
                    <a href="https://parkofideas.com/picozo/demo/catalog/the-juchiat-spaces/?type=226" class="inline-block px-4 py-2 mt-4 text-white bg-blue-500 rounded-lg hover:bg-blue-700">
                        View more
                    </a>
                    </div>
                </div>


                </div>
            </div>
        </div>


    </GuestLayout>
</template>