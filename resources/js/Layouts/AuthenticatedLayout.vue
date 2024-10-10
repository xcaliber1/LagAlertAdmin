<script setup>
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3'; // Import the Inertia usePage hook to access user data
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const showQrModal = ref(false); // State to handle QR code modal visibility
const qrCodePath = ref(''); // Variable to store the QR code path
const municipalityName = ref('');
const qrCodeId = ref('');

// Fetch user data from Inertia
const page = usePage();
const user = page.props.auth?.user || null; // Safely access the user object

onMounted(() => {
    if (user) {
        municipalityName.value = user.municipality || 'N/A';
        qrCodeId.value = user.qr_code_id || 'N/A';
        qrCodePath.value = user.qr_code_data || ''; // Use the Base64 data URL directly instead of the file path
    } else {
        console.warn('User data is not available. Please check the authentication setup.');
    }
});

</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav class="bg-red-400 border-b border-gray-100">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Dashboard
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <!-- Settings Dropdown -->
                            <div class="ml-3 relative flex items-center">
                                <!-- QR Code Icon -->
                                <button
                                    @click="showQrModal = true"
                                    class="mr-4 flex items-center justify-center rounded-full bg-gray-200 p-2 hover:bg-gray-300"
                                    title="View QR Code"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h5v5H3V4zM3 14h5v5H3v-5zM14 4h5v5h-5V4zM14 14h2v2h-2v-2zM18 18h2v2h-2v-2zM18 14h2v2h-2v-2zM14 18h2v2h-2v-2zM7 7h.01M17 7h.01M7 17h.01" />
                                    </svg>
                                </button>

                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                                            >
                                                {{ user ? user.name : 'Guest' }}
                                                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{ 'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{ 'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

<!-- QR Code Modal -->
<div v-if="showQrModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-lg font-semibold mb-4">Your Municipality QR Code in "{{ municipalityName }}"</h2>
        <img :src="qrCodePath" alt="QR Code" class="w-40 h-40 mx-auto" />
        <p class="mt-2 text-sm text-gray-600">ID Code: {{ qrCodeId }}</p>
        <button @click="showQrModal = false" class="mt-4 w-full px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
            Close
        </button>
    </div>
</div>

            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
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
