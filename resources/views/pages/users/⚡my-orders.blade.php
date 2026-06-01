<?php

use App\Models\OrderState;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-4">Order ID</th>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Items</th>
                        <th class="px-6 py-4">Total Cost</th>
                        <th class="px-6 py-4">Shipping</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Location</th>
                        <th class="px-6 py-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($this->orders as $order)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                {{ $order->id }}    
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                {{ $order->created_at->format('d M, Y') }}
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $order->orderedProducts->count() }} items
                            </td>
                            <td class="px-6 py-4 font-bold text-gray-900">
                                ৳{{ number_format($order->total_price, 2) }}
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                ৳{{ number_format($order->total_shipping_charge, 2) }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider 
                                    {{ $order->orderState->name === OrderState::DELIVERED ? 'bg-emerald-100 text-emerald-700' : 
                                       ($order->orderState->name === OrderState::CANCELLED ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">
                                    {{ str_replace('_', ' ', $order->orderState->name) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600 text-xs">
                                {{ $order->orderAddress->district->name ?? 'N/A' }}, 
                                {{ $order->orderAddress->upazila->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('user.order.details', $order) }}" class="text-blue-600 font-semibold hover:underline">Details</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                You haven't placed any orders yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>