<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\OrderState;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component {
    // Search fields
    public $userSearch = '';
    public $orderIdSearch = '';

    public function getApprovedOrdersGroupedProperty()
    {
        $query = Order::whereHas('orderState', function ($query) {
            $query->where('name', OrderState::APPROVED);
        })
        ->with(['user', 'orderedProducts']);

        // Filter by Order ID
        if (!empty($this->orderIdSearch)) {
            $query->where('id', 'like', '%' . $this->orderIdSearch . '%');
        }

        // Filter by User Name or Email (Case-Insensitive)
        if (!empty($this->userSearch)) {
            $query->whereHas('user', function ($userQuery) {
                $userQuery->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($this->userSearch) . '%'])
                          ->orWhereRaw('LOWER(email) LIKE ?', ['%' . strtolower($this->userSearch) . '%']);
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('user_id');
    }
};
?>

<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-black text-gray-800 tracking-tight flex items-center gap-3">
            <i class="fa-solid fa-check text-green-500"></i>
            Approved Orders
        </h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <input type="text" wire:model.live.debounce.300ms="userSearch" 
               placeholder="Search by user name or email..." 
               class="w-full rounded-sm border-gray-200 border p-2 bg-white focus:border-blue-500 focus:ring-blue-500">
        
        <input type="text" wire:model.live.debounce.300ms="orderIdSearch" 
               placeholder="Search by order ID..." 
               class="w-full rounded-sm border-gray-200 border p-2 bg-white focus:border-blue-500 focus:ring-blue-500">
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-8">
        @forelse($this->approvedOrdersGrouped as $userId => $userOrders)
            @php 
                $user = $userOrders->first()->user; 
            @endphp
            <livewire:admin.order-list-item :user="$user" :user-orders="$userOrders" :status="OrderState::APPROVED" :key="'approved-order-'.$userId" />
        @empty
            <div class="text-center py-12">
                <i class="fa-solid fa-box-open text-gray-300 text-6xl mb-4"></i>
                <p class="text-gray-500">No approved orders match your search.</p>
            </div>
        @endforelse
    </div>
</div>