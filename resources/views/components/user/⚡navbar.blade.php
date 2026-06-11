<?php

use App\Models\Category;
use App\Models\Cart; // Imported Cart model
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On; // Imported Livewire On Event Attribute Listener
use Livewire\Component;
use Livewire\Attributes\Reactive;

new class () extends Component {
    public $categories;

    public $time;

    public function mount()
    {
        $this->categories = Cache::remember('nav_categories', null, function () {
            return Category::orderBy('name')
                ->where('is_available', true)
                ->get()->toJson();
        });
        $this->categories = Category::hydrate(json_decode($this->categories, true));
    }

    #[On('cart-updated')]
    public function handleCartRefresh()
    {
        // Keeping this method blank is correct! It triggers the dynamic UI update cycle.
    }

    #[On('echo:demo-channel,DemoEvent')]
    public function getTime($event)
    {
        $this->time = $event['time'];
    }

    public function getCartCountProperty()
    {
        // If the visitor is anonymous, there are no items to count
        if (!Auth::check()) {
            return 0;
        }

        // Count unique cart line items matching all availability conditions
        return Cart::where('user_id', Auth::id())
            ->whereHas('product', function ($query) {
                // 1. The product itself must be marked as available
                $query->where('is_available', true)
                      // 2. The product's parent category must ALSO be marked as available
                      ->whereHas('category', function ($catQuery) {
                          $catQuery->where('is_available', true);
                      });
            })
            ->count();
    }
};
?>

