<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserSubscription;
use App\Models\Subscription;
use App\Models\User;
use App\Models\DeliveryPartner;
use App\Models\DailyDelivery;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateDailyDeliveries extends Command
{
    protected $signature = 'deliveries:generate';
    protected $description = 'Generate daily deliveries automatically for active user subscriptions';

    public function handle()
    {
        $today = Carbon::today();
        $this->info("=== Starting Daily Delivery Generation for {$today->toDateString()} ===");

        $subscriptions = UserSubscription::where('status', 1)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->warn("No active subscriptions found for today.");
            return;
        }

        foreach ($subscriptions as $sub) {
            $this->line("--------------------------------------------------------");
            $this->info("Processing Subscription ID: {$sub->id} for User ID: {$sub->user_id}");

            // Check if already exists for today
            $exists = DailyDelivery::where('user_id', $sub->user_id)
                ->whereDate('delivery_date', $today)
                ->exists();

            if ($exists) {
                $this->warn("Skipped: Already inserted for user {$sub->user_id} on {$today->toDateString()}");
                continue;
            }

            $user = User::find($sub->user_id);
            $plan = Subscription::find($sub->subscription_id);

            if (!$user || !$plan) {
                $this->warn("Skipped: Missing user or plan for subscription {$sub->id}");
                continue;
            }

            if ($sub->status != 1) {
                $this->warn("Skipped: Subscription {$sub->id} inactive.");
                continue;
            }

            $this->info("Found user {$user->id} and plan {$plan->id}");

            // Calculate per-day amount
            $amount = $this->calculatePerDayAmount($plan, $sub);
            $wallet_check = Wallet::where('user_id', $user->id)->first();

            if (!$wallet_check) {
                $this->warn("Skipped: No wallet found for user {$user->id}");
                continue;
            }

            if ($wallet_check->balance < $amount) {
                $this->warn("Skipped: insufficient balance for User ID {$user->id}.");
                continue;
            }

            $this->info("Calculated per-day amount: {$amount}");

            // Find mapped delivery partner based on polygon match
            $deliveryPartner = $this->getMappedDeliveryPartner($user);

            if (!$deliveryPartner) {
                $this->warn("Skipped: No nearby delivery partner found for user {$user->id}");
                continue;
            }

            $this->info("Found mapped Delivery Partner: {$deliveryPartner->id}");

            // Insert new daily delivery record
            DB::transaction(function () use ($user, $amount, $deliveryPartner, $sub, $today) {
                $daily = new DailyDelivery();
                $daily->user_id = $user->id;
                $daily->subscription_id = $sub->id;
                $daily->delivery_id = $deliveryPartner->id;
                $daily->amount = $amount;
                $daily->delivery_date = $today;
                $daily->delivery_status = 'pending';
                $daily->image = '';
                $daily->save();
                echo "Inserted DailyDelivery ID: {$daily->id} for User {$user->id}\n";
            });
        }

        $this->info("Daily deliveries processed successfully!");
    }

    /**
     * Calculate per-day amount based on plan type and actual duration.
     */
    private function calculatePerDayAmount($plan, $userSubscription)
    {
        if (strtolower($plan->plan_type) === 'customize') {
            return round((float)$plan->plan_amount, 2);
        }

        $startDate = Carbon::parse($userSubscription->start_date);
        $endDate   = Carbon::parse($userSubscription->end_date);

        $totalDays = $startDate->diffInDays($endDate) + 1;
        if ($totalDays <= 0) {
            $totalDays = 1;
        }

        $totalAmount = (float)$plan->plan_amount;
        $totalCents = (int) round($totalAmount * 100);
        $perDayCents = intdiv($totalCents, $totalDays);

        return round($perDayCents / 100, 2);
    }

    /**
     * Find nearest delivery partner that covers user's location.
     */
    private function getMappedDeliveryPartner($user)
    {
        $partners = DeliveryPartner::with('get_map_address', 'get_hub')->get();
        $this->info("Checking delivery partners for user {$user->id}");

        foreach ($partners as $partner) {

            if ($partner->get_hub->type == 1) {
                $this->warn("Polygon not created for Partner ID {$partner->id} in this Hub.");
                continue;
            }

            if (!$partner->get_map_address) {
                    $this->warn("Partner {$partner->id} has no mapped address");
                    continue;
            }

            // Decode the coordinates (some DBs store escaped JSON)
            $rawCoordinates = $partner->get_map_address->coordinates;
            $decoded = json_decode($rawCoordinates, true);
            if (!is_array($decoded)) {
                $decoded = json_decode(stripslashes($rawCoordinates), true);
            }

            if (!is_array($decoded) || count($decoded) < 3) {
                $this->warn("Partner {$partner->id} invalid coordinates data");
                continue;
            }

            $this->line("Checking Partner {$partner->id} Polygon Points...");
            $inside = $this->isPointInPolygon(
                (float)$user->latitude,
                (float)$user->longitude,
                $decoded
            );

            if ($inside) {
                $this->info("User {$user->id} is inside Partner {$partner->id} mapped area.");
                return $partner;
            } else {
                $this->line("User {$user->id} not inside Partner {$partner->id} polygon.");
            }
        }

        $this->warn("No delivery partner found covering user {$user->id}");
        return null;
    }

    /**
     * Check if a point (lat/lng) lies inside a polygon.
     */
    private function isPointInPolygon($lat, $lng, array $polygon)
    {
        $inside = false;
        $numPoints = count($polygon);
        $j = $numPoints - 1;

        for ($i = 0; $i < $numPoints; $i++) {
            $lat_i = (float)$polygon[$i]['lat'];
            $lng_i = (float)$polygon[$i]['lng'];
            $lat_j = (float)$polygon[$j]['lat'];
            $lng_j = (float)$polygon[$j]['lng'];

            $intersect = (($lng_i > $lng) != ($lng_j > $lng)) &&
                ($lat < ($lat_j - $lat_i) * ($lng - $lng_i) / (($lng_j - $lng_i) ?: 1e-9) + $lat_i);

            if ($intersect) {
                $inside = !$inside;
            }

            $j = $i;
        }

        return $inside;
    }
}
