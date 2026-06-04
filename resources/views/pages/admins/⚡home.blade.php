<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderState;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component {
    public function getStatsProperty()
    {
        return [
            'active_products'   => Product::where('is_available', true)->count(),
            'total_users'       => User::count(),
            'disabled_users'    => User::where('status', 'disabled')->count(),

            // New Category Stats
            'total_categories'  => Category::count(),
            // Counts categories that have at least one product
            'active_categories' => Category::whereHas('products')->count(),

            'order_counts'      => Order::join('order_states', 'orders.order_state_id', '=', 'order_states.id')
                ->select('order_states.name', DB::raw('count(*) as count'))
                ->groupBy('order_states.name')
                ->pluck('count', 'name')
                ->toArray()
        ];
    }
};
?>


<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Admin Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-gray-500 font-medium text-sm">Active Products</h3>
            <p class="text-3xl font-black text-blue-600">{{ $this->stats['active_products'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-gray-500 font-medium text-sm">Total Users</h3>
            <p class="text-3xl font-black text-gray-800">{{ $this->stats['total_users'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-gray-500 font-medium text-sm">Disabled Users</h3>
            <p class="text-3xl font-black text-red-600">{{ $this->stats['disabled_users'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-gray-500 font-medium text-sm">Total Categories</h3>
            <p class="text-3xl font-black text-purple-600">{{ $this->stats['total_categories'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="text-gray-500 font-medium text-sm">Active Categories</h3>
            <p class="text-3xl font-black text-emerald-600">{{ $this->stats['active_categories'] }}</p>
        </div>
    </div>

    <h2 class="text-xl font-bold mb-4">Orders by Status</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach(['pending', 'approved', 'shipped', 'delivered', 'deliver_failed', 'returned', 'cancelled'] as $state)
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                <p class="text-sm text-gray-500 uppercase font-semibold">{{ $state }}</p>
                <p class="text-2xl font-bold">{{ $this->stats['order_counts'][$state] ?? 0 }}</p>
            </div>
        @endforeach
    </div>
</div>

