<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class () extends Component {
    // take the authenticated user and fetch their orders with related products and images
    public function getOrdersProperty()
    {
        return User::findOrFail(Auth::id())
            ->orders()
            ->with(['products.productImages', 'products.category'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
};
?>

<x-slot:title>
    My Orders - {{ config('app.name') }}
</x-slot>

<div>
    {{-- The whole future lies in uncertainty: live immediately. - Seneca --}}
</div>