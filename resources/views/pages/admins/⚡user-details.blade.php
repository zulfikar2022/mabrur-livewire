<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component {
    //
    public $user;

    public function mount(User $user)
    {
        // User has a orders rleationship. fetch all the orders of a user along with the user

        $this->user = $user->load('orders');
        // dd($this->user);
    }
};
?>

<div>
    <p>User Details</p>
</div>