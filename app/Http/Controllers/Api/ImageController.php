<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Image\StoreImageRequest;
use App\Http\Resources\ImageResource;
use App\Models\Image;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    /**
     * Upload Image
     */
    public function store(StoreImageRequest $request)
    {
        $validated = $request->validated();

        $file = $request->file('image');
        $path = $file->store('uploads/images', 'public');

        $image = Image::create([
            'imageable_id' => $validated['imageable_id'],
            'imageable_type' => $validated['imageable_type'],
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'disk' => 'public',
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'image_type' => $validated['image_type'] ?? null,
            'is_main' => $validated['is_main'] ?? false,
            'notes' => $validated['notes'] ?? null,
        ]);

        return $this->success(
            new ImageResource($image),
            'تم رفع الصورة بنجاح',
            201
        );
    }

    /**
     * Delete Image
     */
    public function destroy(Image $image)
    {
        Storage::disk($image->disk)->delete($image->file_path);

        $image->delete();

        return $this->success(
            null,
            'تم حذف الصورة بنجاح'
        );
    }

    /**
     * Show all images for a User
     */
    public function userImages(User $user)
    {
        $images = $user->images()
            ->latest() // آخر إضافة أولاً
            ->get()
            ->map(function ($img) {
                return [
                    'id' => $img->id,
                    'file_name' => $img->file_name,
                    'url' => asset('storage/' . $img->file_path),
                    'is_main' => $img->is_main,
                    'image_type' => $img->image_type,
                ];
            });

        return $this->success(
            $images,
        );
    }

    /**
     * Show all images for a Student
     */
    public function studentImages(Student $student)
    {
        $images = $student->images()
            ->latest() // آخر إضافة أولاً
            ->get()
            ->map(function ($img) {
                return [
                    'id' => $img->id,
                    'file_name' => $img->file_name,
                    'url' => asset('storage/' . $img->file_path),
                    'is_main' => $img->is_main,
                    'image_type' => $img->image_type,
                ];
            });

        return $this->success(
            $images,
        );
    }
}
