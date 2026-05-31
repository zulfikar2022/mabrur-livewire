<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    // 2. Handle the callback from Google
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Exception $e) {
            return redirect()->route('login')->with('error', 'Google login failed.');
        }

        // 1. Look for an existing user by email
        $user = User::where('email', $googleUser->email)->first();

        if (!$user) {
            $response = Http::get($googleUser->getAvatar());
            if ($response->successful()) {
                $filename = 'avatars/' . Str::slug($googleUser->name) . '_' . $googleUser->id . '.jpg';
                Storage::disk('public')->put($filename, $response->body());
            } else {
                $filename = 'avatars/default.jpg';
            }

            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'profile_image' => $filename,
                'status' => 'active',
            ]);

            $defaultRole = env('DEFAULT_ROLE', 'user');
            $user->assignRole($defaultRole);
        }


        Auth::login($user);

        return redirect()->route('home');
    }
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home'); // Send them back to the welcome/login page
    }
}
