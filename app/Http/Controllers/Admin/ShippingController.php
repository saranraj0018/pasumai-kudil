<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shippings;
use Exception;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function index(Request $request)
    {
        $this->data['get_shipping'] = Shippings::first();
    
        return view('admin.shipping.view')->with($this->data);
    }

    public function saveShipping(Request $request)
    {
        $rules = [
            'city'   => 'required',
        ];
        $request->validate($rules);
        try {
 
        if (!empty($request['shipping_id'])) {
            $message = 'Shipping Updated successfully';
            $shipping = Shippings::find($request['shipping_id']);
        } else{
            $shipping = new Shippings();
            $message = 'Shipping saved successfully';
        }
           
            $shipping->city  = $request['city'];
            $shipping->latitude = $request['latitude'] ?? '';
            $shipping->longitude  = $request['longitude'] ?? '';
            $shipping->free_shipping = $request['free_shipping'] ?? 0;
            $shipping->extra_km = $request['extra_km'] ?? 0;
            $shipping->address  = $request['address'] ?? '';
            $shipping->save();

            return response()->json([
                'success' => true,
                'message' => $message,
                'shipping' => $shipping,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save shipping',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

   
}
