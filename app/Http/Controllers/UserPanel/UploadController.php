<?php

namespace App\Http\Controllers\UserPanel;

use App\Http\Controllers\Controller;
use App\Methods\{WebPConverter, ImageWatermark};
use App\Models\Product\ProductCategory;
use App\Models\UploadedFile;
use App\Traits\HandlesFileStorage;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Codebay\Chunk\Handler\HandlerFactory;
use Codebay\Chunk\Receiver\FileReceiver;

class UploadController extends Controller
{
    use HandlesFileStorage;

    public function upload(Request $request, $category_id)
    {
        if (demoMode()) {
            return $this->error('Some features are disabled in the demo version');
        }

        $originalFileName = $request->file('file')->getClientOriginalName();

        if (strip_tags($originalFileName) !== $originalFileName) {
            return $this->error(translate('The file name contain blocked patterns'));
        }

        if (preg_match('/\{\{[^}]*\}\}|{!![^}]*!!}|<\?php|\{\}|\{[^}]*\}/', $originalFileName)) {
            return $this->error(translate('The file name contain blocked patterns'));
        }

        $seller = authUser();

        $category = ProductCategory::where('id', hash_decode($category_id))->firstOrFail();

        $uploadedFileExists = UploadedFile::where('seller_id', $seller->id)
            ->where('category_id', $category->id)
            ->where('name', $originalFileName)->latest()->first();
        if ($uploadedFileExists) {
            return $this->error(translate('Duplicate files found'));
        }

        $mainFileTypes = explode(',', $category->main_file_types);
        $extensions = array_merge($mainFileTypes, ['jpeg', 'jpg', 'png']);
        if (!in_array($request->file('file')->getClientOriginalExtension(), $extensions)) {
            return $this->error(translate('You cannot upload files of this type'));
        }

        try {
            $storageDriver = storageDriver();
            if (!$storageDriver) {
                return $this->error(translate('Unavailable storage provider'));
            }

            $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));
            if ($receiver->isUploaded() === false) {
                return $this->error(translate('Failed to upload (:filename)', ['filename' => $originalFileName]));
            }

            $save = $receiver->receive();
            if (!$save->isFinished()) {
                return $this->error(translate('Failed to upload (:filename)', ['filename' => $originalFileName]));
            }

            $file = $save->getFile();
            $fileExtension = $file->getClientOriginalExtension();
            $fileMimeType = ($this->fileMimeType($fileExtension)) ? $this->fileMimeType($fileExtension) : $file->getMimeType();
            $fileSize = $file->getSize();

            if ($fileSize == 0) {
                return $this->error(translate('Empty files cannot be uploaded'));
            }

            $productSettings = settings('product');

            $maxFileSize = @$productSettings->max_file_size;
            if ($fileSize > $maxFileSize) {
                return $this->error(translate('File is too big, Max file size :max_file_size', ['max_file_size' => formatFileSize($maxFileSize)]));
            }

            // GET IMAGE DIMENSIONS BEFORE PROCESSING
            $imageWidth = null;
            $imageHeight = null;

            if (in_array($fileMimeType, ['image/png', 'image/jpg', 'image/jpeg'])) {
                // Get dimensions before any processing (watermark, webp conversion)
                $imageSize = getimagesize($file->getPathname());
                if ($imageSize) {
                    $imageWidth = $imageSize[0];
                    $imageHeight = $imageSize[1];
                }

                if (@settings('watermark')->status) {
                    $watermark = new ImageWatermark();
                    $file = $watermark->add($file);
                }

                if (@$productSettings->convert_images_webp) {
                    $image = new WebPConverter();
                    $file = $image->convert($file);
                }
            }

            $userHashId = strtolower(hash_encode($seller->id));

            $path = "files/products/{$userHashId}/";
            $handler = new $storageDriver->handler;
            $response = $handler->upload($file, $path, $fileMimeType);

            if ($response->type == "error") {
                return $this->error($response->message);
            }

            if ($response->type != "success") {
                return $this->error(translate('Failed to upload (:filename)', ['filename' => $originalFileName]));
            }

            // CREATE UPLOADED FILE WITH DIMENSIONS
            $uploadedFile = UploadedFile::create([
                'seller_id' => $seller->id,
                'category_id' => $category->id,
                'name' => $originalFileName,
                'mime_type' => $fileMimeType,
                'extension' => $fileExtension,
                'size' => $fileSize,
                'path' => $response->path,
                'width' => $imageWidth,
                'height' => $imageHeight,
                'expiry_at' => Carbon::now()->addHours(@$productSettings->file_duration),
            ]);

            if (!$uploadedFile) {
                return $this->error(translate('Failed to upload (:filename)', ['filename' => $originalFileName]));
            }

            return $this->success([
                'id' => hash_encode($uploadedFile->id),
                'name' => $uploadedFile->name,
                'size' => $uploadedFile->getSize(),
                'width' => $uploadedFile->width,
                'height' => $uploadedFile->height,
                'mime_type' => $uploadedFile->mime_type,
                'extension' => $uploadedFile->extension,
                'link' => $uploadedFile->getFileLink(),
                'time' => $uploadedFile->created_at->diffforhumans(),
                'delete_link' => route('user.product.files.delete', [hash_encode($category->id), hash_encode($uploadedFile->id)]),
            ]);

        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}


















