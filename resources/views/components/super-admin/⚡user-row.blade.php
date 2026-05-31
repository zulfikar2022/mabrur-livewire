<?php

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

new class () extends Component {
    public User $user;
    public $currentRole;
    public $allRoles;

    public function mount(User $user)
    {
        $this->user = $user;
        $this->currentRole = $user->getRoleNames()->first();
        $this->allRoles = Role::pluck('name')->toArray();
    }


    public function updateStatus($newStatus)
    {
        if (Auth::id() === $this->user->id) {
            session()->flash('error', 'You cannot disable or modify your own status.');
            return;
        }
        if (in_array($newStatus, ['active', 'disabled'])) {
            $this->user->update(['status' => $newStatus]);

            session()->flash('message', 'Status updated successfully.');
        }
    }

    public function updateRole($newRole)
    {
        if (Auth::id() === $this->user->id) {
            session()->flash('error', 'You cannot disable or modify your own status.');
            return;
        }

        if ($newRole === 'super-admin') {
            session()->flash('error', 'Assigning the super-admin role is not allowed through this interface.');
            return;
        }

        if (in_array($newRole, $this->allRoles)) {
            $this->user->syncRoles($newRole);
            $this->currentRole = $newRole;

            session()->flash('message', 'Role updated successfully.');
        }
    }
};
?>

<div class="flex flex-col md:flex-row md:items-center justify-between p-4 gap-4  transition-colors duration-200
    {{ $user->status === 'disabled' ? 'bg-rose-50/70 border-rose-100' : 'hover:bg-slate-600' }}">
    
    <div class="flex items-center space-x-4 flex-1 min-w-0">
        <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-slate-200 bg-slate-100 shrink-0 shadow-inner">
            @if($user->profile_image)
                <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center text-slate-400 bg-slate-200">
                    <i class="fa-solid fa-user text-lg"></i>
                </div>
            @endif
        </div>

        <div class="truncate">
            <h4 class="font-semibold text-base truncate">{{ $user->name }}</h4>
            <p class="text-sm text-slate-500 truncate">{{ $user->email }}</p>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-4 md:gap-8">
        
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" @click.outside="open = false" 
                class="inline-flex items-center space-x-2 px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider transition shadow-sm focus:outline-none
                {{ $user->status === 'active' ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-rose-100 text-rose-800 hover:bg-rose-200' }}">
                <span>{{ $user->status }}</span>
                <i class="fa-solid fa-chevron-down text-[10px]"></i>
            </button>

            <div x-show="open" x-cloak class="absolute right-0 mt-2 w-32 bg-white border border-slate-200 rounded-lg shadow-lg py-1 z-30">
                <button wire:click="updateStatus('active')" @click="open = false" 
                    class="w-full text-left px-4 py-2 text-sm text-emerald-700 hover:bg-emerald-50 font-medium flex items-center justify-between">
                    <span>Active</span>
                    @if($user->status === 'active') <i class="fa-solid fa-check text-xs"></i> @endif
                </button>
                <button wire:click="updateStatus('disabled')" @click="open = false" 
                    class="w-full text-left px-4 py-2 text-sm text-rose-700 hover:bg-rose-50 font-medium flex items-center justify-between">
                    <span>Disabled</span>
                    @if($user->status === 'disabled') <i class="fa-solid fa-check text-xs"></i> @endif
                </button>
            </div>
        </div>

        <div class="relative min-w-35" x-data="{ open: false }">
            <button @click="open = !open" @click.outside="open = false" 
                class="w-full inline-flex items-center justify-between px-3 py-1.5 border border-slate-300 rounded-md bg-white text-sm text-slate-700 font-medium shadow-sm hover:bg-slate-50 focus:outline-none">
                <span class="capitalize">{{ $currentRole ?? 'No Role' }}</span>
                <i class="fa-solid fa-chevron-down text-slate-400 text-xs ml-2"></i>
            </button>

            <div x-show="open" x-cloak class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-lg shadow-xl py-1 z-30 max-h-48">
                @foreach($allRoles as $roleName)
                    <button wire:click="updateRole('{{ $roleName }}')" @click="open = false" 
                        class="w-full text-left px-4 py-2 text-sm hover:bg-blue-50 capitalize transition flex items-center justify-between z-10
                        {{ $currentRole === $roleName ? 'text-blue-600 bg-blue-50/50 font-semibold' : 'text-slate-700' }}">
                        <span>{{ str_replace('-', ' ', $roleName) }}</span>
                        @if($currentRole === $roleName)
                            <i class="fa-solid fa-circle-check text-xs text-blue-600"></i>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

    </div>
</div>