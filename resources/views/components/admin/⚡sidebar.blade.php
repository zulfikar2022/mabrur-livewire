<?php

use Livewire\Component;

new class () extends Component {
    public bool $mobileOpen = false;

    public function toggleSidebar()
    {
        $this->mobileOpen = !$this->mobileOpen;
    }
};
?>

<div>
    <div class="lg:hidden fixed top-4 left-4 z-50">
        <button wire:click="toggleSidebar" class="p-2 bg-indigo-900 text-white rounded-md focus:outline-none shadow-md">
            <i class="fa-solid {{ $mobileOpen ? 'fa-xmark' : 'fa-bars' }} text-xl w-6 text-center"></i>
        </button>
    </div>

    @if($mobileOpen)
        <div wire:click="toggleSidebar" class="lg:hidden fixed inset-0 bg-slate-900/60 z-40 transition-opacity"></div>
    @endif

    <aside class="fixed top-0 left-0 h-screen w-64 bg-indigo-950 text-slate-100 z-40 transform transition-transform duration-300 ease-in-out
        {{ $mobileOpen ? 'translate-x-0' : '-translate-x-full' }} lg:translate-x-0 border-r border-indigo-900 shadow-xl">
        
        <div class="h-16 flex items-center px-6 border-b border-indigo-900 bg-slate-950 font-bold text-lg text-blue-400">
            Admin Dashboard
        </div>

        <nav class="p-4 space-y-2 overflow-y-auto h-[calc(100vh-4rem)]">
            <a wire:navigate href="{{ route('admin.home') }}" class="flex items-center space-x-3 px-1 py-2  rounded-lg text-slate-300 hover:text-white font-bold {{ request()->routeIs('admin.home') ? 'bg-indigo-900 text-white' : '' }}">
                <span>Dashboard</span>
            </a>
            <p class="flex items-center space-x-3 px-1 py-2  rounded-lg text-slate-300 hover:text-white font-bold">
                <span>Category Management</span>
            </p>
            <a href="{{ route('admin.show-all-categories') }}" wire:navigate class="flex items-center space-x-3 px-4 py-2.5 rounded-lg hover:bg-indigo-900 text-slate-300 hover:text-white transition {{ request()->routeIs('admin.show-all-categories') ? 'bg-indigo-900 text-white' : '' }}">
                <span>All Categories</span>
            </a>
            
             <p class="flex items-center space-x-3 px-1 py-2  rounded-lg text-slate-300 hover:text-white font-bold">
                <span>Product Management</span>
            </p>
            <a href="{{ route('admin.show-all-products') }}" wire:navigate class="flex items-center space-x-3 px-4 py-2.5 rounded-lg hover:bg-indigo-900 text-slate-300 hover:text-white transition {{ request()->routeIs('admin.show-all-products') ? 'bg-indigo-900 text-white' : '' }}">
                <span>All Products</span>
            </a>
            <p class="flex items-center space-x-3 px-1 py-2  rounded-lg text-slate-300 hover:text-white font-bold">
                <span>User Management</span>
            </p>
            <a href="{{ route('admin.see-all-users') }}" wire:navigate class="flex items-center space-x-3 px-4 py-2.5 rounded-lg hover:bg-indigo-900 text-slate-300 hover:text-white transition {{ request()->routeIs('admin.see-all-users') ? 'bg-indigo-900 text-white' : '' }}">
                <span>All Users</span>
            </a>
            <a href="{{ route('admin.see-disabled-users') }}" wire:navigate class="flex items-center space-x-3 px-4 py-2.5 rounded-lg hover:bg-indigo-900 text-slate-300 hover:text-white transition {{ request()->routeIs('admin.see-disabled-users') ? 'bg-indigo-900 text-white' : '' }}">
                <span>Disabled Users</span>
            </a>
        </nav>
    </aside>
</div>