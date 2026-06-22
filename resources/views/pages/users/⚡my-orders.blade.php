<?php

use App\Models\OrderState;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

new class () extends Component {
    // take the authenticated user and fetch their orders with related products and images
    public function getOrdersProperty()
    {
        return User::findOrFail(Auth::id())
            ->orders()
            ->with(['orderedProducts.product.productImages', 'orderedProducts.product.category'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
};
?>

<x-slot:title>
    My Orders - {{ config('app.name') }}
</x-slot>

<div class="max-w-6xl mx-auto p-4 md:p-6 my-6">
    <h2 class="text-2xl font-black text-gray-800 mb-6 flex items-center gap-2">
        <i class="fa-solid fa-box-open text-blue-600"></i>
        <span>My Order History</span>
    </h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($this->orders as $order)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow flex flex-col group">
                
                <div class="p-5 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                    <span class="font-black text-gray-900 text-lg">
                        Order Id: {{ $order->id }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider 
                        {{ $order->orderState->name === OrderState::DELIVERED ? 'bg-emerald-100 text-emerald-700' : 
                        ($order->orderState->name === OrderState::CANCELLED ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">
                        {{ str_replace('_', ' ', $order->orderState->name) }}
                    </span>
                </div>

                <div class="p-5 flex-grow space-y-3.5 text-sm text-gray-600">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 shrink-0">
                            <i class="fa-regular fa-calendar"></i>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-400 font-medium uppercase tracking-wider">Date</span>
                            <span class="font-semibold text-gray-800">{{ $order->created_at->format('d M, Y') }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 shrink-0">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-400 font-medium uppercase tracking-wider">Destination</span>
                            <span class="font-semibold text-gray-800">{{ $order->orderAddress->district->name ?? 'N/A' }}, {{ $order->orderAddress->upazila->name ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <div class="flex gap-4 pt-2">
                        <div class="bg-gray-50 rounded-lg px-3 py-2 flex-1 text-center">
                            <span class="block text-xs text-gray-500 mb-0.5">Items</span>
                            <span class="font-bold text-gray-800">{{ $order->orderedProducts->count() }}</span>
                        </div>
                        <div class="bg-gray-50 rounded-lg px-3 py-2 flex-1 text-center">
                            <span class="block text-xs text-gray-500 mb-0.5">Shipping</span>
                            <span class="font-bold text-gray-800">৳{{ number_format($order->total_shipping_charge, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="p-5 pt-0 mt-auto flex items-end justify-between">
                    <div>
                        <span class="block text-xs text-gray-500 font-medium mb-0.5">Total Cost</span>
                        <span class="font-black text-xl text-blue-600">৳{{ number_format($order->total_price + $order->total_shipping_charge, 2) }}</span>
                    </div>
                    
                    <a href="{{ route('user.order.details', $order->id) }}" wire:navigate 
                       class="px-5 py-2.5 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white font-bold text-sm rounded-xl transition-colors flex items-center gap-2 group-hover:bg-blue-600 group-hover:text-white">
                        <span>Details</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>

            </div>
        @empty
            <div class="col-span-full py-16 flex flex-col items-center justify-center bg-white rounded-2xl border border-dashed border-gray-200 text-center">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                    <i class="fa-solid fa-box-open text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-1">No orders found</h3>
                <p class="text-gray-500">You haven't placed any orders with us yet.</p>
                <a href="/" wire:navigate class="mt-6 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-colors">
                    Start Shopping
                </a>
            </div>
        @endforelse
    </div>
</div>