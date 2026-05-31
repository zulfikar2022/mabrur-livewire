<?php

use App\Models\Category;
use Livewire\Component;

new class () extends Component {
    public $categories ;
    public function mount()
    {
        $this->categories = Category::orderBy('name')->where('is_available', true)->get();
    }
};
?>

<div x-data="{ openDrawer: false, openDropdown: false }" class="bg-blue-600 text-white shadow-md relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            
            <div class="shrink-0 flex items-center">
                <a href="{{ route('home') }}" wire:navigate class="font-bold text-xl tracking-wider">
                    MYLOGO
                </a>
            </div>

            <div class="flex items-center space-x-2 sm:space-x-4">
                
                <div class="hidden md:flex items-center space-x-6 mr-2">
                    <a href="{{ route('home') }}" wire:navigate class="hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium transition duration-150 {{ request()->routeIs('user.home') ? 'underline font-bold' : '' }}">Home</a>
                    @foreach ($categories as $category )
                        <a href="#" wire:navigate class="hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium transition duration-150 ">{{ $category->name }}</a>
                    @endforeach
                    @if (! auth()->check())
                        <a href="{{ route('login') }}" wire:navigate class="hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium transition duration-150">Login</a>
                    @endif
                </div>
                @auth
                    <a href="#" class="relative p-2 hover:bg-blue-700 rounded-full transition duration-150 group flex items-center justify-center">
                        <i class="fa-solid fa-cart-shopping text-xl"></i>
                        <span class="absolute -top-0.5 -right-0.5 bg-red-500 text-white font-extrabold text-[10px] h-5 w-5 rounded-full flex items-center justify-center border-2 border-blue-600 shadow-sm transition-transform group-hover:scale-110">
                            3
                        </span>
                    </a>
                @endauth

                @auth
                    <div class="relative flex items-center ml-1 sm:ml-2">
                        <button @click="openDropdown = !openDropdown" 
                                @click.outside="openDropdown = false" 
                                type="button" 
                                class="flex items-center space-x-1.5 focus:outline-none group p-1 rounded-full hover:bg-blue-700 transition duration-150">
                            
                            <div class="w-9 h-9 rounded-full overflow-hidden border-2 border-white group-hover:border-blue-200 transition bg-blue-500 flex items-center justify-center shadow-inner shrink-0">
                                @if(auth()->user()->profile_image)
                                    <img src="{{ asset('storage/' . auth()->user()->profile_image) }}" 
                                         alt="{{ auth()->user()->name }}" 
                                         class="w-full h-full object-cover">
                                @else
                                    <i class="fa-solid fa-user text-sm text-blue-200"></i>
                                @endif
                            </div>

                            <i class="fa-solid fa-chevron-down text-[10px] text-blue-200 group-hover:text-white transition-transform duration-200"
                               :class="openDropdown ? 'rotate-180 text-white' : ''"></i>
                        </button>

                        <div x-show="openDropdown" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 top-12 w-48 bg-white border border-gray-100 rounded-xl shadow-xl py-1.5 z-50 text-gray-700"
                             x-cloak>
                            
                            <div class="px-4 py-2 border-b border-gray-50 text-xs text-gray-400 uppercase font-bold tracking-wider">
                                Account Access
                            </div>
                            
                            <div class="px-4 py-2 border-b border-gray-50 max-w-full">
                                <p class="text-sm font-semibold text-gray-800 truncate">{{ auth()->user()->name }}</p>
                            </div>

                            <a href="{{ route('user.logout') }}" 
                               wire:navigate
                               class="flex items-center space-x-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 font-semibold transition-colors">
                                <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
                                <span>Log Out</span>
                            </a>
                        </div>
                    </div>
                @endauth

                <div class="flex md:hidden items-center">
                    <button @click="openDrawer = true" type="button" class="inline-flex items-center justify-center p-2 rounded-full hover:bg-blue-700 focus:outline-none transition duration-150">
                        <span class="sr-only">Open main navigation drawer</span>
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <div x-show="openDrawer" 
         x-transition:opacity
         @click="openDrawer = false" 
         class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden" 
         x-cloak>
    </div>

    <div x-show="openDrawer"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="fixed inset-y-0 left-0 w-64 bg-blue-700 text-white z-50 p-6 shadow-xl md:hidden"
         x-cloak>
        
        <div class="flex items-center justify-between mb-8">
            <span class="font-bold text-xl tracking-wider">MYLOGO</span>
            <button @click="openDrawer = false" type="button" class="rounded-full p-2 hover:bg-blue-800 focus:outline-none transition duration-150">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav class="flex flex-col space-y-3">
            <a href="{{ route('home') }}" wire:navigate class="hover:bg-blue-800 px-3 py-2 rounded-md text-base font-medium transition duration-150 flex items-center gap-2 {{ request()->routeIs('user.home') ? 'underline font-bold' : '' }}">
                 Home
            </a>
            
            @if (! auth()->check())
                <a href="{{ route('login') }}" wire:navigate class="hover:bg-blue-800 px-3 py-2 rounded-md text-base font-medium transition duration-150 flex items-center gap-2">
                    <i class="fa-solid fa-arrow-right-to-bracket text-sm text-blue-300"></i> Login
                </a>
            @endif
        </nav>
    </div>
</div>