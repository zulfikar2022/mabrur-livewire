<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component {
    use WithPagination;

    /**
     * Activates a disabled user's operational state back to active
     */
    public function activateUser($userId)
    {
        $user = User::findOrFail($userId);

        // Security fallback check to prevent modifying system admins
        if ($user->hasAnyRole(['admin', 'super-admin'])) {
            session()->flash('error', 'Administrator statuses cannot be modified.');
            return;
        }

        // Change status to active
        $user->status = 'active';
        $user->save();

        session()->flash('success', "{$user->name}'s account has been activated successfully.");
    }

    /**
     * Computed Property: Dynamically fetches, sorts, and paginates DISABLED users.
     */
    public function getUsersProperty()
    {
        // 1. Fetch Disabled Admins (if any exist)
        $disabledAdmins = User::role('admin')
            ->where('status', 'disabled')
            ->select('id', 'name', 'email', 'profile_image', 'status')
            ->orderBy('name', 'asc')
            ->get();

        // 2. Fetch Disabled General Users
        $disabledGeneralUsers = User::role('user')
            ->where('status', 'disabled') // <=== THE 핵심 FILTER
            ->select('id', 'name', 'email', 'profile_image', 'status')
            ->orderBy('name', 'asc')
            ->get();

        // 3. Combine collections so disabled admins sit on top
        $allDisabledUsers = $disabledAdmins->concat($disabledGeneralUsers);

        // 4. Manually paginate the unified collection structure (50 items per page)
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 50;

        $currentItems = $allDisabledUsers->slice(($currentPage - 1) * $perPage, $perPage)->all();

        return new LengthAwarePaginator(
            $currentItems,
            $allDisabledUsers->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
    }
};
?>

<div class="max-w-6xl mx-auto p-4 md:p-6 my-6">
    <div class="mb-4">
        <a href="{{ route('admin.see-all-users') }}" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors" wire:navigate>
            <i class="fa-solid fa-arrow-left mr-1.5"></i> Back to Main Directory
        </a>
    </div>

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

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Disabled Users Queue</h2>
                <p class="text-xs text-gray-500 mt-0.5">Review and re-activate restricted user accounts</p>
            </div>
            <span class="text-xs bg-red-100 text-red-700 font-semibold px-2.5 py-1 rounded-full">
                Suspended Accounts: {{ $this->users->total() }}
            </span>
        </div>

        <div class="px-6 py-3 bg-gray-50/50 border-b border-gray-100 font-semibold text-xs text-gray-500 uppercase tracking-wider hidden md:grid md:grid-cols-12 gap-4 items-center">
            <div class="col-span-6">User / Account Credentials</div>
            <div class="col-span-3 text-center">Account Group</div>
            <div class="col-span-3 text-right">Quick Actions</div>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($this->users as $user)
                <div wire:key="disabled-user-row-{{ $user->id }}" class="px-6 py-4 grid grid-cols-1 md:grid-cols-12 gap-4 items-center hover:bg-red-50/30 transition-colors">
                    
                    <div class="col-span-1 md:col-span-6 flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-full bg-gray-100 overflow-hidden shrink-0 border border-gray-200 shadow-inner">
                            @if($user->profile_image)
                                <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}" class="w-full h-full object-cover opacity-75 grayscale-[30%]">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-500 font-bold text-sm tracking-wider uppercase">
                                    {{ substr($user->name, 0, 2) }}
                                </div>
                            @endif
                        </div>

                        <div class="truncate">
                            <h4 class="font-semibold text-gray-700 text-base leading-tight">{{ $user->name }}</h4>
                            <span class="text-sm text-gray-400 font-normal block mt-0.5 truncate">{{ $user->email }}</span>
                        </div>
                    </div>

                    <div class="col-span-1 md:col-span-3 flex md:justify-center items-center">
                        <span class="md:hidden text-xs font-semibold text-gray-400 uppercase tracking-wider mr-2">Role:</span>
                        @if($user->hasRole('admin'))
                            <span class="px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-md text-xs font-bold uppercase tracking-wide">
                                Manager / Admin
                            </span>
                        @else
                            <span class="px-2.5 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded-md text-xs font-medium uppercase tracking-wide">
                                General Customer
                            </span>
                        @endif
                    </div>

                    <div class="col-span-1 md:col-span-3 flex items-center justify-between md:justify-end space-x-4">
                        <span class="md:hidden text-xs font-semibold text-gray-400 uppercase tracking-wider">State:</span>
                        
                        <div class="flex items-center space-x-3">
                            <span class="text-xs font-bold text-red-500 uppercase bg-red-50 px-2 py-0.5 rounded border border-red-100">
                                Suspended
                            </span>

                            @if(!$user->hasRole('admin'))
                                <button type="button" 
                                        wire:click="activateUser({{ $user->id }})"
                                        wire:confirm="Are you sure you want to re-activate this user account?"
                                        class="inline-flex items-center space-x-1 bg-green-600 hover:bg-green-700 text-white font-semibold text-xs px-3 py-1.5 rounded-lg transition-colors cursor-pointer shadow-sm">
                                    <i class="fa-solid fa-user-check"></i>
                                    <span>Enable Account</span>
                                </button>
                            @else
                                <div class="w-24 h-5 hidden md:block"></div>
                            @endif
                        </div>
                    </div>

                </div>
            @empty
                <div class="p-12 text-center text-gray-400">
                    <div class="text-4xl mb-2">🎉</div>
                    <p class="font-medium text-gray-500">The disabled queue is completely clear!</p>
                    <p class="text-xs text-gray-400 mt-1">All user accounts are currently active on the platform.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="mt-4">
        {{ $this->users->links() }}
    </div>
</div>