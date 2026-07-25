<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Attributes\Computed;

new #[Layout('layouts::super-admin')] class extends Component {
    public string $search = '';

    #[Computed]
    public function users()
    {
        // Build the query dynamically based on the search string and strict role constraint
        return User::query()
            ->role('super-admin')
            ->when(trim($this->search) !== '', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('name', 'like', '%' . $this->search . '%')
                             ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('id', 'desc')
            ->get();
    }
};
?>

<div class="max-w-6xl mx-auto my-6 px-4 bg-red-600">
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-100">User Management</h1>
            <p class="text-sm text-slate-400">Manage account access statuses and system role configurations.</p>
        </div>

        <div class="relative w-full md:w-80">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
            </span>
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="Search by name or email..." 
                class="w-full pl-10 pr-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-sm text-slate-100 placeholder-slate-400 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition shadow-inner"
            >
            @if($search !== '')
                <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-200">
                    <i class="fa-solid fa-circle-xmark text-sm"></i>
                </button>
            @endif
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-emerald-900/40 border border-emerald-500 text-emerald-200 rounded-lg text-sm font-medium">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-rose-900/40 border border-rose-500 text-rose-200 rounded-lg text-sm font-medium">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-slate-800 rounded-xl shadow-xl border border-slate-700 ">
        <div class="p-4 bg-slate-850 border-b border-slate-700 font-semibold text-slate-300 flex justify-between items-center">
            <span class="text-xs bg-slate-700 text-slate-300 px-2.5 py-1 rounded-full font-bold">
                Total: {{ $this->users->count() }}
            </span>
        </div>

        <div class="divide-y divide-slate-700">
            @forelse($this->users as $user)
                <livewire:super-admin.user-row :user="$user" :key="$user->id" />
            @empty
                <div class="p-8 text-center text-slate-500">
                    <i class="fa-solid fa-users-slash text-4xl mb-3 block"></i>
                    <p class="text-sm">No user accounts found matching your search criteria.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>