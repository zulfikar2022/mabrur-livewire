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
                <span>Order Management</span>
            </p>
            <a href="{{ route('admin.pending-orders') }}" wire:navigate class="flex items-center space-x-3 px-4 py-2.5 rounded-lg hover:bg-indigo-900 text-slate-300 hover:text-white transition {{ request()->routeIs('admin.pending-orders') ? 'bg-indigo-900 text-white' : '' }}">
                <span>Pending Orders</span>
            </a>
            <a href="{{ route('admin.approved-orders') }}" wire:navigate class="flex items-center space-x-3 px-4 py-2.5 rounded-lg hover:bg-indigo-900 text-slate-300 hover:text-white transition {{ request()->routeIs('admin.approved-orders') ? 'bg-indigo-900 text-white' : '' }}">
                <span>Approved Orders</span>
            </a>
            <a href="{{ route('admin.shipped-orders') }}" wire:navigate class="flex items-center space-x-3 px-4 py-2.5 rounded-lg hover:bg-indigo-900 text-slate-300 hover:text-white transition {{ request()->routeIs('admin.shipped-orders') ? 'bg-indigo-900 text-white' : '' }}">
                <span>Shipped Orders</span>
            </a>
            <a href="{{ route('admin.delivered-orders') }}" wire:navigate class="flex items-center space-x-3 px-4 py-2.5 rounded-lg hover:bg-indigo-900 text-slate-300 hover:text-white transition {{ request()->routeIs('admin.delivered-orders') ? 'bg-indigo-900 text-white' : '' }}">
                <span>Delivered Orders</span>
            </a>
            <a href="{{ route('admin.all-orders') }}" wire:navigate>
                <span class="flex items-center space-x-3 px-4 py-2.5 rounded-lg hover:bg-indigo-900 text-slate-300 hover:text-white transition {{ request()->routeIs('admin.all-orders') ? 'bg-indigo-900 text-white' : '' }}">
                    <span>All Orders</span>
                </span>
            </a>

            <a href="{{ route('admin.cancelled-orders') }}" wire:navigate class="flex items-center space-x-3 px-4 py-2.5 rounded-lg hover:bg-indigo-900 text-red-600 hover:text-red-500 transition {{ request()->routeIs('admin.cancelled-orders') ? 'bg-indigo-900 text-red-600' : '' }}">
                <span>Cancelled Orders</span>
            </a>
            <a href="{{ route('admin.delivery-failed-orders') }}" wire:navigate class="flex items-center space-x-3 px-4 py-2.5 rounded-lg hover:bg-indigo-900 text-red-600 hover:text-red-500 transition {{ request()->routeIs('admin.delivery-failed-orders') ? 'bg-indigo-900 text-red-600' : '' }}">
                <span>Delivery Failed Orders</span>
            </a>
            <a href="{{ route('admin.returned-orders') }}" wire:navigate class="flex items-center space-x-3 px-4 py-2.5 rounded-lg hover:bg-indigo-900 text-red-600 hover:text-red-500 transition {{ request()->routeIs('admin.returned-orders') ? 'bg-indigo-900 text-red-600' : '' }}">
                <span>Returned Orders</span>
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