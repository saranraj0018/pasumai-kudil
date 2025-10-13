<x-layouts.app>
    <a href="{{ route('lists.users', ['id' => request()->id]) }}"><i class="fa-solid fa-arrow-left">‌</i></a>
    <div class="p-6 max-w-6xl mx-auto" x-data="{ open: false }">
        <h2 class="text-2xl font-bold mb-6">{{ $user->name }}'s Account</h2>
        <!-- Two-column Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Left: Account Details -->
            <div
                class="bg-white shadow-md rounded-2xl p-6 border border-gray-200 bg-gradient-to-b from-[#E0F7FA] to-[#B2EBF2] shadow-lg p-6 rounded-2xl">
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    @if ($user->image)
                        <img src="{{ asset('storage/' . $user->image) }}"
                            class="h-10 w-10 object-cover rounded-lg shadow-sm border" />
                    @else
                        <i class="fa-solid fa-user text-blue-500"></i>
                    @endif
                    Account Details
                </h3>
                <div class="space-y-2 text-gray-700">
                    <p><span class="font-medium">Email:</span> {{ $user->email }}</p>
                    <p><span class="font-medium">Phone:</span> {{ $user->mobile_number }}</p>
                </div>
            </div>

            <!-- Right: Wallet + Transactions -->
            <div
                class="bg-white shadow-md rounded-2xl p-6 border border-gray-200 bg-gradient-to-b from-[#F7D8BA] shadow-lg">
                <!-- Wallet Section -->
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="text-lg font-semibold flex items-center gap-2">
                            <i class="fa-solid fa-wallet text-green-500"></i> Wallet
                        </h3>
                        <p class="text-gray-700 mt-1">
                            Balance: <span class="font-semibold text-green-600">₹
                                {{ $user->get_wallet->balance ?? 0 }}</span>
                        </p>
                    </div>
                </div>
                <!-- Transaction History -->
                <div class="flex items-center justify-between mb-5">
                    <!-- Left: Transaction History -->
                    <a href="{{ route('transaction_history.users', ['id' => request()->id]) }}"
                        class="bg-blue-600 hover:bg-blue-700 text-black px-4 py-2 rounded-lg transition duration-200 flex items-center">
                        <i class="fa-solid fa-receipt text-purple-500"></i>
                        Transaction History
                    </a>

                    <!-- Right: Add / Deduct button -->
                    <div class="flex justify-between mb-4">
                         <button @click="document.querySelector('#addWalletModal').__x.$data.open = true"
                            class="bg-[#ab5f00] text-white px-4 py-2 rounded">
                            Add / Deduct
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @include('admin.users.add_wallet_modal')
    </div>
</x-layouts.app>
<script src="{{ asset('admin/js/wallet.js') }}"></script>
