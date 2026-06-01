<?php

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class () extends Component {
    public $order;

    public function mount(Order $order)
    {
        // a user can see only their own order details, so we check if the order belongs to the authenticated user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to order details.');
        }

        $this->order = $order->load(['orderedProducts','orderedProducts.product.productImages', 'orderedProducts.product.category', 'orderAddress.district', 'orderAddress.upazila']);
    }
};
?>


<x-slot:title>
    Order Details - {{ config('app.name') }}
</x-slot>

<div class="max-w-5xl mx-auto p-4 md:p-6 my-6">
    <a href="{{ route('user.my.orders', Auth::user()) }}" wire:navigate class="text-blue-500 hover:text-blue-700 font-medium">
    <i class="fas fa-arrow-left mr-2"></i>
    Go To Orders
    </a>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-black text-gray-800">Order #{{ $order->id }}</h2>
                <p class="text-sm text-gray-500">Placed on {{ $order->created_at->format('F d, Y, h:i A') }}</p>
            </div>
            <div class="px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider bg-blue-100 text-blue-700">
                {{ str_replace('_', ' ', $order->orderState->name) }}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-gray-800 mb-4">Ordered Items</h3>
                <div class="space-y-4">
                    @foreach($order->orderedProducts as $item)
                        <div class="flex items-center gap-4 border-b pb-4 last:border-0 last:pb-0">
                            <div class="w-16 h-16 rounded-lg bg-gray-50 border overflow-hidden shrink-0">
                                @php $img = $item->product->productImages->first(); @endphp
                                @if($img)
                                    <img src="{{ asset('storage/' . $img->image_link) }}" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-900">{{ $item->product->name }}</h4>
                                <p class="text-xs text-gray-500">{{ $item->product->category->name }}</p>
                                <p class="text-xs font-bold">{{ $item->quantity }} <span class="font-light">{{ $item->product->sell_by_piece ? 'piece':'kg' }}</span> </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900">{{ $item->quantity }} x ৳{{ number_format($item->unit_price, 2) }}</p>
                                <p class="text-base font-black text-blue-600">৳{{ number_format($item->price, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-gray-800 mb-4">Delivery Address</h3>
                <div class="text-sm text-gray-600 space-y-2">
                    <p class="font-medium text-gray-900">{{ $order->orderAddress->address }}</p>
                    <p>{{ $order->orderAddress->upazila->name }}, {{ $order->orderAddress->district->name }}</p>
                    <div class="pt-4 border-t mt-4 space-y-1">
                        <p class="text-xs text-gray-400 uppercase font-bold">Contact</p>
                        <p class="font-semibold">{{ $order->orderAddress->phone }}</p>
                        @if($order->orderAddress->second_phone)
                            <p class="font-semibold">{{ $order->orderAddress->second_phone }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex justify-between text-sm text-gray-600 mb-2">
                    <span>Subtotal</span>
                    <span>৳{{ number_format($order->total_price, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm text-gray-600 mb-4">
                    <span>Shipping</span>
                    <span>৳{{ number_format($order->total_shipping_charge, 2) }}</span>
                </div>
                <div class="flex justify-between font-bold text-lg border-t pt-4 text-gray-900">
                    <span>Total</span>
                    <span class="text-blue-600">৳{{ number_format($order->total_price + $order->total_shipping_charge, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>