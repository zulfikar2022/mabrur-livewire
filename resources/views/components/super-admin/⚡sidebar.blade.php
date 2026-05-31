<?php

use Livewire\Component;

new class () extends Component {
    // Track whether the mobile drawer is open or closed
    public bool $mobileOpen = false;

    public function toggleSidebar()
    {
        $this->mobileOpen = !$this->mobileOpen;
    }
};
?>

<div>
    <div class="lg:hidden fixed top-4 left-4 z-50">
        <button wire:click="toggleSidebar" class="p-2 bg-slate-800 text-white rounded-md focus:outline-none shadow-md">
            <i class="fa-solid {{ $mobileOpen ? 'fa-xmark' : 'fa-bars' }} text-xl w-6 text-center"></i>
        </button>
    </div>

    @if($mobileOpen)
        <div wire:click="toggleSidebar" class="lg:hidden fixed inset-0 bg-black/50 z-40 transition-opacity"></div>
    @endif

    <aside class="fixed top-0 left-0 h-screen w-64 bg-slate-800 text-white z-40 transform transition-transform duration-300 ease-in-out
        {{ $mobileOpen ? 'translate-x-0' : '-translate-x-full' }} lg:translate-x-0 border-r border-slate-700 shadow-xl">
        
        <div class="h-16 flex items-center px-6 border-b border-slate-700 bg-slate-900 font-bold text-lg text-emerald-400">
            Super Admin Panel
        </div>

        <nav class="p-4 space-y-2 overflow-y-auto h-[calc(100vh-4rem)]">
            <a wire:navigate href="{{ route('super-admin.home') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-lg hover:bg-slate-700  {{ request()->routeIs('super-admin.home') ? 'bg-slate-700 text-white' : '' }}">
                <span>Super Admins</span>
            </a>
            <a wire:navigate href="{{ route('super-admin.admins') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white transition {{ request()->routeIs('super-admin.admins') ? 'bg-slate-700 text-white' : '' }}">
                <span>Admins</span>
            </a>
            <a wire:navigate href="{{ route('super-admin.general-users') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white transition {{ request()->routeIs('super-admin.general-users') ? 'bg-slate-700 text-white' : '' }}">
                <span>General Users</span>
            </a>

            <a wire:navigate href="{{ route('super-admin.disabled-users') }}" class="flex items-center space-x-3 px-4 py-2.5 rounded-lg hover:bg-slate-700 text-slate-300 hover:text-white transition {{ request()->routeIs('super-admin.disabled-users') ? 'bg-slate-700 text-white' : '' }}">
                <span>Disabled Users</span>
            </a>
             
        </nav>
    </aside>
</div>