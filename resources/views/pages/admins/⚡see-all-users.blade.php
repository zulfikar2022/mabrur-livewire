<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component {
    use WithPagination;

    // Public search property tracked by Livewire
    public $search = '';

    /**
     * Resets pagination to page 1 whenever the search term is updated
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Toggles a general user's operational state between active and disabled
     */
    public function toggleUserStatus($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->hasAnyRole(['admin', 'super-admin'])) {
            session()->flash('error', 'Administrator statuses cannot be modified.');
            return;
        }

        $user->status = $user->status === 'active' ? 'disabled' : 'active';
        $user->save();

        session()->flash('success', "User account status updated to {$user->status}.");
    }

    /**
     * Computed Property: Fetches, filters, sorts, and paginates users dynamically.
     */
    public function getUsersProperty()
    {
        $searchTerm = trim($this->search);

        // 1. Fetch Admins (filtered by search if present)
        $admins = User::role('admin')
            ->select('id', 'name', 'email', 'profile_image', 'status')
            ->when($searchTerm, function ($query) use ($searchTerm) {
                $query->where(function ($subQuery) use ($searchTerm) {
                    // Changed 'like' to 'ilike' for PostgreSQL case-insensitive search
                    $subQuery->where('name', 'ilike', "%{$searchTerm}%")
                             ->orWhere('email', 'ilike', "%{$searchTerm}%");
                });
            })
            ->orderBy('name', 'asc')
            ->get();

        // 2. Fetch General Users (filtered by search if present)
        $generalUsers = User::role('user')
            ->select('id', 'name', 'email', 'profile_image', 'status')
            ->when($searchTerm, function ($query) use ($searchTerm) {
                $query->where(function ($subQuery) use ($searchTerm) {
                    // Changed 'like' to 'ilike' for PostgreSQL case-insensitive search
                    $subQuery->where('name', 'ilike', "%{$searchTerm}%")
                             ->orWhere('email', 'ilike', "%{$searchTerm}%");
                });
            })
            ->orderBy('name', 'asc')
            ->get();

        // 3. Merge so matching Admins are strictly at the top
        $allAllowedUsers = $admins->concat($generalUsers);

        // 4. Manually paginate the unified collection structure (50 items per page)
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 50;

        $currentItems = $allAllowedUsers->slice(($currentPage - 1) * $perPage, $perPage)->all();

        return new LengthAwarePaginator(
            $currentItems,
            $allAllowedUsers->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
    }
};
?>

<div class="max-w-6xl mx-auto p-4 md:p-6 my-6 space-y-4">
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm font-medium">
            {{ session('error') }}
        </div>
    @endif

    <div class="w-full md:max-w-md bg-white rounded-xl shadow-sm border border-gray-100 p-3">
        <div class="relative flex items-center">
            <span class="absolute left-3 text-gray-400">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
            </span>
            <input type="text" 
                   wire:model.live.debounce.300ms="search" 
                   placeholder="Search users by name or email..." 
                   class="w-full border border-gray-300 rounded-lg pl-9 pr-4 py-2 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition shadow-sm">
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-800">User Management Directory</h2>
            <span class="text-xs bg-gray-200 text-gray-700 font-semibold px-2.5 py-1 rounded-full">
                Total Found: {{ $this->users->total() }}
            </span>
        </div>

        <div class="px-6 py-3 bg-gray-50/50 border-b border-gray-100 font-semibold text-xs text-gray-500 uppercase tracking-wider hidden md:grid md:grid-cols-12 gap-4 items-center">
            <div class="col-span-6">Account Credentials</div>
            <div class="col-span-3 text-center">Account Group</div>
            <div class="col-span-3 text-right">Status Controls</div>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($this->users as $user)
                <div wire:key="user-row-{{ $user->id }}" class="px-6 py-4 grid grid-cols-1 md:grid-cols-12 gap-4 items-center hover:bg-gray-50 transition-colors">
                    
                    <div class="col-span-1 md:col-span-6 flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-full bg-gray-100 overflow-hidden shrink-0 border border-gray-200 shadow-inner">
                            @if($user->profile_image)
                               <a href="{{ route('admin.user-details', $user) }}"> <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}" class="w-full h-full object-cover"></a>
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-blue-50 text-blue-600 font-bold text-sm tracking-wider uppercase">
                                    <!-- admin.user-details -->
                                    <a href="{{ route('admin.user-details', $user) }}">
                                        {{ substr($user->name, 0, 2) }}
                                    </a>
                                </div>
                            @endif
                        </div>

                        <div class="truncate">
                            <h4 class="font-semibold text-gray-900 text-base leading-tight">
                                <a href="{{ route('admin.user-details', $user) }}">
                                    {{ $user->name }}
                                </a>
                            </h4>
                            <span class="text-sm text-gray-500 font-normal block mt-0.5 truncate">
                                <a href="{{ route('admin.user-details', $user) }}">
                                    {{ $user->email }}
                                </a>
                            </span>
                        </div>
                    </div>

                    <div class="col-span-1 md:col-span-3 flex md:justify-center items-center">
                        <span class="md:hidden text-xs font-semibold text-gray-400 uppercase tracking-wider mr-2">Role:</span>
                        @if($user->hasRole('admin'))
                            <span class="px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-md text-xs font-bold uppercase tracking-wide">
                                 Admin
                            </span>
                        @else
                            <span class="px-2.5 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded-md text-xs font-medium uppercase tracking-wide">
                                Customer
                            </span>
                        @endif
                    </div>

                    <div class="col-span-1 md:col-span-3 flex items-center justify-between md:justify-end space-x-4">
                        <span class="md:hidden text-xs font-semibold text-gray-400 uppercase tracking-wider">Account State:</span>
                        
                        <div class="flex items-center space-x-3">
                            <span class="text-xs font-semibold {{ $user->status === 'active' ? 'text-green-600' : 'text-gray-400' }}">
                                {{ ucfirst($user->status ?? 'active') }}
                            </span>

                            @if(!$user->hasRole('admin'))
                                <button type="button" 
                                        wire:click="toggleUserStatus({{ $user->id }})"
                                        class="relative inline-flex h-5 w-10 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $user->status === 'active' ? 'bg-green-600' : 'bg-gray-200' }}">
                                    <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $user->status === 'active' ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                            @else
                                <div class="w-10 h-5 hidden md:block"></div>
                            @endif
                        </div>
                    </div>

                </div>
            @empty
                <div class="p-12 text-center text-gray-500">
                    <div class="text-4xl mb-2">🔍</div>
                    <p class="font-medium">No results found for "{{ $search }}".</p>
                    <p class="text-xs text-gray-400 mt-1">Try checking the spelling or searching for a different name or email.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="mt-4">
        {{ $this->users->links() }}
    </div>
</div>