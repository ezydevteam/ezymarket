<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\RichTextImage;
use App\Traits\HandlesValidation;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class RichTextImageController extends Controller
{
    use HandlesValidation;

    /**
     * Display a listing of rich text images.
     *
     * @return View
     */
    public function index(): View
    {
        $richTextImages = RichTextImage::query()->get();

        return view('admin.system.rich-text-images', compact('richTextImages'));
    }

    /**
     * Remove the specified rich text image from storage and database.
     *
     * @param RichTextImage $richTextImage
     * @return JsonResponse
     */
    public function destroy(RichTextImage $richTextImage): JsonResponse
    {
        try {
            $richTextImage->deleteImage();

            return $this->successJson('Image deleted successfully.');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }
}

















