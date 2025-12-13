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

        $get_notification = Notification::where(['user_id' => $userId, 'role' => 2 , 'type' => 1])->get();
        $milk_notification =  Notification::where(['user_id' => $userId, 'role' => 2, 'type' => 2])->get();

        $grocery_details = $get_notification->map(function ($get_notification) {
            return [
                'id' => $get_notification->id,
                'title' => $get_notification->title,
                'description' => $get_notification->description,
                'status' => $get_notification->status,
                'created_at' => $get_notification->created_at,
                'updated_at' => $get_notification->updated_at,
            ];
        });

        $milk_details = $milk_notification->map(function ($milk_notification) {
            return [
                'id' => $milk_notification->id,
                'title' => $milk_notification->title,
                'description' => $milk_notification->description,
                'status' => $milk_notification->status,
                'created_at' => $milk_notification->created_at,
                'updated_at' => $milk_notification->updated_at,
            ];
        });

        return response()->json([
            'status' => 200,
            'message' => 'Notification detail fetched successfully',
            'grocery_notification' => $grocery_details,
            'milk_notification' =>  $milk_details
        ]);
    }

    public function deleteNotification(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'id' => 'required|array|min:1', // ensure at least one ID is provided
            'id.*' => 'integer|distinct',   // each element should be an integer and unique
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 409,
                'message' => $validator->errors()->first(),
            ], 409);
        }

        $data = $validator->validated();
        $userId = auth()->id();

        // Delete notifications
        $deletedCount = Notification::whereIn('id', $data['id'])
            ->where(['role' => 2, 'user_id' => $userId])
            ->delete();

        if ($deletedCount === 0) {
            return response()->json([
                'status' => 404,
                'message' => 'No notifications found to delete',
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Notification(s) deleted successfully',
            'deleted_count' => $deletedCount
        ]);
    }

    public function readNotification(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 409,
                    'message' => $validator->errors()->first(),
                ], 409);
            }

            $data = $validator->validated();
            $userId = auth()->id();

            $update = Notification::where(['id' => $data['id'] , 'user_id' => $userId])->update([
                'status' => 1
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Notification read successfully'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => $th->getCode() ?: 500,
                'message' => $th->getMessage(),
            ], $th->getCode() ?: 500);
        }
    }

    public function readNotificationAll(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 409,
                    'message' => $validator->errors()->first(),
                ], 409);
            }

            $data = $validator->validated();
            $userId = auth()->id();

            $update = Notification::where(['user_id' => $userId])->update([
                'status' => 1
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Notification readed successfully!'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => $th->getCode() ?: 500,
                'message' => $th->getMessage(),
            ], $th->getCode() ?: 500);
        }
    }
}
