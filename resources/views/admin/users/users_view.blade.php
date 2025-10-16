<x-layouts.app>
    <a href="{{ route('lists.users', ['id' => request()->id]) }}" class="inline-flex items-center text-gray-600 hover:text-gray-800 mb-4">
        <i class="fa-solid fa-arrow-left mr-2"></i> Back
    </a>

    <div class="p-6 max-w-6xl mx-auto" x-data="{ open: false }">
        <h2 class="text-2xl font-bold mb-6 capitalize">{{ $user->name }}'s Account</h2>

        <!-- Two-column layout -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Account Details -->
            <div class="bg-gradient-to-b from-[#CAF1DE] to-[#E1F8DC] border border-gray-200 shadow-md rounded-2xl p-6">
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    @if ($user->image)
                        <img src="{{ asset('storage/' . $user->image) }}" class="h-10 w-10 rounded-lg border object-cover" alt="User Image">
                    @else
                        <i class="fa-solid fa-user text-blue-500"></i>
                    @endif
                    Account Details
                </h3>

                <div class="space-y-2 text-gray-700">
                    <p><span class="font-medium">Account Holder Name:</span> {{ $user->account_holder_name ?? '—' }}</p>
                    <p><span class="font-medium">Bank Name:</span> {{ $user->bank_name ?? '—' }}</p>
                    <p><span class="font-medium">Account Number:</span> {{ $user->account_number ?? '—' }}</p>
                    <p><span class="font-medium">IFSC Code:</span> {{ $user->ifsc_code ?? '—' }}</p>
                </div>

                <div class="mt-6">
                    @if ($user->account_holder_name || $user->bank_name || $user->account_number || $user->ifsc_code)
                        <button
                            @click="document.querySelector('#addAccountModal').__x.$data.open = true"
                            class="bg-[#ab5f00] hover:bg-[#8b4d00] transition text-white px-4 py-2 rounded-lg shadow update_account"
                            data-id="{{ $user->id }}"
                            data-account_holder_name="{{ $user->account_holder_name ?? '' }}"
                            data-bank_name="{{ $user->bank_name ?? '' }}"
                            data-account_number="{{ $user->account_number ?? '' }}"
                            data-ifsc_code="{{ $user->ifsc_code ?? '' }}">
                            Update Account
                        </button>
                    @else
                        <button
                            @click="document.querySelector('#addAccountModal').__x.$data.open = true"
                            class="bg-[#276221] hover:bg-[#52a447] transition text-white px-4 py-2 rounded-lg shadow add_account">
                            Add Account
                        </button>
                    @endif
                </div>
            </div>

            <!-- Wallet Section -->
            <div class="bg-gradient-to-b from-[#FBE6D4] to-[#F7D8BA] border border-gray-200 shadow-md rounded-2xl p-6">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="text-lg font-semibold flex items-center gap-2">
                            <i class="fa-solid fa-wallet text-green-600"></i> Wallet
                        </h3>
                        <p class="text-gray-700 mt-1">
                            Balance: <span class="font-semibold text-green-600">₹ {{ $user->get_wallet->balance ?? 0 }}</span>
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 mt-6">
                    <a href="{{ route('transaction_history.users', ['id' => request()->id]) }}"
                        class="bg-[#ab5f00] hover:bg-[#8b4d00] transition text-white px-4 py-2 rounded-lg shadow flex items-center gap-2">
                        <i class="fa-solid fa-receipt"></i> Transaction History
                    </a>

                    <button
                        @click="document.querySelector('#addWalletModal').__x.$data.open = true"
                        class="bg-[#ab5f00] hover:bg-[#8b4d00] transition text-white px-4 py-2 rounded-lg shadow">
                        Add / Deduct
                    </button>
                </div>
            </div>

        </div>

        @include('admin.users.add_wallet_modal')
     </div>

    </x-layouts.app>
    <script src="{{ asset('admin/js/wallet.js') }}"></script>
