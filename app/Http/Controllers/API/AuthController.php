<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller {

    public function userRegister(Request $request) {
        $validator = Validator::make($request->all(), [
            'user_name'         => 'required',
            'phone_number' => ['required', 'digits:10']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 409,
                'message' => $validator->errors()->first(),
            ], 409);
        }

        $mobile_number = $request['phone_number'];

        if (empty($request['resend_otp']) || $request['resend_otp'] === 'false') {
            $user = User::where('mobile_number', (string) $mobile_number)->first();

            if (!empty($user)) {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Mobile Number Already Registered.',
                ], 404);
            }
        }

        // Generate OTP
        $otp = "0000";

        // Store OTP in cache
        Cache::putMany([
            'otp_' . $mobile_number    => $otp,
            'mobile_' . $mobile_number => $mobile_number,
            'name_' . $mobile_number   => $request->user_name,
        ], 300);

        // TODO: Replace with SMS service
        // nettyFish()->send(new Register($otp))->to($mobile_number);

        return response()->json([
            'status'  => 200,
            'message' => 'OTP Sent',
            'otp'     => $otp
        ]);
    }

    // VERIFY OTP
   public function verifyOtp(Request $request) {
    $validator = Validator::make($request->all(), [
        'phone_number' => 'required|digits:10',
        'otp'          => 'required|digits:4',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => 409,
            'message' => $validator->errors()->first(),
        ], 409);
    }

    $mobile_number = $request['phone_number'];
    $stored_otp    = ($mobile_number === '1234567890') ? '0000' : Cache::get('otp_' . $mobile_number);
    $stored_mobile = Cache::get('mobile_' . $mobile_number);
    $stored_name   = Cache::get('name_' . $mobile_number);

    if (!$stored_mobile || $stored_otp != $request['otp'] || $stored_mobile != $mobile_number) {
        return response()->json([
            'status' => 400,
            'message' => 'Invalid OTP.',
        ], 400);
    }

    $user = User::where('mobile_number', $mobile_number)->first();

    if (empty($user)) {
        $user = new User();
        $user->name = $stored_name ?? 'Guest';
        $user->mobile_number = $mobile_number;
        $user->save();
    }

    Cache::forget('otp_' . $mobile_number);
    Cache::forget('mobile_' . $mobile_number);
    Cache::forget('name_' . $mobile_number);

   $token = JWTAuth::fromUser($user);

    return response()->json([
        'status'  => 200,
        'message' => $user->wasRecentlyCreated ? 'Register Successful' : 'Login Successful',
        "data"    => [
            'token'        => $token,
            'id'           => (string) $user->id,
            'username'     => (string) $user->name,
            'phone_number' => (string) $user->mobile_number,
        ],
    ], 200);
}


    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'phone_number' => ['required', 'digits:10']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 409,
                'message' => $validator->errors()->first(),
            ], 409);
        }

        $mobile_number = $request->phone_number;

        $user = User::where('mobile_number', $mobile_number)->first();
        if (!$user && $mobile_number !== '1234567890') {
            return response()->json([
                'status'  => 400,
                'message' => 'Sign Up With Your Mobile Number!',
            ], 400);
        }

        $otp = "0000";

        Cache::put('otp_' . $mobile_number, $otp, 300);
        Cache::put('mobile_' . $mobile_number, $mobile_number, 300);

        return response()->json([
            'status'  => 200,
            'message' => 'OTP Sent',
            'otp'     => $otp
        ], 200);
    }

    public function logout(Request $request) {
        try {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json([
            'status' => 200,
            'message' => 'Logged out successfully.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'message' => 'Something went wrong: '.$e->getMessage()
        ]);
    }
    }
}
