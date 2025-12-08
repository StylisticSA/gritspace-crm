<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

</script>

<template>
    <GuestLayout>
        <Head title="Bookings"/>
        <!-- Header Section -->
        <section class="w-full bg-center bg-cover" style="background-image: url('/files_grits/grit_services.jpg');">
            <div class="py-24 text-center">
                <h1 class="text-4xl font-bold text-white">Offices</h1>
            </div>
        </section>
        <section>
            <div class="max-w-screen-xl px-4 py-16 mx-auto">
                <div class="grid grid-cols-1 gap-12 lg:grid-cols-2">
        
                    <!-- Image Gallery -->
                    <div class="space-y-4">
                        <div class="flex gap-3 overflow-x-auto">
                            <img
                                class="w-full max-w-lg rounded shadow"
                                src="https://placehold.co/570x428?text=Placeholder"
                                alt="Placeholder image"
                                />
                        </div>
                    </div>
        
                    <!-- Details Section -->
                    <div>
                        <h1 class="mb-6 text-3xl font-bold">The Prestige Building</h1>
        
                        <table class="w-full mb-6 text-left">
                            <tbody>
                            <tr class="py-2 border-b">
                                <td class="font-semibold">Work space:</td>
                                <td>From $250 per Person / Month</td>
                            </tr>
                            <tr class="py-2 border-b">
                                <td class="font-semibold">Separate office:</td>
                                <td>From $80 per Person / Month</td>
                            </tr>
                            <tr class="py-2 border-b">
                                <td class="font-semibold">Dedicated Desk:</td>
                                <td>From $40 / Month</td>
                            </tr>
                            <tr class="py-2 border-b">
                                <td class="font-semibold">Meeting Rooms:</td>
                                <td>From $35 per Person / Month</td>
                            </tr>
                            <tr class="py-2 border-b">
                                <td class="font-semibold">Virtual Office:</td>
                                <td>From $450 / Month</td>
                            </tr>
                            </tbody>
                        </table>
        
                        <div class="mb-6 text-gray-700">
                            <p class="mb-4">
                            Third brought were rule. Greater they’re second created make man wherein fifth earth also was. Dominion them you’re two place dry without you, lights that also seas greater earth.
                            </p>
                            <p>
                            Years god be which don’t divide bring replenish wherein dry light were multiply image gathered herb land called a creepeth, deep abundantly lights i Have third behold waters fruit fowl saw rule seasons which.
                            </p>
                        </div>
                    
                    
                    </div>
               
        
                </div>
                <div>
                    <div class="mb-6 aspect-video">
                        <iframe class="w-full h-full rounded shadow" loading="lazy" src="https://maps.google.com/maps?q=London%20Eye%2C%20London%2C%20United%20Kingdom&amp;t=m&amp;z=10&amp;output=embed&amp;iwloc=near" title="London Eye, London, United Kingdom"></iframe>
                    </div>
        
                    <a href="#" class="inline-flex items-center px-6 py-3 text-white transition bg-blue-600 rounded hover:bg-blue-700">BOOK NOW <i class="ml-2">→</i></a>
                </div>
            </div>
        </section>
    </GuestLayout>

</template>