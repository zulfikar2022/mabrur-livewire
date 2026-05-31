<?php

use Laravel\Socialite\Socialite;
use Livewire\Component;

new class () extends Component {
    public function redirectToGoogle()
    {
        return redirect()->route('auth.google');
    }
};
?>

<div class="flex items-center justify-center mt-10">
    <button 
        wire:click="redirectToGoogle"
        class="flex items-center gap-3 px-6 py-3 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 hover:cursor-pointer transition duration-200"
    >
        <!-- The Google Icon -->
       <img src="{{ asset('storage/logos/google.jpeg') }}"  class="w-5 h-5" alt="Google Logo">
        
        <span class="font-semibold text-gray-700">Login with Google</span>
    </button>
</div>