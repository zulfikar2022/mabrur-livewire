<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-slate-900 text-slate-100 min-h-screen font-sans antialiased relative"
      x-data="{ isFacebookWebView: false, isIOS: false }"
      x-init="
         const ua = navigator.userAgent || navigator.vendor || window.opera;
         const isFB = ua.indexOf('FBAN') > -1 || ua.indexOf('FBAV') > -1 || ua.indexOf('Instagram') > -1;
         
         if (isFB) {
             isFacebookWebView = true;
             isIOS = /iPad|iPhone|iPod/.test(ua) && !window.MSStream;

             // ANDROID ESCAPE: Instantly force-open Google Chrome
             if (!isIOS) {
                 const cleanUrl = window.location.href.replace(/^https?:\/\//, '');
                 window.location.href = 'intent://' + cleanUrl + '#Intent;scheme=https;package=com.android.chrome;end';
             }
         }
      }">

    <template x-if="isFacebookWebView && isIOS">
        <div class="fixed inset-0 bg-slate-950 z-99999 flex flex-col items-center justify-center p-6 text-white text-center">
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

                <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 text-left text-xs sm:text-sm space-y-3">
                    <div class="flex gap-3 items-start">
                        <span class="flex items-center justify-center bg-emerald-500 text-slate-950 rounded-full h-5 w-5 text-xs font-bold shrink-0 mt-0.5">1</span>
                        <p class="text-slate-300">Tap the **three dots (... )** or menu icon in the top right corner of your screen.</p>
                    </div>
                    <div class="flex gap-3 items-start">
                        <span class="flex items-center justify-center bg-emerald-500 text-slate-950 rounded-full h-5 w-5 text-xs font-bold shrink-0 mt-0.5">2</span>
                        <p class="text-slate-300">Select <span class="text-white font-semibold underline">"Open in Browser"</span> or <span class="text-white font-semibold underline">"Open in Safari"</span>.</p>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <livewire:super-admin.sidebar />

    <div class="lg:pl-64 flex flex-col min-h-screen">
        
        <header class="h-16 bg-slate-800 border-b border-slate-700 px-6 lg:px-8 flex items-center justify-between sticky top-0 z-30 shadow-md">
            <div class="flex items-center space-x-3 pl-12 lg:pl-0">
                <i class="fa-solid fa-compass text-emerald-400 text-2xl"></i>
                <span class="font-bold tracking-wider uppercase text-slate-200">MabrurHut</span>
            </div>

            @auth
                <div class="relative flex items-center space-x-3" x-data="{ open: false }">
                    
                    <button @click="open = !open" @click.outside="open = false" class="flex items-center space-x-3 focus:outline-none group">
                        <span class="font-medium text-slate-300 group-hover:text-emerald-400 transition text-sm hidden sm:inline-block">
                            {{ auth()->user()->name }}
                        </span>
                        <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-slate-600 group-hover:border-emerald-500 transition bg-slate-700 flex items-center justify-center shadow-inner relative">
                            @if(auth()->user()->profile_image)
                                <img src="{{ asset('storage/' . auth()->user()->profile_image) }}" 
                                     alt="{{ auth()->user()->name }}" 
                                     class="w-full h-full object-cover">
                            @else
                                <i class="fa-solid fa-user text-slate-400"></i>
                            @endif
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs text-slate-400 group-hover:text-emerald-400 transition pt-1"></i>
                    </button>

                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 top-12 mt-2 w-48 bg-slate-800 border border-slate-700 rounded-lg shadow-xl py-1 z-50"
                         style="display: none;">
                        
                        <div class="px-4 py-2 border-b border-slate-700 text-xs text-slate-400 uppercase font-semibold tracking-wider">
                            System Options
                        </div>

                        <a href="{{ route('super-admin.logout') }}" class="flex items-center space-x-2 px-4 py-2 text-sm text-rose-400 hover:bg-slate-700 font-medium transition">
                            <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
                            <span>Log Out</span>
                        </a>
                    </div>

                </div>
            @endauth
        </header>

        <main class="p-6 lg:p-8 grow">
            {{ $slot }}
        </main>
        
    </div>

    @livewireScripts
</body>
</html>