<div x-data="{ openDrawer: false, openDropdown: false, openLogoutModal: false }" class="bg-blue-600 text-white shadow-md relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            
            <div class="shrink-0 flex items-center">
                <a href="{{ route('home') }}" wire:navigate class="font-bold text-xl tracking-wider">
                    <img src="{{ asset('storage/logos/site-logo.png') }}" alt="Site Logo" class="h-14 w-auto">
                </a>
            </div>
        <div class="flex items-center space-x-2 sm:space-x-4">
                
                <div class="hidden md:flex items-center space-x-2 mr-2">
                    <a href="{{ route('home') }}" wire:navigate class="hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium transition duration-150 {{ request()->routeIs('user.home') || request()->routeIs('guest.home') ? 'underline font-bold' : '' }}">Home</a>
                    
                    @auth
                        @foreach ($categories as $category )
                            @php
                                $isActive = request()->routeIs('user.category.products') && request()->route('categoryName') === $category->name;
                            @endphp
                            <a href="{{ route('user.category.products', $category->name) }}" wire:navigate class="hover:bg-blue-700 px-3 py-2 rounded-md text-sm  transition duration-150 {{ $isActive ? 'underline font-bold' : 'font-medium' }}">{{ $category->name }}</a>
                        @endforeach
                    @endauth
                    
                    @if (! auth()->check())
                        @foreach ($categories as $category )
                            @php
                                $isActive = request()->routeIs('guest.category.products') && request()->route('categoryName') === $category->name;
                            @endphp
                            <a href="{{ route('guest.category.products', $category->name) }}" wire:navigate class="hover:bg-blue-700 px-3 py-2 rounded-md text-sm  transition duration-150 {{ $isActive ? 'underline font-bold' : 'font-medium' }}">{{ $category->name }}</a>
                        @endforeach
                    @endif

                    @if (! auth()->check())
                        <a href="{{ route('login') }}" wire:navigate class="hover:bg-blue-700 px-3 py-2 rounded-md text-sm  transition duration-150 {{ request()->routeIs('login') ? 'underline font-bold' : 'font-medium' }}">Login</a>
                    @endif
                </div>

                @auth
                    <a href="{{ route('user.cart', Auth::id()) }}" wire:navigate class="relative p-2 hover:bg-blue-700 rounded-full transition duration-150 group flex items-center justify-center">
                        <i class="fa-solid fa-cart-shopping text-xl"></i>
                        
                        @if($this->cartCount > 0)
                            <span class="absolute -top-0.5 -right-0.5 bg-red-500 text-white font-extrabold text-[10px] h-5 w-5 rounded-full flex items-center justify-center border-2 border-blue-600 shadow-sm transition-transform group-hover:scale-110">
                                {{ $this->cartCount }}
                            </span>
                        @endif
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
                            <a href="{{ route('user.profile') }}" wire:navigate class="flex items-center space-x-2.5 px-4 py-2.5 text-sm text-blue-600 hover:bg-blue-50 font-semibold transition-colors">
                                <i class="fa-solid fa-user w-4 text-center"></i>
                                My Profile
                            </a>
                            <a href="{{ route('user.my.orders') }}" wire:navigate class="flex items-center space-x-2.5 px-4 py-2.5 text-sm text-blue-600 hover:bg-blue-50 font-semibold transition-colors">
                                <i class="fa-solid fa-shopping-bag w-4 text-center"></i>
                                My Orders
                            </a>
                            <button type="button" 
                                    @click="openLogoutModal = true; openDropdown = false"
                                    class="w-full flex items-center space-x-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 font-semibold transition-colors cursor-pointer">
                                <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
                                <span>Log Out</span>
                            </button>
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
    
    @if (!config('services.is_for_department'))
        <div class="bg-orange-500 text-white text-center text-xs py-1 px-4">
            <p>
                অর্ডার আপডেট করতে, ডিলিট করতে বা অন্য যেকোনো সমস্যায় কল দিন অথবা হোয়াটসঅ্যাপ করুন: <a href="tel:+8801234567890" class="underline font-bold">+8801677-520339</a>
            </p>        
        </div>
    @endif

    <template x-teleport="body">
        <div>
            <div x-show="openDrawer" 
                 x-transition:opacity
                 @click="openDrawer = false" 
                 class="fixed inset-0 bg-black bg-opacity-50 z-100 md:hidden" 
                 x-cloak>
            </div>

            <div x-show="openDrawer"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full"
                 class="fixed inset-y-0 left-0 w-64 bg-blue-700 text-white z-101 p-6 shadow-xl md:hidden"
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
                    <a href="{{ route('home') }}" wire:navigate class="hover:bg-blue-800 px-3 py-2 rounded-md text-base font-medium transition duration-150 flex items-center gap-2 {{ request()->routeIs('user.home') || request()->routeIs('guest.home') ? 'underline font-bold' : '' }}">
                         Home
                    </a>
                    
                    @auth
                        @foreach ($categories as $category )
                            @php
                                $isActive = request()->routeIs('user.category.products') && request()->route('categoryName') === $category->name;
                            @endphp
                            <a href="{{ route('user.category.products', $category->name) }}" wire:navigate class="hover:bg-blue-700 px-3 py-2 rounded-md text-sm  transition duration-150 {{ $isActive ? 'underline font-bold' : 'font-medium' }}">{{ $category->name }}</a>
                        @endforeach
                    @endauth
                    
                    @if (! auth()->check())
                        @foreach ($categories as $category )
                            @php
                                $isActive = request()->routeIs('guest.category.products') && request()->route('categoryName') === $category->name;
                            @endphp
                            <a href="{{ route('guest.category.products', $category->name) }}" wire:navigate class="hover:bg-blue-700 px-3 py-2 rounded-md text-sm  transition duration-150 {{ $isActive ? 'underline font-bold' : 'font-medium' }}">{{ $category->name }}</a>
                        @endforeach
                    @endif
                    
                    @if (! auth()->check())
                        <a href="{{ route('login') }}" wire:navigate class="hover:bg-blue-800 px-3 py-2 rounded-md text-base  transition duration-150 flex items-center gap-2 ">
                            <i class="fa-solid fa-arrow-right-to-bracket text-sm text-blue-300"></i> <span class="{{ request()->routeIs('login') ? 'underline font-bold' : 'font-medium' }}">Login</span>
                        </a>
                    @endif
                </nav>
            </div>
        </div>
    </template>

    <template x-teleport="body">
        <div x-show="openLogoutModal" 
            class="fixed inset-0 z-100 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-cloak>
            
            <div @click.outside="openLogoutModal = false"
                class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl space-y-4 border border-gray-100">
                <h3 class="text-lg font-black text-gray-900">Confirm Logout</h3>
                <p class="text-sm text-gray-500">Are you sure you want to log out of your account?</p>
                
                <div class="flex gap-3 justify-end pt-4">
                    <button @click="openLogoutModal = false" 
                            class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 rounded-xl transition-colors hover:cursor-pointer">
                        Cancel
                    </button>
                    <button @click="window.location.href = '{{ route('user.logout') }}'" 
                            class="px-6 py-2 text-sm font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors shadow-md hover:cursor-pointer">
                        Yes, Log Out
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>