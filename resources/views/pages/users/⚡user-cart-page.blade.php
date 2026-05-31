<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class () extends Component {
    public function mount(User $user)
    {
        if (!Auth::check()) {
            session()->flash('error', 'Please log in to view your cart.');
            return redirect()->route('login');
        }

        if ($user->id !== Auth::id()) {
            session()->flash('error', 'Unauthorized access! You cannot view another user\'s shopping cart.');
            return redirect()->route('home');
        }
    }
};
?>

<div>
    <p>This is the product cart page</p>
</div>