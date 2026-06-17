<?php

use App\Models\Order;
use App\Models\OrderState;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component {
    public Order $order;
    public $orderStates;
    public $selectedState;
    public $userOrderStats = [];

    public function mount(Order $order)
    {
        // 1. Eager load all necessary relationships for the view
        $this->order = $order->load([
            'user.orders.orderState', // Loaded to calculate user stats efficiently
            'orderedProducts.product.productImages',
            'orderAddress.division',
            'orderAddress.district',
            'orderAddress.upazila',
            'orderState'
        ]);

        // 2. Load available states for the dropdown
        $this->orderStates = OrderState::all();
        $this->selectedState = $this->order->order_state_id;

        // 3. Calculate brief user history
        $this->calculateUserStats();
    }

    public function calculateUserStats()
    {
        $userOrders = $this->order->user->orders;

        $this->userOrderStats = [
            'total'          => $userOrders->count(),
            'pending'        => $userOrders->where('orderState.name', OrderState::PENDING)->count(),
            'approved'       => $userOrders->where('orderState.name', OrderState::APPROVED)->count(),
            'shipped'        => $userOrders->where('orderState.name', OrderState::SHIPPED)->count(),
            'delivered'      => $userOrders->where('orderState.name', OrderState::DELIVERED)->count(),
            'cancelled'      => $userOrders->where('orderState.name', OrderState::CANCELLED)->count(),
            'deliver_failed' => $userOrders->where('orderState.name', OrderState::DELIVER_FAILED)->count(),
            'returned'       => $userOrders->where('orderState.name', OrderState::RETURNED)->count(),
        ];
    }

    public function updateOrderStatus()
    {
        $this->order->update([
            'order_state_id' => $this->selectedState
        ]);

        Product::withoutEvents(function () {
            // if the changed state is 'approved', reduce the stock quantity of the ordered products
            if ($this->order->orderState->name === OrderState::APPROVED) {
                foreach ($this->order->orderedProducts as $orderedProduct) {
                    $product = $orderedProduct->product;
                    $product->decrement('available_quantity', $orderedProduct->quantity);
                }
            }
        });

        // Refresh the local relationship to update the UI badges instantly
        $this->order->load('orderState');
        $this->calculateUserStats();

        session()->flash('success', 'Order status successfully updated.');
    }
};
?>
<div>
    <div class="max-w-7xl mx-auto p-4 md:p-6 my-6 space-y-6">
        @if (session()->has('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-bold flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif
    </div>


    <div class="flex-col gap-2">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-5">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-black text-gray-900">Order #{{ $order->id }}</h1>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-slate-100 text-slate-700">
                        {{ str_replace('_', ' ', $order->orderState->name) }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 mt-1">Placed on {{ $order->created_at->format('F d, Y, h:i A') }}</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{route('admin.update-order', $order->id)}}" class="px-5 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-bold rounded-xl shadow-sm transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square"></i> Edit Order
                </a>
            </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        
        <div class="xl:col-span-2 space-y-6">
            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-2">
                    <i class="fa-solid fa-box-open text-blue-600"></i>
                    <h2 class="font-bold text-gray-800">Ordered Items ({{ $order->orderedProducts->count() }})</h2>
                </div>
                
                <div class="p-6 space-y-4">
                    @foreach($order->orderedProducts as $item)
                        <div class="flex items-center gap-4 border-b border-gray-50 pb-4 last:border-0 last:pb-0">
                            <div class="w-16 h-16 rounded-xl bg-gray-50 border border-gray-100 overflow-hidden shrink-0 shadow-inner">
                                @php $img = $item->product->productImages->first(); @endphp
                                @if($img)
                                    <img src="{{ asset('storage/' . $img->image_link) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                                        <i class="fa-solid fa-image"></i>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-gray-900 truncate">{{ $item->product->name }}</h4>
                                <p class="text-xs text-gray-500 mt-0.5">Unit Price: ৳{{ number_format($item->unit_price, 2) }}</p>
                            </div>
                            
                            <div class="text-right shrink-0">
                                <p class="text-sm font-bold text-gray-600">Qty: {{ $item->quantity }}</p>
                                <p class="text-base font-black text-blue-600">৳{{ number_format($item->price, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="w-full max-w-sm ml-auto space-y-3 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span class="font-bold text-gray-900">৳{{ number_format($order->total_price, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Shipping Charge</span>
                        <span class="font-bold text-gray-900">৳{{ number_format($order->total_shipping_charge, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center border-t border-gray-100 pt-3 mt-3">
                        <span class="font-black text-gray-900">Total Payable</span>
                        <span class="text-xl font-black text-blue-600">৳{{ number_format($order->total_price + $order->total_shipping_charge, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            
            <div class="bg-blue-50 rounded-2xl shadow-sm border border-blue-100 p-6">
                <h3 class="font-bold text-blue-900 mb-4 text-sm uppercase tracking-wide">Update Order Status</h3>
                <div class="flex flex-col gap-3">
                    <select wire:model="selectedState" class="w-full rounded-sm p-3 border-blue-200 bg-white text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                        @foreach($orderStates as $state)
                            <option value="{{ $state->id }}">{{ ucfirst(str_replace('_', ' ', $state->name)) }}</option>
                        @endforeach
                    </select>
                    <button wire:click="updateOrderStatus" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md transition-colors text-sm">
                        Apply Status
                    </button>
                </div>
            </div>

             <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-2 mb-4">
                    <i class="fa-solid fa-truck-fast text-gray-400"></i>
                    <h3 class="font-bold text-gray-800">Shipping Details</h3>
                </div>
                
                <div class="space-y-4 text-sm">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Contact Numbers</p>
                        <p class="font-semibold text-gray-900">{{ $order->orderAddress->phone }}</p>
                        @if($order->orderAddress->alternative_phone)
                            <p class="font-semibold text-gray-600">{{ $order->orderAddress->alternative_phone }} <span class="text-xs font-normal">(Alt)</span></p>
                        @endif
                    </div>
                    
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Delivery Address</p>
                        <p class="font-medium text-gray-900 leading-relaxed">{{ $order->orderAddress->address }}</p>
                        <p class="text-gray-600 mt-1">
                            {{ $order->orderAddress->upazila->name ?? 'N/A' }}, 
                            {{ $order->orderAddress->district->name ?? 'N/A' }},
                            {{ $order->orderAddress->division->name ?? 'N/A' }}
                        </p>
                        <!-- <p class="text-gray-600"> Division</p> -->
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-14 h-14 rounded-full bg-gray-100 border-2 border-white shadow-sm overflow-hidden shrink-0">
                        @if($order->user->profile_image)
                            <a href="{{ route('admin.user-details', $order->user->id) }}">
                                <img src="{{ asset('storage/' . $order->user->profile_image) }}" class="w-full h-full object-cover">
                            </a>
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400"><i class="fa-solid fa-user text-xl"></i></div>
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900">
                            <a href="{{ route('admin.user-details', $order->user->id) }}">{{ $order->user->name }}</a>
                        </h3>
                        <p class="text-xs text-gray-500">
                            <a href="{{ route('admin.user-details', $order->user->id) }}" class="hover:text-blue-600 transition-colors">{{ $order->user->email }}</a>
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-50 rounded-xl p-3 text-center border border-gray-100">
                        <span class="block text-xs text-gray-500 font-semibold mb-1">Total Orders</span>
                        <span class="block text-lg font-black text-gray-800">{{ $userOrderStats['total'] }}</span>
                    </div>

                    <div class="bg-amber-50 rounded-xl p-3 text-center border border-amber-100">
                        <span class="block text-xs text-amber-600 font-semibold mb-1">Pending</span>
                        <span class="block text-lg font-black text-amber-700">{{ $userOrderStats['pending'] }}</span>
                    </div>

                    <div class="bg-blue-50 rounded-xl p-3 text-center border border-blue-100">
                        <span class="block text-xs text-blue-600 font-semibold mb-1">Approved</span>
                        <span class="block text-lg font-black text-blue-700">{{ $userOrderStats['approved'] }}</span>
                    </div>

                    <div class="bg-indigo-50 rounded-xl p-3 text-center border border-indigo-100">
                        <span class="block text-xs text-indigo-600 font-semibold mb-1">Shipped</span>
                        <span class="block text-lg font-black text-indigo-700">{{ $userOrderStats['shipped'] }}</span>
                    </div>

                    <div class="bg-emerald-50 rounded-xl p-3 text-center border border-emerald-100">
                        <span class="block text-xs text-emerald-600 font-semibold mb-1">Delivered</span>
                        <span class="block text-lg font-black text-emerald-700">{{ $userOrderStats['delivered'] }}</span>
                    </div>

                    <div class="bg-red-50 rounded-xl p-3 text-center border border-red-100">
                        <span class="block text-xs text-red-600 font-semibold mb-1">Cancelled</span>
                        <span class="block text-lg font-black text-red-700">{{ $userOrderStats['cancelled'] }}</span>
                    </div>

                    <div class="bg-rose-50 rounded-xl p-3 text-center border border-rose-100">
                        <span class="block text-xs text-rose-600 font-semibold mb-1">Failed Delivery</span>
                        <span class="block text-lg font-black text-rose-700">{{ $userOrderStats['deliver_failed'] }}</span>
                    </div>

                    <div class="bg-orange-50 rounded-xl p-3 text-center border border-orange-100">
                        <span class="block text-xs text-orange-600 font-semibold mb-1">Returned</span>
                        <span class="block text-lg font-black text-orange-700">{{ $userOrderStats['returned'] }}</span>
                    </div>
                </div>
            </div>

           
        </div>
    </div>
    </div>
</div>

</div>