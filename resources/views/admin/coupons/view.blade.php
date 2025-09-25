@extends('layouts.app')

@section('content')
<div class="p-4">
    <div class="flex justify-between mb-4">
        <h2 class="text-2xl font-bold">Coupons</h2>
        <button id="createCouponBtn" class="bg-[#ab5f00] text-white px-4 py-2 rounded">
            Create
        </button>
    </div>

    <div id="success-msg" class="bg-green-100 text-green-800 p-2 rounded mb-4 hidden"></div>

   <div id="couponTable" class="boverflow-x-auto bg-white rounded-xl shadow-md">
    <table class="w-full text-sm text-left text-gray-700 border-collapse">
        <thead>
            <tr class="bg-[#ab5f00] text-white text-sm uppercase tracking-wider">
                <th class="px-3 py-2">Code</th>
                <th class="px-3 py-2">Type</th>
                <th class="px-3 py-2">Apply For</th>
                <th class="px-3 py-2">Status</th>
                <th class="px-3 py-2">Expires At</th>
                <th class="px-4 py-2 text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($coupons as $coupon)
                <tr data-id="{{ $coupon->id }}">
                    <td class="px-4 py-3">{{ $coupon->coupon_code }}</td>
                    <td class="px-4 py-3">
                        @if ($coupon->discount_type == 1)
                            <span class="text-green-600 font-bold">%</span>
                        @else
                            <span class="text-blue-600 font-bold">₹</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">{{ $coupon->apply_for == 1 ? 'Subtotal' : 'Order' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-3 py-1 text-xs font-semibold rounded-full
                            {{ $coupon->status == 1 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $coupon->status == 1 ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">{{ $coupon->expires_at }}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button class="editBtn text-blue-600 hover:text-blue-800 p-2 rounded">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button class="deleteBtn text-red-600 hover:text-red-800 p-2 rounded">
                                <i class="fa-solid fa-delete-left"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $coupons->links() }}
</div>

</div>

{{-- Modals --}}
@include('admin.coupons.modal')
@endsection
@section('customJs')
    <script src="{{ asset('admin/js/coupon.js') }}"></script>
@endsection
