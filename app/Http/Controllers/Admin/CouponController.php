<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
     public function index(Request $request)
    {
        $coupons = Coupon::latest()->paginate(10);

        return view('admin.coupons.view', compact('coupons'));
    }

    public function store(Request $request)
{
    $data = $request->validate([
        'coupon_code' => 'required|string|max:255',
        'discount_type' => 'required|in:1,2',
        'discount_value' => 'required|numeric',
        'description' => 'required|string',
        'apply_for' => 'required|in:1,2',
        'max_price' => 'nullable|numeric',
        'min_price' => 'nullable|numeric',
        'order_count' => 'nullable|integer',
        'expires_at' => 'required|date',
        'status' => 'required|boolean'
    ]);

    $coupon = new \App\Models\Coupon();

    $coupon->coupon_code = $data['coupon_code'];
    $coupon->discount_type = $data['discount_type'];
    $coupon->discount_value = $data['discount_value'];
    $coupon->description = $data['description'];
    $coupon->apply_for = $data['apply_for'];
    $coupon->max_price = $data['max_price'] ?? null;
    $coupon->min_price = $data['min_price'] ?? null;
    $coupon->order_count = $data['order_count'] ?? null;
    $coupon->expires_at = $data['expires_at'];
    $coupon->status = $data['status'];

    $coupon->save();

    return response()->json(['success' => 'Coupon added successfully']);
}

public function update(Request $request, $id)
{
    $coupon = \App\Models\Coupon::findOrFail($id);

    $data = $request->validate([
        'coupon_code' => 'required|string|max:255',
        'discount_type' => 'required|in:1,2',
        'discount_value' => 'required|numeric',
        'description' => 'required|string',
        'apply_for' => 'required|in:1,2',
        'max_price' => 'nullable|numeric',
        'min_price' => 'nullable|numeric',
        'order_count' => 'nullable|integer',
        'expires_at' => 'required|date',
        'status' => 'required|boolean'
    ]);

    $coupon->coupon_code = $data['coupon_code'];
    $coupon->discount_type = $data['discount_type'];
    $coupon->discount_value = $data['discount_value'];
    $coupon->description = $data['description'];
    $coupon->apply_for = $data['apply_for'];
    $coupon->max_price = $data['max_price'] ?? null;
    $coupon->min_price = $data['min_price'] ?? null;
    $coupon->order_count = $data['order_count'] ?? null;
    $coupon->expires_at = $data['expires_at'];
    $coupon->status = $data['status'];

    $coupon->save();

    return response()->json(['success' => 'Coupon updated successfully']);
}

   public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return response()->json(['success' => 'Coupon deleted successfully']);
    }

}
