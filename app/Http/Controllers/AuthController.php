<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class AuthController extends Controller
{
    public function sendResetOTP(Request $request)
    {
        $request->validate(['username_or_email' => 'required|string']);

        $user = User::where('user_name', $request->username_or_email)
            ->orWhere('email', $request->username_or_email)
            ->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $otp = rand(100000, 999999);

        Cache::put('reset_otp_' . $user->email, [
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes(10)
        ], now()->addMinutes(10));

        Mail::raw("Your password reset OTP is: $otp", function ($message) use ($user) {
            $message->to($user->email)->subject('Password Reset OTP');
        });

        return response()->json(['success' => true, 'message' => 'OTP sent to your email.']);
    }

    public function verifyResetOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        $otpData = Cache::get('reset_otp_' . $request->email);

        if (!$otpData || $otpData['otp'] != $request->otp) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired OTP.'], 400);
        }

        Cache::put('reset_verified_' . $request->email, true, now()->addMinutes(10));

        return response()->json(['success' => true, 'message' => 'OTP verified. You may now reset your password.']);
    }

    public function passwordReset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Cache::get('reset_verified_' . $request->email)) {
            return response()->json(['success' => false, 'message' => 'OTP not verified or expired.'], 403);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        Cache::forget('reset_otp_' . $request->email);
        Cache::forget('reset_verified_' . $request->email);

        return response()->json(['success' => true, 'message' => 'Password reset successful.']);
    }

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_name' => 'required|string|max:255|unique:users,user_name',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6|same:confirm_password',
                'confirm_password' => 'required|string|min:6',
                'agreed_to_terms' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 400);
            }

            $validated = $validator->validated();

            $user = User::create([
                'user_name' => $validated['user_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'agreed_to_terms' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'id' => $user->id,
                    'user_name' => $user->user_name,
                    'email' => $user->email,
                ]
            ], 201);
        } catch (Exception $e) {
            Log::error('Error registering user: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to register user.',
                'error' => app()->environment('production') ? 'Internal server error' : $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'emailOrPassword' => 'required|string',  // can be user_name or email
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 400);
            }

            $login = $request->emailOrPassword;

            // Find user by user_name OR email
            $user = User::where('user_name', $login)
                ->orWhere('email', $login)
                ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials.',
                ], 401);
            }

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'success' => true,
                'message' => 'Login successful.',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'user_name' => $user->user_name,
                    'email' => $user->email,
                    'role' => $user->role,  // Assuming 'role' is a field in the User model
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Login error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Login failed.',
                'error' => app()->environment('production') ? 'Internal server error' : $e->getMessage(),
            ], 500);
        }
    }

    // ... keep other methods like logout(), me(), updatePassword(), etc. the same unless needed

    public function logout(Request $request)
    {
        try {
            // Invalidate the token so it can't be used anymore
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'Logout successful.'
            ]);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token is invalid.'
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to logout, please try again.'
            ], 500);
        } catch (Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Logout failed.',
                'error' => app()->environment('production') ? 'Internal server error' : $e->getMessage(),
            ], 500);
        }
    }

    public function sendPasswordResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Send reset link to the user
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => __($status),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => __($status),
            ], 400);
        }
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => __($status),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => __($status),
            ], 400);
        }
    }
}
