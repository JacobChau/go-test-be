<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:2048', // Validates the upload
        ]);

        $image = $request->file('image');
        $imageName = time().'.'.$image->getClientOriginalExtension();

        // Store the image in the S3 bucket
        Storage::disk('s3')->put($imageName, file_get_contents($image), 'public');

        // Return the image URL
        return new JsonResponse([
            'name' => $imageName,
            'url' => Storage::disk('s3')->url($imageName),
        ]);

    }
}
