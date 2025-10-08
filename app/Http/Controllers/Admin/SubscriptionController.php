<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;

class SubscriptionController extends Controller
{
    public function view(Request $request)
    {
        $subscriptions = Subscription::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.subscription.view', compact('subscriptions'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'plan_id' => 'nullable|integer',
            'plan_amount' => 'required|numeric',
            'plan_pack' => 'required|string|max:255',
            'plan_type' => 'required|string|max:255',
            'plan_duration' => 'nullable|integer',
            'min_duration' => 'required|integer|min:1',
            'max_duration' => 'required|integer|gte:min_duration',
            'plan_duration_unit' => 'required|in:days,months',
            'plan_details' => 'nullable|string',
            'quantity' => 'nullable|string',
            'pack' => 'nullable|string',
            'delivery_days' => 'nullable|string',
        ]);

        $sub = $request->id ? Subscription::find($request->id) : new Subscription();

        if($request->id && !$sub){
            return response()->json(['success' => false, 'message' => 'Subscription not found'], 404);
        }

        $sub->plan_id = $request->plan_id;
        $sub->plan_amount = $request->plan_amount;
        $sub->plan_pack = $request->plan_pack;
        $sub->plan_type = $request->plan_type;
        $sub->plan_duration = $request->plan_duration;
        $sub->min_duration = $request->min_duration;
        $sub->max_duration = $request->max_duration;
        $sub->plan_duration_unit = $request->plan_duration_unit; // store unit
        $sub->plan_details = $request->plan_details;
        $sub->quantity = $request->quantity;
        $sub->pack = $request->pack;
        $sub->delivery_days = $request->delivery_days;

        $sub->save();

        $message = $request->id ? 'Subscription updated successfully' : 'Subscription created successfully';

        return response()->json(['success' => true, 'message' => $message, 'subscription' => $sub]);
    }

    public function destroy(Request $request)
    {
        if (!$request->id) return response()->json(['success' => false, 'message' => 'Subscription ID is required'], 400);

        $sub = Subscription::find($request->id);
        if (!$sub) return response()->json(['success' => false, 'message' => 'Subscription not found'], 404);

        $sub->delete();
        return response()->json(['success' => true, 'message' => 'Subscription deleted successfully']);
    }
}
