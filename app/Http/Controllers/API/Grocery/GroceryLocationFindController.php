<?php

namespace App\Http\Controllers\API\Grocery;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\DeliveryPartner;
use App\Models\Hub;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GroceryLocationFindController extends Controller
{
    public function GroceryLocationFind(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 409,
                'message' => $validator->errors()->first(),
            ], 200);
        }

        $userId = auth()->id();
        if (!$userId) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthenticated'
            ], 401);
        }
        Cache::forget("inside_grocery_zone:user:{$userId}");
        $cacheKey = "inside_grocery_zone:user:{$userId}";
        if (Cache::has($cacheKey)) {
            return response()->json([
                'status' => 200,
                'inside_grocery_zone' => Cache::get($cacheKey),
            ]);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'status' => 404,
                'message' => 'User not found'
            ], 404);
        }

        $user->latitude  = (float) $request->latitude;
        $user->longitude = (float) $request->longitude;

        $isInside = $this->isUserInsideGroceryHub($user);

        Cache::put($cacheKey, $isInside, now()->addMinutes(10));

        return response()->json([
            'status' => 200,
            'inside_grocery_zone' => $isInside,
        ]);
    }



    private function isUserInsideGroceryHub(User $user): bool
    {
        $hub = Hub::with('get_city')
            ->where('type', 1)
            ->first();

        if (!$hub || !$hub->get_city || empty($hub->get_city->coordinates)) {
            return false;
        }

        $polygonRaw = json_decode($hub->get_city->coordinates, true);

        if (!is_array($polygonRaw) || count($polygonRaw) < 3) {
            return false;
        }

        // ✅ Normalize polygon (fix key names & cast to float)
        $polygon = [];
        foreach ($polygonRaw as $point) {
            $polygon[] = [
                'lat' => (float) ($point['lat'] ?? $point['latitude']),
                'lng' => (float) ($point['lng'] ?? $point['longitude']),
            ];
        }

        // ✅ Close polygon if not closed
        if ($polygon[0] !== end($polygon)) {
            $polygon[] = $polygon[0];
        }

        return $this->isPointInPolygon(
            (float) $user->longitude, // X
            (float) $user->latitude,  // Y
            $polygon
        );
    }




    private function isPointInPolygon(float $x, float $y, array $polygon): bool
    {
        $inside = false;
        $n = count($polygon);

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {

            $xi = $polygon[$i]['lng'];
            $yi = $polygon[$i]['lat'];
            $xj = $polygon[$j]['lng'];
            $yj = $polygon[$j]['lat'];

            if (
                (($yi > $y) !== ($yj > $y)) &&
                ($x < ($xj - $xi) * ($y - $yi) / (($yj - $yi) ?: 1e-10) + $xi)
            ) {
                $inside = !$inside;
            }
        }

        return $inside;
    }
}
