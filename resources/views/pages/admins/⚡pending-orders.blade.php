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
            $query->where('name', OrderState::PENDING);
        })
            ->with(['user', 'orderedProducts']) // Eager load to prevent N+1 issues
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('user_id'); // Groups the collection into arrays keyed by user_id
    }
};
?>
<div>

    <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-black text-gray-800 tracking-tight flex items-center gap-3">
                <i class="fa-solid fa-times text-amber-500"></i>
                Pending Orders
            </h1>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-8">
        
        @forelse($this->pendingOrdersGrouped as $userId => $userOrders)
                @php 
                    $user = $userOrders->first()->user; 
                @endphp
            <livewire:admin.order-list-item :user="$user" :user-orders="$userOrders" :status="OrderState::PENDING" />
        @empty
            <div class="text-center py-12">
                <i class="fa-solid fa-box-open text-gray-300 text-6xl mb-4"></i>
                <p class="text-gray-500">No orders found.</p>
            </div>
        @endforelse
    </div>
</div>