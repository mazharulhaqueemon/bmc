<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProfilePicture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProfilePictureController extends Controller
{
    // Upload a new picture (enforces plan limit)
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png|max:5120', // 5MB
        ]);

        $user = Auth::user();
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
        $path = null;

        try {
            $path = $file->store($folder, 's3'); // stored in S3 bucket

            $picture = DB::transaction(function () use ($user, $path) {
                $isPrimary = $user->profilePictures()->count() === 0;
                return $user->profilePictures()->create([
                    'image_path' => $path,
                    'is_primary' => $isPrimary,
                ]);
            });

        } catch (\Throwable $e) {
            // rollback file if DB fails
            if ($path && Storage::disk('s3')->exists($path)) {
                try { Storage::disk('s3')->delete($path); } catch (\Throwable $_) {}
            }
            return response()->json([
                'success' => false,
                'message' => 'Upload failed.',
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile picture uploaded.',
            'data' => $picture, 
        ], 201);
    }

    // List user's pictures
    public function list(Request $request)
    {
        $user = Auth::user();
        $pics = $user->profilePictures()
            ->orderByDesc('is_primary')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['success' => true, 'data' => $pics]);
    }

    // Delete a picture (and its S3 object)
    public function delete(Request $request, $id)
    {
        $user = Auth::user();
        $picture = $user->profilePictures()->where('id', $id)->first();

        if (!$picture) {
            return response()->json(['success' => false, 'message' => 'Picture not found.'], 404);
        }

        $path = $picture->image_path;
        $wasPrimary = $picture->is_primary;

        try {
            if ($path && Storage::disk('s3')->exists($path)) {
                Storage::disk('s3')->delete($path);
            }
        } catch (\Throwable $e) {}

        $picture->delete();

        if ($wasPrimary) {
            $next = $user->profilePictures()->latest()->first();
            if ($next) {
                $next->update(['is_primary' => true]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Picture deleted.']);
    }

    // Set a picture as primary
    public function setPrimary(Request $request, $id)
    {
        $user = Auth::user();
        $picture = $user->profilePictures()->where('id', $id)->first();

        if (!$picture) {
            return response()->json(['success' => false, 'message' => 'Picture not found.'], 404);
        }

        DB::transaction(function () use ($user, $picture) {
            $user->profilePictures()->where('is_primary', true)->update(['is_primary' => false]);
            $picture->update(['is_primary' => true]);
        });

        return response()->json(['success' => true, 'message' => 'Primary picture updated.']);
    }
}
