<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title wire:navigate.update>{{ $title ?? config('app.name') }}</title>

    <meta name="description" content="{{ $metaDescription ?? 'Welcome to ' . config('app.name') . '. Shop the best products at the best prices.' }}">
    <meta name="robots" content="{{ $robots ?? 'index, follow' }}">
    <link rel="canonical" href="{{ $canonical ?? request()->url() }}">

    <meta property="og:type" content="{{ $ogType ?? 'website' }}" />
    <meta property="og:title" content="{{ $title ?? config('app.name') }}" />
    <meta property="og:description" content="{{ $metaDescription ?? 'Welcome to ' . config('app.name') . '. Shop the best products at the best prices.' }}" />
    <meta property="og:url" content="{{ request()->url() }}" />
    <meta property="og:site_name" content="{{ config('app.name') }}" />
    <meta property="og:image" content="{{ $metaImage ?? asset('images/default-social-share.jpg') }}" />
    <meta property="og:image:width" content="{{ $imageWidth ?? '1200' }}" />
    <meta property="og:image:height" content="{{ $imageHeight ?? '630' }}" />

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? config('app.name') }}">
    <meta name="twitter:description" content="{{ $metaDescription ?? 'Welcome to ' . config('app.name') . '.' }}">
    <meta name="twitter:image" content="{{ $metaImage ?? asset('images/default-social-share.jpg') }}">

    <link rel="preload" as="image" href="https://ik.imagekit.io/mabrurhut/logos/mabrur-banner.jpg?updatedAt=1781614071640&tr=w-800,f-avif,f-webp">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"></noscript>
    
    @if(env('GA_MEASUREMENT_ID'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ env('GA_MEASUREMENT_ID') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ env("GA_MEASUREMENT_ID") }}');
            document.addEventListener('livewire:navigated', () => {
                gtag('event', 'page_view', {
                    page_location: window.location.href,
                    page_title: document.title
                });
            });
        </script>
    @endif
</head>
    
<body class="bg-slate-300 flex flex-col min-h-screen relative"
      x-data="{ isFacebookWebView: false, isIOS: false }"
      x-init="
         const ua = navigator.userAgent || navigator.vendor || window.opera;
         // Added 'LinkedInApp' to the detection logic
         const isWebView = ua.indexOf('FBAN') > -1 || 
                           ua.indexOf('FBAV') > -1 || 
                           ua.indexOf('Instagram') > -1 || 
                           ua.indexOf('LinkedInApp') > -1;
         
         if (isWebView) {
             isFacebookWebView = true;
             isIOS = /iPad|iPhone|iPod/.test(ua) && !window.MSStream;

             // ANDROID ESCAPE: Instantly force-open Google Chrome
             if (!isIOS) {
                 const cleanUrl = window.location.href.replace(/^https?:\/\//, '');
                 window.location.href = 'intent://' + cleanUrl + '#Intent;scheme=https;package=com.android.chrome;end';
             }
         }
      ">

        <template x-if="isFacebookWebView && isIOS">
            <div class="fixed inset-0 bg-slate-900 z-[99999] flex flex-col items-center justify-center p-6 text-white text-center">
                <div class="absolute top-4 right-4 animate-bounce text-amber-400 flex flex-col items-center">
                    <i class="fa-solid fa-arrow-up text-2xl"></i>
                    <span class="text-xs font-bold mt-1">Tap Menu here</span>
                </div>

                <div class="max-w-sm space-y-6">
                    <div class="w-20 h-20 bg-amber-500/10 text-amber-400 rounded-2xl flex items-center justify-center mx-auto text-3xl border border-amber-500/30">
                        <i class="fa-solid fa-compass"></i>
                    </div>
                    
                    <div class="space-y-2">
                        <h3 class="text-xl font-bold tracking-tight">Open in Safari for Full Experience</h3>
                        <p class="text-sm text-slate-400 leading-relaxed">
                            To support safe, passwordless Google authentication, our platform requires your default system browser.
                        </p>
                    </div>

                    <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 text-left text-xs sm:text-sm space-y-3">
                        <div class="flex gap-3 items-start">
                            <span class="flex items-center justify-center bg-blue-600 text-white rounded-full h-5 w-5 text-xs font-bold shrink-0 mt-0.5">1</span>
                            <p class="text-slate-300">Tap the **three dots (... )** or menu icon in the top right corner of your screen.</p>
                        </div>
                        <div class="flex gap-3 items-start">
                            <span class="flex items-center justify-center bg-blue-600 text-white rounded-full h-5 w-5 text-xs font-bold shrink-0 mt-0.5">2</span>
                            <p class="text-slate-300">Select <span class="text-white font-semibold underline">"Open in Browser"</span> or <span class="text-white font-semibold underline">"Open in Safari"</span>.</p>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        
        <div x-data="{ 
                showNavbar: true, 
                lastScrollY: window.scrollY 
             }"
             @scroll.window="
                let currentScrollY = window.scrollY;
                // If scrolling down AND past the height of the navbar (approx 80px), hide it. Otherwise show it.
                if (currentScrollY > lastScrollY && currentScrollY > 80) {
                    showNavbar = false;
                } else {
                    showNavbar = true;
                }
                lastScrollY = currentScrollY;
             "
             class="sticky top-0 z-50 transition-transform duration-300 ease-in-out"
             :class="showNavbar ? 'translate-y-0' : '-translate-y-full'">
            <livewire:user.navbar />
        </div>
        <div class="container mx-auto mt-8 grow px-4 md:px-8">
            {{ $slot }}
        </div>

        @livewireScripts
        
       <!-- footer will go here -->
        <livewire:user.footer />
    </body>
</html>