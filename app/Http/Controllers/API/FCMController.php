<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FCMController extends Controller
{
    public function saveFCMToken(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fcm_token' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 409,
                    'message' => $validator->errors()->first(),
                ], 409);
            }

            $data = $validator->validated();
            $userId = auth()->id();
            $update = User::where('id', $userId)->update([
                'fcm_token' => $data['fcm_token']
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'FCM Token updated successfully'
            ]);
        } catch (\Throwable $th) {

            return response()->json([
                'status' => $th->getCode() ?: 500,
                'message' => $th->getMessage(),
            ], $th->getCode() ?: 500);
        }
    }

    public function notificationList()
    {
        $userId = auth()->id();

        if (!$userId) {
            return response()->json([
                'status' => 409,
                'message' => 'User Not Found',
            ], 200);
        }

        $get_notification = Notification::where(['user_id' => $userId, 'role' => 2])->get();

        $notificationlists = $get_notification->map(function ($get_notification) {
            return [
                'id' => $get_notification->id,
                'title' => $get_notification->title,
                'description' => $get_notification->description,
                'status' => $get_notification->status,
                'created_at' => $get_notification->created_at,
                'updated_at' => $get_notification->updated_at,
            ];
        });

        return response()->json([
            'status' => 200,
            'message' => 'Notification detail fetched successfully',
            'notification_details' => $notificationlists
        ]);
    }
}
