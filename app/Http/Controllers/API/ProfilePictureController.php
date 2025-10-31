<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProfilePicture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProfilePictureController extends Controller
{
    /**
     * Upload a new profile picture
     */
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|image|mimes:jpg,jpeg,png|max:5120', // max 5MB
            ]);

            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $plan = $user->plan;
            if (!$plan || !isset($plan->profile_picture_limit)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your subscription plan is missing a profile picture limit.'
                ], 403);
            }

            $limit = (int) $plan->profile_picture_limit;
            $currentCount = $user->profilePictures()->count();
            if ($currentCount >= $limit) {
                return response()->json([
                    'success' => false,
                    'message' => "You have reached your plan's picture limit ({$limit}). Please delete one before uploading."
                ], 403);
            }

            $file = $request->file('image');
            $folder = "profile_pictures/{$user->id}";
            $path = $file->store($folder, 'public');

            $picture = DB::transaction(function () use ($user, $path) {
                $isPrimary = $user->profilePictures()->count() === 0;
                return $user->profilePictures()->create([
                    'image_path' => $path,
                    'is_primary' => $isPrimary,
                ]);
            });

            Log::info('Profile picture uploaded', [
                'user_id' => $user->id,
                'picture_id' => $picture->id,
                'path' => $path,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile picture uploaded.',
                'data' => [
                    'id' => $picture->id,
                    'url' => Storage::disk('public')->url($path),
                    'is_primary' => $picture->is_primary,
                ],
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Upload profile picture failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if (isset($path) && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            return response()->json([
                'success' => false,
                'message' => 'Upload failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List all profile pictures of the authenticated user
     */
    public function list(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $pics = $user->profilePictures()
                ->orderByDesc('is_primary')
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($pic) {
                    $pic->url = Storage::disk('public')->url($pic->image_path);
                    return $pic->only(['id', 'url', 'is_primary']);
                });

            return response()->json([
                'success' => true,
                'data' => $pics,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to list profile pictures', [
                'user_id' => Auth::id(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch profile pictures.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a profile picture
     */
    public function delete(Request $request, $id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $picture = $user->profilePictures()->where('id', $id)->first();
            if (!$picture) {
                return response()->json(['success' => false, 'message' => 'Picture not found.'], 404);
            }

            $path = $picture->image_path;
            $wasPrimary = $picture->is_primary;

            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            $picture->delete();

            // If deleted picture was primary, set another one as primary
            if ($wasPrimary) {
                $next = $user->profilePictures()->latest()->first();
                if ($next) {
                    $next->update(['is_primary' => true]);
                }
            }

            return response()->json(['success' => true, 'message' => 'Picture deleted.']);
        } catch (\Throwable $e) {
            Log::error('Delete profile picture failed', [
                'user_id' => Auth::id(),
                'picture_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete picture.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set a profile picture as primary
     */
    public function setPrimary(Request $request, $id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $picture = $user->profilePictures()->where('id', $id)->first();
            if (!$picture) {
                return response()->json(['success' => false, 'message' => 'Picture not found.'], 404);
            }

            DB::transaction(function () use ($user, $picture) {
                $user->profilePictures()->where('is_primary', true)->update(['is_primary' => false]);
                $picture->update(['is_primary' => true]);
            });

            return response()->json(['success' => true, 'message' => 'Primary picture updated.']);
        } catch (\Throwable $e) {
            Log::error('Set primary profile picture failed', [
                'user_id' => Auth::id(),
                'picture_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to set primary picture.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
