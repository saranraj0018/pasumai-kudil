<?php

use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $mobile_number = '';
    public string $role = '';
    public string $code = '';

    public function register(): void
    {
        $validated = $this->validate([
            'role' => ['required'],
            'name' => ['required', 'string', 'max:255'],
            'mobile_number' => ['required', 'digits:10'],
            'email' => ['required', 'email', 'max:255', 'unique:' . Admin::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'code' => ['required', 'string'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = new User();



        Auth::login($user);

        $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
    }
};
?>

<div class="min-h-screen flex items-center justify-center px-4"
     style="background: linear-gradient(to bottom, #FFE6CE 0%, #ffffff 80%);">
    <div class="w-full max-w-2xl bg-white rounded-3xl shadow-xl p-10">
        <h2 class="text-2xl font-bold text-green-800 mb-6">Create an Account</h2>

        <form wire:submit.prevent="register" class="space-y-5">
            <!-- Role -->
            <div>
                <label class="text-sm text-gray-700">Role</label>
                <select wire:model="role"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1 focus:ring-2 focus:ring-green-500">
                    <option value="">Choose Role</option>
                    <option value="1">Admin</option>
                    <option value="2">User</option>
                </select>
                @error('role') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Name -->
            <div>
                <label class="text-sm text-gray-700">Full Name</label>
                <input wire:model="name" type="text" class="w-full border rounded-md px-3 py-2 mt-1" />
                @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Mobile -->
            <div>
                <label class="text-sm text-gray-700">Mobile</label>
                <input wire:model="mobile_number" type="tel" class="w-full border rounded-md px-3 py-2 mt-1" />
                @error('mobile_number') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Email -->
            <div>
                <label class="text-sm text-gray-700">Email</label>
                <input wire:model="email" type="email" class="w-full border rounded-md px-3 py-2 mt-1" />
                @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Password -->
            <div>
                <label class="text-sm text-gray-700">Password</label>
                <input wire:model="password" type="password" class="w-full border rounded-md px-3 py-2 mt-1" />
                @error('password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Confirm -->
            <div>
                <label class="text-sm text-gray-700">Confirm Password</label>
                <input wire:model="password_confirmation" type="password" class="w-full border rounded-md px-3 py-2 mt-1" />
            </div>

            <!-- Security Code -->
            <div>
                <label class="text-sm text-gray-700">Security Code</label>
                <input wire:model="code" type="text" class="w-full border rounded-md px-3 py-2 mt-1" />
                @error('code') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-md">Sign Up</button>
        </form>

        <p class="text-sm text-gray-600 text-center mt-6">
            Already have an account?
            <a href="/" class="text-green-600 hover:underline">Sign In</a>
        </p>
    </div>
</div>
