<?php

namespace App\Http\Controllers\API\Milk;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserAddressController extends Controller
{
    public function userAddressSave(Request $request)
    {
        $userId = auth()->id();
        $validator = Validator::make($request->all(), [
            'address' => 'required|string',
            'pincode' => 'required|string',
            'state' => 'required|string',
            'city' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 409,
                'message' => $validator->errors()->first(),
            ], 409);
        }

        $data = $validator->validated();
        $update = User::where('id', $userId)->update([
            'address' => $request['address'],
            'pincode' => $request['pincode'],
            'state' => $request['state'],
            'city' => $request['city'],
            'latitude' => $request['latitude'],
            'longitude' => $request['longitude'],
        ]);

        $userdetails = User::where('id', $userId)->first();

        return response()->json([
            'status' => 200,
            'message' => 'Address created successfully',
            'user_details' => $userdetails
        ]);
    }

    public function userAddressDetails(Request $request){
        $userId = auth()->id();
        $userDetails = User::select('name','address', 'pincode', 'city', 'state', 'latitude', 'longitude')
            ->where('id', $userId)
            ->first();

        return response()->json([
            'status' => 200,
            'message' => 'Address details fetch successfully',
            'user_details' => $userDetails
        ]);

    }
}
