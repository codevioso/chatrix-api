<?php

namespace App\Http\Controllers;

use App\Enums\MediaType;
use App\Rules\MediaTypeRule;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    /**
     * Upload media file
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        // Validate the request
        $request->validate([
            'media_type' => ['required', new MediaTypeRule()],
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg,wav,mp4,mp3|max:20480',
        ]);

        try {
            // Get the file
            $file = $request->file('file');
            // Get the media type
            $mediaType = MediaType::from($request->media_type);
            // Get the file path
            $file_path = MediaService::get_file_name($mediaType) . '.' . $file->getClientOriginalExtension();
            // Store the file
            Storage::disk('public')->put($file_path, file_get_contents($file));
            // Return a success response
            return response()->json([
                'data' => [
                    'file_path' => $file_path,
                    'file_url' => asset('storage/' . $file_path),
                ],
            ], 201);

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
