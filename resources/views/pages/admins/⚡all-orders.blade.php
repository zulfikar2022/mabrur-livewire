<?php

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class () extends Component {
    public function getAllOrdersGroupedProperty()
    {
        return Order::with(['user', 'orderedProducts', 'orderState'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('user_id');
    }
};
?>

<div>

    <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-black text-gray-800 tracking-tight flex items-center gap-3">
                <i class="fa-solid fa-check text-green-500"></i>
                All Orders
            </h1>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-8">
        
        @forelse($this->allOrdersGrouped as $userId => $userOrders)
                @php 
                    $user = $userOrders->first()->user; 
                @endphp
            <livewire:admin.order-list-item :user="$user" :user-orders="$userOrders"  />
        @empty
            <div class="text-center py-12">
                <i class="fa-solid fa-box-open text-gray-300 text-6xl mb-4"></i>
                <p class="text-gray-500">No orders found.</p>
            </div>
        @endforelse
    </div>
</div>