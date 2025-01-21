<?php

namespace App\Http\Controllers;

use App\Enums\RoomStatus;
use App\Models\Room;
use App\Models\User;
use App\Rules\RoomTypeRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Get the request parameters and set the default values
            $keyword = request('keyword');
            $limit = request('limit', 50);

            // Get the authenticated user
            $user = User::session_user();

            // Get the rooms
            $data = Room::where('user_id', $user->id);
            $data = $data->where(function ($q) use ($keyword) {
                if (!empty($keyword)) {
                    $q->where('name', 'like', "%$keyword%");
                }
                $q->where('status', 'active');
            });
            $data = $data->where('status', RoomStatus::ACTIVE);
            $data = $data->orderBy('created_at', 'desc')->paginate($limit);

            // Return a success response
            return response()->json(['data' => $data], 200);

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
     * abort as the method is not not available
     *
     * @return void
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Create a new room
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string:min:5|unique:rooms,name',
            'description' => 'nullable|string',
            'type' => ['required', new RoomTypeRule()],
            'password' => 'nullable|string',
            'logo' => 'nullable|string',
            'cover' => 'nullable|string'
        ]);

        try {
            $user = User::session_user();

            // Begin a database transaction
            DB::beginTransaction();

            // Create a new room
            $room = new Room([
                'user_id' => $user->id,
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description ?? null,
                'type' => $request->type,
                'password' => $request->password ?? null,
                'logo' => $request->logo ?? null,
                'cover' => $request->cover ?? null
            ]);
            // Save the room
            if ($room->save()) {
                // Commit the database transaction
                DB::commit();
                // Return a success response
                return response()->json([
                    'message' => 'Room created successfully'
                ], 201);
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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {

            // Get the authenticated user
            $user = User::session_user();

            // Get the room
            $room = Room::where('user_id', $user->id)
                ->where('status', RoomStatus::ACTIVE)
                ->where('id', $id)->first();
            if (!$room) {
                return response()->json([
                    'message' => 'Room not found'
                ], 404);
            }

            // Return a success response
            return response()->json(['data' => $room], 200);

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
     * abort as the method is not not available
     *
     * @param string $id
     * @return void
     */
    public function edit(string $id)
    {
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string:min:5|unique:rooms,name,' . $id . ',id',
            'description' => 'nullable|string',
            'type' => ['required', new RoomTypeRule()],
            'password' => 'nullable|string',
            'logo' => 'nullable|string',
            'cover' => 'nullable|string'
        ]);

        try {
            $user = User::session_user();
            $room = Room::where('user_id', $user->id)
                ->where('status', RoomStatus::ACTIVE)
                ->where('id', $id)->first();
            if (!$room) {
                return response()->json([
                    'message' => 'Room not found'
                ], 404);
            }

            // Begin a database transaction
            DB::beginTransaction();

            // Update the room
            $room->name = $request->name;
            $room->slug = Str::slug($request->name);
            $room->description = $request->description ?? null;
            $room->type = $request->type;
            $room->password = $request->password ?? null;
            $room->logo = $request->logo ?? null;
            $room->cover = $request->cover ?? null;

            // Save the room
            if ($room->save()) {
                // Commit the database transaction
                DB::commit();
                // Return a success response
                return response()->json([
                    'message' => 'Room updated successfully'
                ], 200);
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {

            // Get the authenticated user
            $user = User::session_user();

            // Get the room
            $room = Room::where('user_id', $user->id)
                ->where('status', RoomStatus::ACTIVE)
                ->where('id', $id)->first();
            if (!$room) {
                return response()->json([
                    'message' => 'Room not found'
                ], 404);
            }

            // Update the room name, slug and status
            $room->name = 'deleted-' . $room->name . '-' . time();
            $room->slug = 'deleted-' . $room->slug . '-' . time();
            $room->status = RoomStatus::INACTIVE;
            if($room->save()){

                // Delete the room
                $room->delete();

                // Return a success response
                return response()->json([
                    'message' => 'Room has been deleted successfully'
                ], 200);
            }


        } catch (\Exception $e) {
            // Log the error message
            Log::error($e->getMessage());
            // Return an error response
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
