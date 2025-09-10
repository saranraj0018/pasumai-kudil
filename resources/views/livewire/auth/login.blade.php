<?php

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')]
class extends Component {

    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|string|min:6')]
    public string $password = '';

    public bool $showPassword = false;

    public function login()
    {
        $this->validate();
        // Throttle login attempts
        $this->ensureIsNotRateLimited();

        if (!Auth::guard('admin')->attempt(['email' => $this->email, 'password' => $this->password], true)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        throw ValidationException::withMessages([
            'email' => __('Too many login attempts. Please try again later.'),
        ]);
    }

    protected function throttleKey(): string
    {
        return strtolower($this->email) . '|' . request()->ip();
    }
}; ?>

<div class="min-h-screen flex items-center justify-center px-4"
     style="background: linear-gradient(to bottom, #FFE6CE 0%, #ffffff 80%)">

    <div class="w-full max-w-5xl bg-white rounded-3xl shadow-lg overflow-hidden flex border-2 border-[antiquewhite]">

        {{-- Left Image --}}
        <div class="w-1/2 bg-[#fdf6e9] hidden md:flex items-center justify-center p-6">
            <img src="{{ asset('grocery.jpeg') }}"
                 alt="Pasumai Kudil"
                 class="w-full h-full object-cover rounded-l-3xl"/>
        </div>

        {{-- Right Form --}}
        <div class="md:w-1/2 p-10 flex flex-col justify-center">
            <h2 class="text-2xl font-bold text-[#ab5f00] mb-2">Welcome Back</h2>
            <p class="text-sm text-gray-500 mb-6">Sign in to your Pasumai Kudil dashboard</p>

            <form wire:submit.prevent="login" class="space-y-5">
                {{-- Email --}}
                <div>
                    <label class="text-sm text-gray-700">Email</label>
                    <input type="email"
                           wire:model="email"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1
                                  focus:outline-none focus:ring-2 focus:ring-[#ab5f00]"
                           placeholder="you@example.com"/>
                    @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label class="text-sm text-gray-700">Password</label>
                    <div class="relative">
                        <input :type="$wire.showPassword ? 'text' : 'password'"
                               wire:model="password"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1
                                      focus:outline-none focus:ring-2 focus:ring-[#ab5f00] pr-10"
                               placeholder="••••••••"/>
                        <button type="button"
                                wire:click="$toggle('showPassword')"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
                            @if($showPassword)
                                {{-- EyeOff --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M13.875 18.825A10.05 10.05 0 0112 19c-5.523 0-10-4.477-10-10
                                              0-1.342.264-2.621.741-3.787m1.548-2.675A9.956 9.956 0 0112 3c5.523
                                              0 10 4.477 10 10 0 1.342-.264 2.621-.741 3.787m-1.548
                                              2.675L4.29 4.29"/>
                                </svg>
                            @else
                                {{-- Eye --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M2.458 12C3.732 7.943 7.523 5 12
                                              5c4.477 0 8.268 2.943 9.542 7-1.274
                                              4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            @endif
                        </button>
                    </div>
                    @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full bg-[#ab5f00] hover:bg-green-700 text-white py-2 rounded-md transition">
                    Sign In
                </button>
            </form>

            {{-- Register --}}
            <p class="text-sm text-center text-gray-600 mt-6">
                Don’t have an account?
                <a href="{{ route('register') }}" class="text-[#ab5f00] font-medium hover:underline">
                    Sign Up
                </a>
            </p>
        </div>
    </div>
</div>

