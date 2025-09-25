<x-layouts.auth>
    <div class="w-full bg-white rounded-2xl max-w-2xl shadow-lg p-8 md:p-12">
        <!-- Heading -->
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold text-gray-900">Create an Account</h2>
            <p class="text-sm text-gray-500 mt-2">Fill in your details to register</p>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('admin.register.update') }}" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @csrf

            <!-- Role -->
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700">Role</label>
                <select name="role"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-1 focus:ring-2 focus:ring-[#FFE6CE] focus:border-[#FFE6CE]">
                    <option value="">Choose Role</option>
                    <option value="1">Admin</option>
                    <option value="2">User</option>
                </select>
                @error('role')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Full Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Full Name</label>
                <input name="name" type="text"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-1 focus:ring-2 focus:ring-[#FFE6CE] focus:border-[#FFE6CE]" />
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Mobile -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Mobile</label>
                <input name="mobile_number" type="number"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-1 focus:ring-2 focus:ring-[#FFE6CE] focus:border-[#FFE6CE]" />
                @error('mobile_number')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input name="email" type="email"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-1 focus:ring-2 focus:ring-[#FFE6CE] focus:border-[#FFE6CE]" />
                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input name="password" type="password"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-1 focus:ring-2 focus:ring-[#FFE6CE] focus:border-[#FFE6CE]" />
                @error('password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input name="password_confirmation" type="password"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-1 focus:ring-2 focus:ring-[#FFE6CE] focus:border-[#FFE6CE]" />
            </div>

            <!-- Security Code -->
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700">Security Code</label>
                <input name="code" type="text"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-1 focus:ring-2 focus:ring-[#FFE6CE] focus:border-[#FFE6CE]" />
                @error('code')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit -->
            <div class="col-span-2">
                <button type="submit"
                    class="w-full bg-[#FFE6CE] text-black font-semibold py-3 rounded-lg shadow-md hover:bg-[#FFD9B5] transition-all duration-200">
                    Sign Up
                </button>
            </div>
        </form>

        <!-- Footer -->
        <p class="text-sm text-gray-600 text-center mt-6">
            Already have an account?
            <a href="/" class="text-[#FF7F32] font-medium hover:underline">Sign In</a>
        </p>
    </div>
</x-layouts.auth>
