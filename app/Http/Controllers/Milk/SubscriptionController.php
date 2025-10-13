<?php

namespace App\Http\Controllers\Milk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::orderBy('id')->get();

        $planDetails = $subscriptions->map(function ($plan) {
              $duration = null;
            if ($plan->min_duration !== null && $plan->max_duration !== null && $plan->plan_duration_unit) {
                $duration = $plan->min_duration . '-' . $plan->max_duration . ' ' . $plan->plan_duration_unit;
            }
            return [
                'plan_id' => $plan->id,
                'plan_amount' => $plan->plan_amount,
                'plan_pack' => $plan->plan_pack,
                'plan_type' => $plan->plan_type,
                'plan_duration' => $duration,
                'plan_details' => $plan->plan_details ?? [],
                'quantity' => $plan->quantity ?? [],
                'pack' => $plan->pack ?? [],
                'delivery_days' => $plan->delivery_days ?? [],
            ];
        });

        return response()->json([
            'status' => 200,
            'message' => 'fetch subscription details successfully.',
            'response' => [
                'plan_details' => $planDetails,
                'createdAt' => now()->format('d/m/Y'),
            ],
        ]);
    }
}
