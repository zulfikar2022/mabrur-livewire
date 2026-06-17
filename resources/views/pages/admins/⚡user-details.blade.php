<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\OrderState;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component {
    public User $user;
    public $stats = [];

    public function mount(User $user)
    {
        // Eager load everything needed for the view
        $this->user = $user->load([
            'orders.orderState',
            'orders.orderedProducts'
        ]);

        $this->calculateStats();
    }

    public function toggleStatus()
    {
        // 1. Prevent admin from modifying other admins
        if ($this->user->hasRole('admin')) {
            session()->flash('error', 'Cannot modify administrator accounts.');
            return;
        }

        // 2. Toggle status
        $this->user->status = ($this->user->status === 'disabled') ? 'active' : 'disabled';
        $this->user->save();

        session()->flash('success', 'User status updated to ' . $this->user->status);
    }

    public function calculateStats()
    {
        $orders = $this->user->orders;
        $total_orders = $orders->count();

        // Calculate count for each status
        $statusCounts = $orders->groupBy('orderState.name')->map->count();

        // Calculate total spend (only for 'delivered' orders)
        $totalSpent = $orders
            ->filter(fn ($order) => $order->orderState?->name === OrderState::DELIVERED)
            ->sum('total_price');

        $this->stats = [
            'total_spent' => $totalSpent,
            'counts'      => $statusCounts,
            'total_orders' => $total_orders
        ];
    }
};
?>

<div class="max-w-7xl mx-auto p-4 md:p-6 my-6 space-y-6">
    
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col md:flex-row gap-6 items-center">
        <div class="w-24 h-24 rounded-full border-4 border-gray-50 overflow-hidden shadow-inner shrink-0">
            @if($user->profile_image)
                <img src="{{ asset('storage/' . $user->profile_image) }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center bg-blue-50 text-blue-300 text-3xl font-black">
                    {{ substr($user->name, 0, 1) }}
                </div>
            @endif
        </div>
        <!-- <div class="flex-1 text-center md:text-left">
            <h1 class="text-2xl font-black text-gray-900">{{ $user->name }}</h1>
            <p class="text-gray-500">{{ $user->email }}</p>
            <p class="text-sm font-bold mt-4">Total Orders: {{ $stats['total_orders'] }}</p>
        </div> -->
        <div class="flex-1 text-center md:text-left">
            <div class="flex items-center justify-center md:justify-start gap-3">
                <h1 class="text-2xl font-black text-gray-900">{{ $user->name }}</h1>
                
                <button type="button" 
                        wire:click="toggleStatus"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $user->status === 'active' ? 'bg-green-600' : 'bg-gray-200' }}">
                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $user->status === 'active' ? 'translate-x-5' : 'translate-x-0' }}"></span>
                </button>
                <span class="text-xs font-bold uppercase {{ $user->status === 'active' ? 'text-green-600' : 'text-gray-400' }}">
                    {{ $user->status }}
                </span>
            </div>
            <p class="text-gray-500">{{ $user->email }}</p>
            <!-- SHOW USER'status from role -->
            @if($user->hasRole('admin'))
                <span class="text-sm font-bold mt-1 text-green-600">Admin</span>
            @elseif($user->hasRole('user'))
                <span class="text-sm font-bold mt-1 text-blue-600">User</span>
            @endif
            <p class="text-sm font-bold mt-4">Total Orders: {{ $stats['total_orders'] }}</p>
        </div>
        <div class="bg-blue-50 rounded-xl p-4 text-center border border-blue-100 min-w-37.5">
            <span class="block text-xs text-blue-600 font-bold uppercase">Total Spent</span>
            <span class="text-xl font-black text-blue-700">৳{{ number_format($stats['total_spent'], 2) }}</span>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
        @foreach(['pending', 'approved', 'shipped', 'delivered', 'cancelled', 'deliver_failed', 'returned'] as $state)
            <div class="p-4 rounded-xl shadow-sm border border-gray-100 text-center {{ $state == 'delivered' ? 'bg-green-200':'bg-white' }}">
                <span class="block text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1">{{ str_replace('_', ' ', $state) }}</span>
                <span class="block text-lg font-black text-gray-800">{{ $stats['counts'][$state] ?? 0 }}</span>
            </div>
        @endforeach
    </div>

   <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-50 font-bold text-gray-800">Order History</div>
    
    <div class="overflow-x-auto"> 
        <table class="w-full text-sm min-w-[600px]"> <thead class="bg-gray-50 text-gray-500 uppercase text-[10px]">
                <tr>
                    <th class="px-6 py-3">Order ID</th>
                    <th class="px-6 py-3">Date</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Amount</th>
                    <th class="px-6 py-3">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($user->orders as $order)
                    <tr>
                        <td class="px-6 py-4 font-bold text-gray-900 text-center">#{{ $order->id }}</td>
                        <td class="text-center px-6 py-4 text-gray-600">{{ $order->created_at->format('M d, Y') }}</td>
                        <td class="text-center px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-600">
                                {{ str_replace('_', ' ', $order->orderState->name) }}
                            </span>
                        </td>
                        <td class="text-right px-6 py-4 font-bold text-gray-900">৳{{ number_format($order->total_price, 2) }}</td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('admin.order-details', $order->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 hover:bg-gray-50 hover:text-blue-600 text-gray-700 text-xs font-bold rounded-lg shadow-sm transition-colors">
                                <span>View</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">No orders found for this user.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div> </div>
</div>