<?php

namespace App\Http\Controllers;

use App\Mail\UserActivationMail;
use App\Mail\UserForgotPasswordMail;
use App\Models\User;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    /**
     * Login the user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        // Validate the request
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|min:6|string'
        ]);

        try {
            // Start database transaction
            DB::beginTransaction();

            // Find the user by username or email
            $user = User::where(function ($query) use ($request) {
                $query->where('username', $request->username)
                    ->orWhere('email', $request->username);
            })->first();
            // Check if the user is not found
            if ($user === null) {
                // Return an error response
                return response()->json([
                    'message' => 'Invalid username or email address'
                ], 401);
            }

            // Check if the user account is not activated
            if ($user->activation_code !== null || $user->email_verified_at === null) {
                // Send an activation code to the user's email address
                Mail::to($user->email)->send(new UserActivationMail($user));
                // Return an error response
                return response()->json([
                    'data' => $user,
                    'message' => 'Your account is not activated. Please check your email for the activation code.'
                ], 401);
            }

            // Check if the password is invalid
            if (Hash::check($request->password, $user->password)) {
                // Generate an access token
                $accessToken = User::access_token($user);
                // Commit the database transaction
                DB::commit();
                // Return a success response
                return response()->json([
                    'data' => $user,
                    'access_token' => $accessToken
                ], 200);
            } else {
                // Return an error response
                return response()->json([
                    'message' => 'Validation Error',
                    'errors' => ['password' => ['Invalid password']],
                ], 422);
            }

        } catch (\Exception $e) {
            // Rollback the database transaction
            DB::rollBack();
            // Log the error message
            Log::error($e->getMessage());
            // Return an error response
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Signup the user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function signup(Request $request): JsonResponse
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string',
            'username' => 'required|string|unique:users,username',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|min:6|string|confirmed'
        ]);

        try {
            // Start database transaction
            DB::beginTransaction();

            // Create a new user
            $user = new User([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'avatar' => MediaService::generate_avatar($request->name),
                'password' => bcrypt($request->password),
                'activation_code' => mt_rand(100000, 999999)
            ]);

            // Save the user
            if ($user->save()) {
                // Send an activation code to the user's email address
                Mail::to($user->email)->send(new UserActivationMail($user));

                // Commit the database transaction
                DB::commit();

                // Return a success response
                return response()->json([
                    'message' => 'Your signup process has been completed successfully. A six-digit activation code has been sent to your email address.'
                ], 201);

            } else {
                // Return an error response
                return response()->json([
                    'message' => 'Server Error'
                ], 500);
            }

        } catch (\Exception $e) {
            // Rollback the database transaction
            DB::rollBack();
            // Log the error message
            Log::error($e->getMessage());
            // Return an error response
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activate the user account
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function activateAccount(Request $request): JsonResponse
    {
        // Validate the request
        $request->validate([
            'username' => 'required|string',
            'activation_code' => 'required|integer|exists:users,activation_code'
        ]);

        try {
            // Start database transaction
            DB::beginTransaction();

            // Find the user by activation code
            $user = User::where('activation_code', $request->activation_code)
                ->where(function ($query) use ($request) {
                    $query->where('username', $request->username)
                        ->orWhere('email', $request->username);
                })->first();
            // Check if the user is not found
            if ($user === null) {
                // Return an error response
                return response()->json([
                    'message' => 'Validation Error',
                    'errors' => ['activation_code' => ['Invalid Activation Code']],
                ], 422);
            }

            // Check if the user already activated the account
            if ($user->activation_code === null && $user->email_verified_at !== null) {
                // Return a success response
                return response()->json([
                    'message' => 'Your account has already been activated'
                ], 200);
            }

            // Activate the user account
            $user->activation_code = null;
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->save();
            DB::commit();

            // Return a success response
            return response()->json([
                'message' => 'Your account has been activated successfully'
            ], 200);

        } catch (\Exception $e) {
            // Rollback the database transaction
            DB::rollBack();
            // Log the error message
            Log::error($e->getMessage());
            // Return an error response
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Forgot password
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        // Validate the request
        $request->validate([
            'email' => 'required|string|email|exists:users,email',
        ]);

        try {
            // Start database transaction
            DB::beginTransaction();

            // Find the user by email
            $user = User::whereNull('activation_code')->whereNotNull('email_verified_at')
                ->where('email', $request->email)
                ->first();
            // Check if the user is not found
            if ($user === null) {
                // Return an error response
                return response()->json([
                    'message' => 'Validation Error',
                    'errors' => ['email' => ['Invalid email address']],
                ], 422);
            }

            // Generate a six-digit reset code
            $user->reset_code = rand(100000, 999999);
            $user->save();

            // Send a reset code to the user's email address
            Mail::to($user->email)->send(new UserForgotPasswordMail($user));

            // Commit the database transaction
            DB::commit();

            // Return a success response
            return response()->json([
                'message' => 'A six-digit reset code has been sent to your email address'
            ], 200);

        } catch (\Exception $e) {
            // Rollback the database transaction
            DB::rollBack();
            // Log the error message
            Log::error($e->getMessage());
            // Return an error response
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset password
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        // Validate the request
        $request->validate([
            'email' => 'required|string|email|exists:users,email',
            'reset_code' => 'required|integer|exists:users,reset_code',
            'password' => 'required|string|confirmed'
        ]);

        try {
            // Start database transaction
            DB::beginTransaction();

            // Find the user by email
            $user = User::whereNull('activation_code')->whereNotNull('email_verified_at')
                ->where('email', $request->email)
                ->where('reset_code', $request->reset_code)
                ->first();
            // Check if the user is not found
            if ($user === null) {
                // Return an error response
                return response()->json([
                    'message' => 'Validation Error',
                    'errors' => ['reset_code' => ['Invalid Reset Code']],
                ], 422);
            }

            // Reset the user password
            $user->reset_code = null;
            $user->password = bcrypt($request->password);
            $user->save();

            // Commit the database transaction
            DB::commit();

            // Return a success response
            return response()->json([
                'message' => 'Password has been reset successfully.'
            ], 200);

        } catch (\Exception $e) {
            // Rollback the database transaction
            DB::rollBack();
            // Log the error message
            Log::error($e->getMessage());
            // Return an error response
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
