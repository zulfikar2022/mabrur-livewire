<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class () extends Component {
};
?>

 <footer class="mt-6 w-full bg-[#1a63fb] text-white">
    @if (!config('services.is_for_department'))
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4  justify-items-center px-4 md:px-8 py-6 bg-[#1a63fb] ">
        
        <div class="flex flex-col gap-5 md:gap-15 items-center md:items-start">
            <div class="flex justify-center space-x-4 text-white">
                <a href="https://www.facebook.com/mabrurhut" target="_blank" class="">
                    <img src="https://ik.imagekit.io/mabrurhut/logos/facebook_logo_qknef0.png?tr=w-40,h-40,f-avif,f-webp" 
                         alt="Facebook Icon" 
                         width="40" 
                         height="40" 
                         loading="lazy" 
                         class="h-10 w-10 rounded-2xl">
                </a>
                <a href="https://wa.me/8801677520339" target="_blank" class=" ">
                    <img src="https://ik.imagekit.io/mabrurhut/logos/whatsapp-logo_e9ncdm.avif?tr=w-40,h-40,f-avif,f-webp" 
                         alt="Whatsapp Icon" 
                         width="40" 
                         height="40" 
                         loading="lazy" 
                         class="h-10 w-10 rounded-2xl">
                </a>
            </div>
            <div class="">
                <p class="text-sm mb-[-3]">আমাদের অফিসের ঠিকানাঃ <span class="font-bold">টঙ্গী, গাজীপুর।</span> </p>
            </div>
        </div>
        <div class="text-white flex flex-col gap-2 items-center md:items-start">
            <h3 class="text-lg font-bold mb-2">Quick Links</h3>
            <ul class="space-y-1">
                @if(Auth::check())
                    <li><a href="{{ route('guest.home') }}" class="hover:underline">Home</a></li>
                    <li><a href="{{ route('guest.faq') }}" class="hover:underline">FAQ</a></li>
                    <li><a href="{{ route('user.profile') }}" class="hover:underline">Profile</a></li>
                    <li><a href="{{ route('user.my.orders') }}" class="hover:underline">My Orders</a></li>
                @endif

                @if(!Auth::check())
                    <li><a href="{{ route('guest.home') }}" class="hover:underline">Home</a></li>
                    <!-- login page -->
                    <li><a href="{{ route('login') }}" class="hover:underline">Login</a></li>
                    <li><a href="{{ route('guest.faq') }}" class="hover:underline">FAQ</a></li>
                @endif

                
                
            </ul>
        </div>
        <div class="text-sm text-center md:text-left flex flex-col items-center md:items-start">
            <a href="{{ route('guest.home') }}">
                <img class="h-20 w-auto"  
                     src="https://ik.imagekit.io/mabrurhut/logos/cropped-mabrur-logo.png?tr=h-80,f-avif,f-webp" 
                     alt="Logo"
                     height="80"
                     width="240"
                     loading="lazy">
            </a>
            <p class="mt-6">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
    @endif
    
    <div data-key="{{ ! config('services.is_for_department') }}" class="bg-slate-800 text-white py-1">
        @if (config('services.is_for_department'))
            <p class="text-center">All rights reserved</p>
        @else
            <div class="container mx-auto text-center text-sm">
                This website is developed by <a href="https://github.com/zulfikar2022" target="_blank" class="underline">Sayed Zulfikar Mahmud</a> and <a href="https://github.com/ADIBA-ANJUM-HSTU" target="_blank" class="underline">Adiba Anjum</a> 
            </div>
        @endif
    </div>
</footer>