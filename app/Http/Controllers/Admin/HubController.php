<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Hub;
use App\Models\HubArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HubController extends Controller
{
   public function view() {
       $hub_list = Hub::with('user')->orderBy('created_at', 'desc')->paginate(10);
       return view('admin.hub.view', compact('hub_list'));
   }

   public function citySave(Request $request) {

       $validator = Validator::make($request->all(), [
           'hub_name' => 'required|string',
           'latitude' => 'required|numeric',
           'longitude' => 'required|numeric',
           'status' => 'required|in:0,1',
           'type' => [
               'required',
               'in:1,2',
               function ($attribute, $value, $fail) {
                   if ($value == 1 && \App\Models\Hub::where('type', 1)->exists()) {
                       $fail('Only one record with type = 1 is allowed.');
                   }
               },
           ],
       ]);

       if ($validator->fails()) {
           return response()->json([
               'status' => 409,
               'message' => $validator->errors()->first(),
           ], 409);
       }


       $city = !empty($request['hub_id']) ? Hub::find($request['hub_id']) : new Hub();
       $city->address = $request['hub_name'];
       $city->type = $request['type'];
       $city->name = strstr($request['hub_name'], ',', true) ?: $request['hub_name'];;
       $city->latitude = $request['latitude'];
       $city->longitude = $request['longitude'];
       $city->status = $request['status'];
       $city->user_id = Auth::guard('admin')->id();
       $city->save();

       return response()->json([
           'success' => true,
           'message' => !empty($request['hub_id']) && $request['hub_id'] ? 'City Updated successfully' : 'City Created successfully',
           'city' => $city
       ]);
   }

   public function destroy(Request $request) {
       if (empty($request->id)) {
           return response()->json(['success' => false, 'message' => 'City ID is required'], 400);
       }
       Hub::find($request->id)->delete();
       return response()->json(['success' => true, 'message' => 'City deleted successfully']);
   }

    public function showMap() {
        $hub_list = Hub::orderBy('created_at', 'desc')->get();
        return view('admin.map.view', compact('hub_list'));
    }

    public function getCityCoordinates(Request $request)
    {
         $request->validate([
            'city_id' => 'required',
        ]);
        $areas = City::where('hub_id', $request['city_id'])->get(); // Get all polygons for the city
        $coordinates = $areas->map(function($area) {
            return json_decode($area->coordinates, true); // Decode each area’s coordinates
        });
        return response()->json(['data' => $coordinates]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'polygons' => 'required|array',
            'hub_id' => 'required',
            'polygons.*' => 'required|array',
            'polygons.*.*.lat' => 'required|numeric',
            'polygons.*.*.lng' => 'required|numeric',
        ]);

        City::where('hub_id', $validated['hub_id'])->delete();

        foreach ($validated['polygons'] as $polygon) {
            $area = new City();
            $area->coordinates = json_encode($polygon); // Save each polygon as JSON
            $area->hub_id = $validated['hub_id'];
            $area->save();
        }
        return response()->json(['message' => 'Area saved successfully']);
    }
}
