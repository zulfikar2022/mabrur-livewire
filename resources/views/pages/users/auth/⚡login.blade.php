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

<div class="flex flex-col gap-3.5 items-center justify-center mt-10">
    <p>পণ্য অর্ডার করার জন্য আপনাকে আগে লগইন করতে হবে। </p>
    <button 
        wire:click="redirectToGoogle"
        class="flex items-center gap-3 px-6 py-3 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 hover:cursor-pointer transition duration-200"
    >
        <!-- The Google Icon -->
       <img src="https://res.cloudinary.com/dq7jdy5xy/image/upload/v1781240987/google_ceygiu.jpg"  class="w-5 h-5" alt="Google Logo">
        
        <span class="font-semibold text-gray-700">গুগল দিয়ে লগইন করুন</span>
    </button>
</div>