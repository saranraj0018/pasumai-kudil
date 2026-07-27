<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\DeliveryPartner;
use App\Models\Hub;
use App\Services\GeoNameResolver;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DeliveryPartnerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('query');
        $this->data['delivery_partner'] = DeliveryPartner::with('get_hub', 'get_daily_deliveries')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->where('mobile_number', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        $this->data['hub'] = Hub::where('status',1)->get();
        $this->data['search'] = $search;
        $resolver = new GeoNameResolver();
        $cities = City::all();

        $areas = $cities->map(function ($city) use ($resolver) {
            $coords = json_decode($city->coordinates, true);

            if (!is_array($coords) || empty($coords)) {
                return null;
            }

            $places = Cache::remember("city_names_v2_{$city->id}", now()->addDays(30), function () use ($resolver, $coords) {
                $spacing = $resolver->autoSpacingKm($coords);
                return $resolver->resolveAllNamesInPolygon($coords, $spacing);
            });

            // Guard against malformed / legacy cache entries
            $places = collect($places)
                ->filter(fn($p) => is_array($p) && isset($p['name'], $p['lat'], $p['lng']))
                ->values()
                ->all();

            if (empty($places)) {
                return null;
            }

            return [
                'city_id' => $city->id,
                'hub_id'  => $city->hub_id,
                'names'   => $places,
            ];
        })->filter()->values();
        $this->data['areas'] = $areas;
        return view('admin.delivery_partner.index')->with($this->data);
    }

    public function saveDeliveryPartner(Request $request)
    {
        $rules = [
            'name'   => 'required|string|max:255',
            'hub_id'    => 'required',
            'city_id'    => 'required',
            'area_name'    => 'required',
            'mobile_number'    => 'required',
            'lat' => 'required',
            'lng' => 'required',
        ];
        $request->validate($rules);
        try {
            if (!empty($request['delivery_partner_id'])) {
                $message = 'Delivery Partner  updated successfully';
                $delivery_partner = DeliveryPartner::findOrFail($request['delivery_partner_id']);
            } else {
                $delivery_partner = new DeliveryPartner();
                $message = 'Delivery Partner created successfully';
            }

            $delivery_partner->name = $request['name'];
            $delivery_partner->mobile_number = $request['mobile_number'];
            $delivery_partner->hub_id = $request['hub_id'];
            $delivery_partner->area_name =  $request['area_name'];
            $delivery_partner->city_id =  $request['city_id'];
            $delivery_partner->latitude =  $request['lat'];
            $delivery_partner->longitude =  $request['lng'];
            $delivery_partner->save();

            return response()->json([
                'success' => true,
                'message' => $message,
                'delivery_partner' => $delivery_partner,
            ]);
        } catch (Exception $e) {
             return response()->json([
                'success' => false,
                'message' => 'Failed to save Delivery Partner',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteDeliveryPartner(Request $request)
    {
        $delivery_partner = DeliveryPartner::find($request->id);
        if (!$delivery_partner) {
            return response()->json([
                'success' => false,
                'message' => 'Delivery Partner not found'
            ], 404);
        }
        $delivery_partner->delete();
        return response()->json([
            'success' => true,
            'message' => 'Delivery Partner deleted successfully'
        ]);
    }
}
