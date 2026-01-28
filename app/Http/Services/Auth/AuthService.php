<?php

namespace App\Http\Services\Auth;

use App\Jobs\SendVerificationEmail;
use App\Jobs\SendWelcomeEmail;
use App\Mail\PasswordResetCodeMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function register($request)
    {
        $verificationCode = rand(100000, 999999);

        $request->merge([
            'password' => bcrypt($request->password),
            'email_verified_at' => Carbon::now(),
        ]);

        $user = User::create($request->only([
            'username', 'type', 'email', 'password', 'phone', 'email_verified_at'
        ]));

        // Store the code in the cache for 10 minutes
        Cache::put('email_verification_code_' . $user->email, $verificationCode, now()->addMinutes(10));

        // ✉️ Send the code via email using a Job
        SendVerificationEmail::dispatch($user->email, $verificationCode);

        $token = JWTAuth::fromUser($user);

        $responseData = $user->toArray();

        return Response::successResponse($responseData, 'User created successfully', 201);
    }

    public function login($request)
    {
        // dd($request->all());
        $user = User::where('email', $request->email)
                    ->first();

        if (!$user) {
            return Response::errorResponse('User is Not Found', [], 400);
        }

        if($user->email_verified_at === null) {
            return Response::errorResponse('Email is not verified', [], 400);
        }

        if (!$user->validatePassportPassword($request->password)) {
            return Response::errorResponse('Password is Incorrect', [], 400);
        }

        $token = JWTAuth::fromUser($user);

        $responseData          = $user->toArray();
        $responseData['token'] = $token;

        return Response::successResponse($responseData);
    }

    public function logout()
    {
        $user = Auth::guard('api')->user();
        if ($user) {
            JWTAuth::invalidate(JWTAuth::getToken());

            return Response::successResponse('Logged out successfully', [], 200);
        }

        return Response::errorResponse('User not authenticated', [], 401);
    }

    public function refreshToken($oldToken)
    {
        JWTAuth::setToken($oldToken);
        $newAccessToken = JWTAuth::refresh();

        return Response::successResponse([
            'new_token' => $newAccessToken
        ], 200);
    }

    public function sendResetCodeToEmail($request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $email = $request->email;
        $code = random_int(100000, 999999); // 6-digit code

        // You can store the code in cache or DB temporarily
        Cache::put('password_reset_code_' . $email, $code, now()->addMinutes(10));

        // Send the code by email
        Mail::to($email)->queue(new PasswordResetCodeMail($code));

        return response()->json(['message' => 'Reset code sent to email.']);
    }

    public function verifyResetCode($request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'code' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $email = $request->email;
        $code = (int) $request->code;
        $cachedCode = Cache::get('password_reset_code_' . $email);

        if ($cachedCode !== $code) {
            return response()->json(['message' => 'Invalid or expired code.'], 400);
        }

        return response()->json(['message' => 'Code is valid.']);
    }

    public function resetPassword($request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $email = $request->email;

        // update password
        $user = User::where('email', $email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Optionally, you can delete the cached code after successful reset
        Cache::forget('password_reset_code_' . $email);

        return response()->json(['message' => 'Password has been reset successfully.']);
    }

    public function verifyEmail($request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return Response::errorResponse('User not found.', [], 404);
        }

        if ($user->email_verified_at !== null) {
            return Response::errorResponse('Email is already verified.', [], 400);
        }

        // ✅ Get code from cache
        $cachedCode = Cache::get('email_verification_code_' . $user->email);

        if (!$cachedCode || $cachedCode !== (int) $request->verification_code) {
            return Response::errorResponse('Invalid or expired verification code.', [], 400);
        }

        $user->email_verified_at = now();
        $user->save();

        // ✅ Remove code from cache
        Cache::forget('email_verification_code_' . $user->email);

        // ✅ Send welcome email (queued)
        SendWelcomeEmail::dispatch($user->email);

        return Response::successResponse('Email verified successfully.');
    }


    public function resendVerificationEmail($request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return Response::errorResponse('User not found.', [], 404);
        }

        if ($user->email_verified_at !== null) {
            return Response::errorResponse('Email is already verified.', [], 400);
        }

        $verificationCode = rand(100000, 999999);
        // Store the code in the cache for 10 minutes
        Cache::put('email_verification_code_' . $user->email, $verificationCode, now()->addMinutes(10));

        // Dispatch the job to send the verification email
        SendVerificationEmail::dispatch($user->email, $verificationCode);

        return Response::successResponse('Verification email sent successfully.');
    }
}
