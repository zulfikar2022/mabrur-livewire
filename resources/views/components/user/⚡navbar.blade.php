<?php

use Livewire\Component;

new class () extends Component {
    // You can add properties or logout logic here if needed
};
?>

<div x-data="{ open: false }" class="bg-blue-600 text-white shadow-md relative">
    <!-- Primary Navigation Bar -->
    <div class="max-w-7xl mx-auto px-5 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            
            <!-- Left Side: Logo -->
            <div class="shrink-0 flex items-center">
                <a href="{{ route('home') }}" wire:navigate class="font-bold text-xl tracking-wider">
                    MYLOGO
                </a>
            </div>

            <!-- Right Side: Desktop Nav Items (Hidden on Mobile) -->
            <div class="hidden md:flex items-center space-x-6">
                <a href="{{ route('home') }}" wire:navigate class="hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium transition duration-150">Home</a>
                
                @if (! auth()->check())
                    <a wire:navigate href="{{ route('login') }}" class="hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium transition duration-150">Login</a>
                @endif

                @auth
                    <a wire:navigate href="{{ route('user.logout') }}" class="hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium transition duration-150">Logout</a>
                @endauth
            </div>

            <!-- Hamburger Menu Icon (Hidden on Desktop) -->
            <div class="flex md:hidden">
                <button @click="open = true" type="button" class="inline-flex items-center justify-center p-2 rounded-md hover:bg-blue-700 focus:outline-none transition duration-150" aria-controls="mobile-menu" aria-expanded="false">
                    <span class="sr-only">Open main menu</span>
                    <!-- Hamburger Icon -->
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Background Overlay for Mobile Drawer -->
    <div x-show="open" 
         x-transition:opacity
         @click="open = false" 
         class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden" 
         x-cloak>
    </div>

    <!-- Mobile Drawer (Slides in from the Left) -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="fixed inset-y-0 left-0 w-64 bg-blue-700 text-white z-50 p-6 shadow-xl md:hidden"
         x-cloak>
        
        <!-- Close Button inside Drawer -->
        <div class="flex items-center justify-between mb-8">
            <span class="font-bold text-xl tracking-wider">MYLOGO</span>
            <button @click="open = false" type="button" class="rounded-md p-2 hover:bg-blue-800 focus:outline-none transition duration-150">
                <!-- Close X Icon -->
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Mobile Nav Links -->
        <nav class="flex flex-col space-y-4">
            <a href="{{ route('home') }}" wire:navigate class="hover:bg-blue-800 px-3 py-2 rounded-md text-base font-medium transition duration-150">Home</a>
            @if (! auth()->check())
                <a href="{{ route('login') }}" wire:navigate class="hover:bg-blue-800 px-3 py-2 rounded-md text-base font-medium transition duration-150">Login</a>
            @endif
            @auth
                <a href="{{ route('user.logout') }}" wire:navigate class="hover:bg-blue-800 px-3 py-2 rounded-md text-base font-medium transition duration-150">Logout</a>
            @endauth

        </nav>
    </div>
</div>