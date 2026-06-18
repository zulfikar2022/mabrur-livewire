<?php

use Livewire\Component;

new class () extends Component {
    //
};
?>

 <footer class="mt-6 w-full bg-[#1a63fb] text-white">
    @if (!config('services.is_for_department'))
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4  justify-items-center px-4 md:px-8 py-6 bg-[#1a63fb] ">
        
        <div class="flex flex-col gap-5 md:gap-15 items-center md:items-start">
            <div class="flex justify-center space-x-4 text-white">
                <a href="https://www.facebook.com/mabrurhut" target="_blank" class="">
                <img src="https://ik.imagekit.io/mabrurhut/logos/facebook_logo_qknef0.png" alt="Facebook Icon" class="h-10 w-10 rounded-2xl">
                </a>
                <a href="https://wa.me/8801677520339" target="_blank" class=" ">
                    <img src="https://ik.imagekit.io/mabrurhut/logos/whatsapp-logo_e9ncdm.avif" alt="Whatsapp Icon" class="h-10 w-10 rounded-2xl">
                </a>
            </div>
            <div class="">
                <p class="text-sm mb-[-3]">আমাদের অফিসের ঠিকানাঃ <span class="font-bold">টঙ্গী, গাজীপুর।</span> </p>
            </div>
        </div>
        <div class="text-sm text-center md:text-left flex flex-col items-center md:items-start">
            <a href="{{ route('home') }}"><img class="h-20 w-auto"  src="https://ik.imagekit.io/mabrurhut/logos/cropped-mabrur-logo.png" alt="Logo"></a>
            <p class="mt-6">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
    @endif
    
    <div data-key="{{ ! config('services.is_for_department') }}" class="bg-slate-800 text-white py-1">
        @if (config('services.is_for_department'))
            <p class="text-center">All rights reserved</p>
        @else
            <div class="container mx-auto text-center text-sm">
                This website is developed by <a href="https://github.com/zulfikar2022" target="_blank" class="underline">Sayed Zulfikar Mahmud</a> | WhatsApp: +8801309417042
            </div>
        @endif
    </div>
</footer>