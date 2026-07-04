<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Not Found</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <!-- create a ui to show the 503 error -->
    <div class=" flex flex-col items-center justify-center min-h-screen bg-gray-100 px-4">
         @php
            // Generate a random seed for consistent randomization on each page load
            $seed = session()->get('error_page_seed', rand());
            session()->put('error_page_seed', $seed);
            $randomColors = ['text-red-500', 'text-green-500', 'text-blue-500', 'text-yellow-500', 'text-purple-500'];
            $randomColor = $randomColors[$seed % count($randomColors)];
        @endphp
        <div class="bg-white rounded-xl  shadow-sm border border-gray-100 p-12 text-center md:w-2/3 lg:w-1/2 mx-auto mt-24">
            <i class="fa-solid fa-triangle-exclamation text-6xl {{ $randomColor }} animate-pulse"></i>
            <h1 class="text-9xl font-black text-blue-600">503</h1>
            <h2 class="text-2xl font-bold text-gray-800 mt-4">Service Unavailable</h2>
            <p class="text-gray-500 mt-2 mb-8">The server is currently unable to handle the request due to temporary overloading or maintenance of the server.</p>
            
            <a href="{{ route('guest.home') }}" wire:navigate 
            class="px-6 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition">
                Go back Home
            </a>
        </div>
</body>
</html>