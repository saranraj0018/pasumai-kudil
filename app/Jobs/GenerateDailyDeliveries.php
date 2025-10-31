<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\UserSubscription;
use App\Models\DailyDelivery;
use App\Models\Wallet;
use App\Models\DeliveryPartner;
use App\Models\Subscription;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Carbon\Carbon;

class GenerateDailyDeliveries implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected $subscription;
    protected $dates;

    public function __construct(UserSubscription $subscription, $dates)
    {
        $this->subscription = $subscription;
        $this->dates = $dates;
    }

    public function handle()
    {
        $sub = $this->subscription;
        $user = User::find($sub->user_id);
        $plan = Subscription::find($sub->subscription_id);
        if (!$user || !$plan || $sub->status != 1) {
            return;
        }
        // $wallet = Wallet::where('user_id', $user->id)->first();
        // if (!$wallet) return;
        $amount = $this->calculatePerDayAmount($plan, $sub);
        $partner = $this->getMappedDeliveryPartner($user);
        if (!$partner) return;
        DB::transaction(function () use ($user, $sub, $amount, $partner) {
            foreach ($this->dates as $date) {
                $exists = DailyDelivery::where('user_id', $user->id)
                    ->whereDate('delivery_date', $date)
                    ->exists();
                if (!$exists) {
                    $daily = new DailyDelivery();
                    $daily->user_id = $user->id;
                    $daily->subscription_id = $sub->id;
                    $daily->delivery_id = $partner->id;
                    $daily->amount = $amount;
                    $daily->delivery_date = $date;
                    $daily->delivery_status = 'pending';
                    $daily->quantity =  $sub->quantity;
                    $daily->pack =  $sub->pack;
                    $daily->image = '';
                    $daily->save();
                }
            }
        });
    }

    private function calculatePerDayAmount($plan, $sub)
    {
        if (strtolower($plan->plan_type) === 'customize') {
            return round((float)$plan->plan_amount, 2);
        }

        $startDate = Carbon::parse($sub->start_date);
        $endDate   = Carbon::parse($sub->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;
        $totalDays = $totalDays > 0 ? $totalDays : 1;

        $totalAmount = (float)$plan->plan_amount;
        $perDay = $totalAmount / $totalDays;

        return round($perDay, 2);
    }

    private function getMappedDeliveryPartner($user)
    {
        $partners = DeliveryPartner::with('get_map_address', 'get_hub')->get();

        foreach ($partners as $partner) {
            if ($partner->get_hub->type == 1) continue;
            if (!$partner->get_map_address) continue;

            $coords = json_decode($partner->get_map_address->coordinates, true);
            if (!is_array($coords)) continue;

            if ($this->isPointInPolygon((float)$user->latitude, (float)$user->longitude, $coords)) {
                return $partner;
            }
        }
        return null;
    }

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
