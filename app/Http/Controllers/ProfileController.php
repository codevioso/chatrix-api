<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Get the authenticated user
            $user = User::session_user();
            // Return the user profile
            return response()->json([
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            // Log the error message
            Log::error($e->getMessage());
            // Return an error response
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the authenticated user profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string',
            'avatar' => 'nullable|string',
        ]);

        try {
            // Start database transaction
            DB::beginTransaction();

            // Get the authenticated user
            $user = User::session_user();

            // Update the user profile
            $user->name = $request->name ?? $user->name;
            if(!empty($request->avatar)){
                $user->avatar = $request->avatar;
            }
            $user->save();

            // Commit the database transaction
            DB::commit();
            // Return a success response
            return response()->json([
                'message' => 'Profile updated successfully',
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

    public function change_password(Request $request): JsonResponse
    {
        // Validate the request
        $request->validate([
            'new_password' => 'required|min:6|string|not_in:'.$request->password,
            'password' => 'required|min:6|string|confirmed'
        ], [
            'new_password.not_in' => 'New password must be different from the current password'
        ]);

        try {
            // Start database transaction
            DB::beginTransaction();

            // Get the authenticated user
            $user = User::session_user();
            // Check if the old password is correct
            if (!Hash::check($request->password, $user->password)) {
                // Return an error response
                return response()->json([
                    'message' => 'Validation Error',
                    'errors' => ['password' => ['Password is not correct.']],
                ], 422);
            }
            // Update the user password
            $user->update([
                'password' => bcrypt($request->new_password),
            ]);

            // Commit the database transaction
            DB::commit();
            // Return a success response
            return response()->json([
                'message' => 'Password updated successfully',
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
