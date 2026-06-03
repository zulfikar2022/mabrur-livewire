<?php

use App\Models\OrderState;
use Livewire\Component;

new class () extends Component {
    public $user;
    public $userOrders;
    public $status;
    public function mount($user, $userOrders, $status = null)
    {
        $this->user = $user;
        $this->userOrders = $userOrders;
        $this->status = $status;
    }
};
?>

<div class="space-y-4">
                
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-gray-100 shrink-0">
            @if($user->profile_image)
                <a href="{{ route('admin.user-details', $user->id) }}">
                    <img src="{{ asset('storage/' . $user->profile_image) }}" class="w-full h-full object-cover">
                </a>
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
        @if($status)
            <div class="ml-auto">
                <span class="bg-amber-100 text-amber-700 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                    {{ $userOrders->count() }} {{ $status }} {{ Str::plural('Order', $userOrders->count()) }}
                </span>
            </div>
        @else
            <div class="ml-auto">
                <span class="bg-amber-100 text-amber-700 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                    {{ $userOrders->count() }} Orders
                </span>
            </div>
        @endif
    </div>

    <div class="overflow-x-auto border border-gray-100 rounded-xl">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-500 uppercase text-[11px] font-bold tracking-wider">
                <tr>
                    <th class="px-5 py-3">Order ID</th>
                    <th class="px-5 py-3">Total Products</th>
                    @if (!$status) 
                        <th class="px-5 py-3">Status</th>
                    @endif
                    <th class="px-5 py-3">Total Payable</th>
                    <th class="px-5 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($userOrders as $order)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-4 font-bold text-gray-900">
                            {{ $order->id }}
                            <span class="block text-[10px] text-gray-400 font-normal mt-0.5">{{ $order->created_at->diffForHumans() }}</span>
                        </td>
                        
                        <td class="px-5 py-4 text-gray-600 font-medium">
                            {{ $order->orderedProducts->count() }} Items
                        </td>
                        @if (!$status) 
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-bold {{ $order->orderState->name === OrderState::PENDING ? 'bg-yellow-100 text-yellow-700' : ($order->orderState->name === OrderState::DELIVERED ? 'bg-green-100 text-green-700' : ($order->orderState->name === OrderState::CANCELLED ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700')) }}">
                                    {{ $order->orderState->name }}
                                </span>
                            </td>
                        @endif
                        
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