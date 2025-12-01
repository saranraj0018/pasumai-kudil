<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;

class SubscriptionController extends Controller
{
    public function view(Request $request)
    {
        $subscriptions = Subscription::with('get_user')->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.subscription.view', compact('subscriptions'));
    }
    public function save(Request $request)
    {
        $request->validate([
            'plan_amount' => 'required|numeric',
            'plan_pack' => 'required_if:plan_type,Basic,Best Value|nullable|numeric',
            'plan_type' => 'required|in:Basic,Best Value,Customize',
            'plan_duration' => 'required|string|max:255',
            'plan_details' => 'nullable|string',
            'quantity' => 'nullable|string',
            'pack' => 'nullable|string',
            'plan_name' => 'required|string',
            'delivery_days' => 'nullable|string',
        ]);
      try {
        $sub = $request->id ? Subscription::find($request->id) : new Subscription();
        if ($request->id && !$sub) {
            return response()->json(['success' => false, 'message' => 'Subscription not found'], 404);
        }
        $planDetailArray = array_map('trim', explode(',', $request->plan_details));
        $sub->plan_amount = $request->plan_amount;
        $sub->plan_pack = $request->plan_type !== 'Customize' ? (int)$request->plan_pack : 0;
        $sub->plan_name = $request->plan_name;
        $sub->plan_type = $request->plan_type;
        $sub->plan_duration = $request->plan_duration;
        $sub->plan_details = $planDetailArray ?? null;
        $sub->quantity = $request->quantity ?? null;
        $sub->pack = $request->pack ?? null;
        $sub->delivery_days = $request->plan_type === 'Customize' ? $request->delivery_days : null;
        $sub->is_show_mobile = $request->is_show_mobile ?? 0;
        $sub->save();
        $message = $request->id ? 'Subscription updated successfully' : 'Subscription created successfully';
        return response()->json(['success' => true, 'message' => $message]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}
    public function destroy(Request $request)
    {
        if (!$request->id) {
            return response()->json(['success' => false, 'message' => 'Subscription ID is required'], 400);
        }

        $sub = Subscription::find($request->id);
        if (!$sub) {
            return response()->json(['success' => false, 'message' => 'Subscription not found'], 404);
        }

        $sub->delete();
        return response()->json(['success' => true, 'message' => 'Subscription deleted successfully']);
    }
}
