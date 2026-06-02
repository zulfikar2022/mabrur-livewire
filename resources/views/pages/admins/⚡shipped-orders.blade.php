<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\OrderState;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component {
    /**
     * Computed Property: Fetch and group pending orders
     */
    public function getPendingOrdersGroupedProperty()
    {
        return Order::whereHas('orderState', function ($query) {
            $query->where('name', OrderState::SHIPPED);
        })
            ->with(['user', 'orderedProducts']) // Eager load to prevent N+1 issues
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('user_id'); // Groups the collection into arrays keyed by user_id
    }
};
?>

<div class="max-w-7xl mx-auto p-4 md:p-6 my-6">
    
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-black text-gray-800 tracking-tight flex items-center gap-3">
            <!-- <i class="fa-solid  text-amber-500"></i> -->
            Shipped Orders
        </h1>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-8">
        
        @forelse($this->pendingOrdersGrouped as $userId => $userOrders)
            @php 
                // Grab the user details from the first order in this group
                $user = $userOrders->first()->user; 
            @endphp
            
            <div class="space-y-4">
                
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-gray-100 shrink-0">
                        @if($user->profile_image)
                           <a href="{{ route('admin.user-details', $user->id) }}"> <img src="{{ asset('storage/' . $user->profile_image) }}" class="w-full h-full object-cover"></a>
                        @else
                            <div class="w-full h-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
                                <a href="{{ route('admin.user-details', $user->id) }}">{{ strtoupper(substr($user->name, 0, 1)) }}</a>
                            </div>
                        @endif
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 leading-tight">
                            <a href="{{ route('admin.user-details', $user->id) }}">{{ $user->name }}</a>
                        </h2>
                        <a href="{{ route('admin.user-details', $user->id) }}" class="text-sm text-gray-500 hover:text-blue-600 transition-colors">
                            {{ $user->email }}
                        </a>
                    </div>
                    <div class="ml-auto">
                        <span class="bg-amber-100 text-amber-700 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                            {{ $userOrders->count() }} Shipped {{ Str::plural('Order', $userOrders->count()) }}
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto border border-gray-100 rounded-xl">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-[11px] font-bold tracking-wider">
                            <tr>
                                <th class="px-5 py-3">Order ID</th>
                                <th class="px-5 py-3">Total Products</th>
                                <th class="px-5 py-3">Total Payable</th>
                                <th class="px-5 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($userOrders as $order)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-5 py-4 font-bold text-gray-900">
                                        #{{ $order->id }}
                                        <span class="block text-[10px] text-gray-400 font-normal mt-0.5">{{ $order->created_at->diffForHumans() }}</span>
                                    </td>
                                    
                                    <td class="px-5 py-4 text-gray-600 font-medium">
                                        {{ $order->orderedProducts->count() }} Distinct Items
                                    </td>
                                    
                                    <td class="px-5 py-4 font-black text-blue-600">
                                        ৳{{ number_format($order->total_price + $order->total_shipping_charge, 2) }}
                                    </td>
                                    
                                    <td class="px-5 py-4 text-right">
                                        <a href="{{route('admin.order-details', $order->id)}}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 hover:bg-gray-50 hover:text-blue-600 text-gray-700 text-xs font-bold rounded-lg shadow-sm transition-colors">
                                            <span>Manage</span>
                                            <i class="fa-solid fa-arrow-right"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>

            @if(!$loop->last)
                <hr class="border-gray-100 border-t-2 border-dashed my-8">
            @endif

        @empty
            <div class="text-center py-16 text-gray-500">
                <i class="fa-solid fa-clipboard-check text-5xl text-gray-200 mb-4 block"></i>
                <p class="font-bold text-gray-600 text-lg">All caught up!</p>
                <p class="text-sm mt-1 text-gray-400">There are no shipped orders requiring your attention right now.</p>
            </div>
        @endforelse

    </div>
</div>