<?php

use App\Models\OrderState;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class () extends Component {
    public $user;
    public $name; // Used for the modal input

    public function mount()
    {
        $this->user = Auth::user();
        $this->name = $this->user->name;
    }

    public function updateName()
    {
        $this->validate(['name' => 'required|string|max:255']);

        $this->user->update(['name' => $this->name]);

        session()->flash('success', 'Profile updated successfully!');
        $this->dispatch('close-modal'); // Dispatch event to close Alpine modal
    }

    // ... inside your component class
    public function getStatsProperty()
    {
        // Total orders
        $totalOrders = $this->user->orders()->count();

        // Total products in cart (must be available)
        $cartItemsCount = $this->user->carts()
            ->whereHas('product', function ($query) {
                $query->where('is_available', true);
            })
            ->count();

        // Total spent on 'delivered' orders
        // We join with order_state to filter by 'delivered'
        $totalSpent = $this->user->orders()
            ->whereHas('orderState', function ($query) {
                $query->where('name', OrderState::DELIVERED);
            })
            ->sum('total_price');

        return [
            'total_orders'     => $totalOrders,
            'cart_count'       => $cartItemsCount,
            'total_spent'      => $totalSpent,
        ];
    }
};
?>
<x-slot:title>
    {{ $this->user->name }} - {{ config('app.name') }}
</x-slot>
<div class="max-w-xl mx-auto p-6" x-data="{ openNameModal: false }">
    
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-bold border border-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center text-center">
        <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-gray-50 shadow-inner mb-6">
            <img src="{{ $user->profile_image ? asset('storage/' . $user->profile_image) : asset('default-avatar.png') }}" 
                 class="w-full h-full object-cover">
        </div>

        <h2 class="text-2xl font-black text-gray-900">{{ $user->name }}</h2>
        <!-- <p class="text-gray-500 mb-6">{{ $user->email }}</p> -->

        <button @click="openNameModal = true" 
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-xl transition-colors">
            Update Name
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 my-6 ">
        <a href="{{ route('user.my.orders') }}" wire:navigate class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center">
            <span class="block text-xs font-bold text-gray-400 uppercase tracking-widest">Total Orders</span>
            <span class="text-3xl font-black text-gray-900 mt-2">{{ $this->stats['total_orders'] }}</span>
        </a>

        <a href="{{ route('user.cart', Auth::user()) }}" wire:navigate class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center">
            <span class="block text-xs font-bold text-gray-400 uppercase tracking-widest">Items in Cart</span>
            <span class="text-3xl font-black text-blue-600 mt-2">{{ $this->stats['cart_count'] }}</span>
        </a>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 text-center">
            <span class="block text-xs font-bold text-gray-400 uppercase tracking-widest">Total Spent</span>
            <span class="text-3xl font-black text-emerald-600 mt-2">৳{{ number_format($this->stats['total_spent'], 2) }}</span>
        </div>
    </div>

    <template x-teleport="body">
        <div x-show="openNameModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
             x-cloak>
            
            <div @click.outside="openNameModal = false" 
                 class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl space-y-4">
                <h3 class="text-lg font-black text-gray-900">Update Name</h3>
                
                <input type="text" wire:model="name" class="w-full rounded-lg border-gray-200 bg-gray-50 p-3">
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                <div class="flex gap-3 justify-end pt-4">
                    <button @click="openNameModal = false" class="px-4 py-2 text-gray-600">Cancel</button>
                    <button wire:click="updateName" 
                            @click="openNameModal = false"
                            class="px-6 py-2 bg-blue-600 text-white font-bold rounded-xl">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>