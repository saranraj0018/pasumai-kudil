<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryPartner;
use App\Models\Hub;
use Exception;
use Illuminate\Http\Request;

class DeliveryPartnerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('query');
        $this->data['delivery_partner'] = DeliveryPartner::with('get_hub')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->where('mobile_number', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $this->data['hub'] = Hub::get();
        $this->data['search'] = $search;
        return view('admin.delivery_partner.index')->with($this->data);
    }

    public function saveDeliveryPartner(Request $request)
    {
        $rules = [
            'name'   => 'required|string|max:255',
            'hub_id'    => 'required',
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
