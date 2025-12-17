<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = Address::where('created_by', Auth::id())->get();
        return response()->json([
            'status'  => 200,
            'data'    => $addresses->isEmpty() ? (object)[] : $addresses,
            'message' => $addresses->isEmpty()
                ? 'Address Not Found'
                : 'All addresses retrieved successfully',
        ]);
    }

    public function save(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string',
        'phone_number' => 'required|string',
        'address' => 'required|string',
        'pincode' => 'required|string',
        'state' => 'required|string',
        'city' => 'required|string',
        'address_type' => 'required|string',
        'is_default' => 'nullable|boolean',
        'latitude' => 'nullable|numeric',
        'longitude' => 'nullable|numeric',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 409,
            'message' => $validator->errors()->first(),
        ], 409);
    }

    $data = $validator->validated();

    $address = new Address();
    $address->name = $data['name'];
    $address->phone_number = $data['phone_number'];
    $address->address = $data['address'];
    $address->pincode = $data['pincode'];
    $address->state = $data['state'];
    $address->city = $data['city'];
    $address->address_type = $data['address_type'];
    $address->is_default = $data['is_default'] ?? false;
    $address->latitude = $data['latitude'] ?? null;
    $address->longitude = $data['longitude'] ?? null;
    $address->created_by = Auth::id();

    $address->save();

    return response()->json([
        'status' => 200,
        'message' => 'Address created successfully'
    ]);
}

public function update(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:addresses,id',
            'name' => 'required|string',
            'phone_number' => 'required|string',
            'address' => 'required|string',
            'pincode' => 'required|string',
            'state' => 'required|string',
            'city' => 'required|string',
            'address_type' => 'required|string',
            'is_default' => 'nullable|boolean',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 419);
        }

        $data = $validator->validated();

        $address = Address::find($data['id']);
        if (!$address) {
            throw new \Exception('Address not found', 404);
        }

        if (!empty($data['is_default']) && $data['is_default'] == 1) {
            Address::where('created_by', Auth::id())
                   ->where('id', '!=', $address->id)
                   ->update(['is_default' => false]);
            $address->is_default = true;
        } else {
            $address->is_default = false;
        }

        $address->name = $data['name'];
        $address->phone_number = $data['phone_number'];
        $address->address = $data['address'];
        $address->pincode = $data['pincode'];
        $address->state = $data['state'];
        $address->city = $data['city'];
        $address->address_type = $data['address_type'];
        $address->latitude = $data['latitude'] ?? null;
        $address->longitude = $data['longitude'] ?? null;
        $address->updated_by = Auth::id();

        $address->save();

        return response()->json([
            'status' => 200,
            'message' => 'Address updated successfully'
        ]);
    } catch (\Throwable $th) {

        return response()->json([
            'status' => $th->getCode() ?: 500,
            'message' => $th->getMessage(),
        ], $th->getCode() ?: 500);
    }
}

public function setDefaultAddress(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'address_id' => 'required|integer|exists:addresses,id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 409,
                    'message' => $validator->errors()->first(),
                ], 409);
            }
            $address = Address::find($request->address_id);

            if (!$address) {
                throw new \Exception('Address not found', 404);
            }
            Address::where('created_by', $address->created_by)->where('id', '!=', $address->id) ->get()
               ->each(function($address){
                   $address->is_default = false;
                   $address->save();
               });
            $address->is_default = true;
            $address->save();
            return response()->json([
                'status' => 200,
                'message' => 'Address set as default successfully'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => $th->getCode(),
                'message' => $th->getMessage(),
            ], $th->getCode() ?: 500);
        }
    }

    public function delete(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'address_id' => 'required|integer|exists:addresses,id',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 419);
        }

        $address = Address::find($request->address_id);

        if (!$address) {
            throw new \Exception('Address not found', 404);
        }

        $address->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Address deleted successfully'
        ]);
    } catch (\Throwable $th) {
        return response()->json([
            'status' => $th->getCode() ?: 500,
            'message' => $th->getMessage(),
        ], $th->getCode() ?: 500);
    }
}


}
