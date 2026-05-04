<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Financial\{Currency, Transaction, Payout};
use App\Models\Support\Ticket;
use App\Models\{Refund, Settings};
use App\Traits\{HandlesValidation, HandlesFileStorage};
use App\Models\RichTextImage;
use Exception;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{Artisan, Cookie};
use Illuminate\Database\Eloquent\Relations\Relation;

class UtilityController extends Controller
{
    use HandlesValidation, HandlesFileStorage;

    /**
     * Allowed image MIME types.
     */
    private const ALLOWED_MIME_TYPES = [
        'image/png',
        'image/jpg',
        'image/jpeg',
        'image/gif',
    ];
    /**
     * Dismiss the restoration notice by clearing the restored_at timestamp.
     *
     * @param Request $request
     * @param string $modelType
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function dismissRestorationNotice(Request $request, string $modelType, int $id)
    {
        $modelClasses = [
            'transaction' => Transaction::class,
            'ticket' => Ticket::class,
            'refund' => Refund::class,
            'payout' => Payout::class,
        ];

        // Resolve the model class
        $modelClass = $modelClasses[strtolower($modelType)] ?? (Relation::getMorphedModel($modelType) ?? $modelType);

        if (!class_exists($modelClass)) {
            return $this->errorJson('Invalid model type.');
        }

        $model = $modelClass::findOrFail($id);

        // Ownership verification
        $isOwner = false;
        $userId = authUser()->id;

        if (isset($model->user_id) && $model->user_id == $userId) {
            $isOwner = true;
        } elseif (isset($model->seller_id) && $model->seller_id == $userId) {
            $isOwner = true;
        }

        if (!$isOwner) {
            return $this->errorJson('Unauthorized action.');
        }

        // Clear the restored_at field
        $model->restored_at = null;
        $model->save();

        return $this->successJson('Notice dismissed successfully.');
    }
    /**
     * Switch the current currency.
     */
    public function currency($code)
    {
        $currency = Currency::where('code', $code)->firstOrFail();
        config(['app.currency' => $currency->code]);

        return redirect()->back()
            ->cookie('currency', $currency->code, 60 * 24 * 30);
    }

    /**
     * Accept the GDPR cookie consent.
     */
    public function cookie()
    {
        Cookie::queue('gdpr_cookie', true, 1440 * 30);
    }

    /**
     * Run the cron job.
     */
    public function cronjob(Request $request): JsonResponse
    {
        ini_set('max_execution_time', 0);

        $cronJobSettings = settings('cronjob');

        if (@$cronJobSettings->key) {

            $validator = $this->validateRequestJson($request, [
                'key' => ['required', 'string'],
            ]);

            if ($validator instanceof JsonResponse) {
                return $validator;
            }

            if (@$cronJobSettings->key != $request->key) {
                return $this->errorJson('Invalid Cron Job Key');
            }
        }

        Artisan::call('schedule:run');

        Settings::updateSettings('cronjob', ['last_execution' => \Carbon\Carbon::now()]);

        return $this->successJson('Cron Job executed successfully');
    }

    /**
     * Upload image for rich text editor.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $validator = $this->validateRequestJson($request, [
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $image = $request->file('image');

        if (!in_array($image->getClientMimeType(), self::ALLOWED_MIME_TYPES)) {
            return $this->errorJson(
                'Invalid file type. Only image files are allowed (JPEG, JPG, PNG, GIF).',
                [],
                400
            );
        }

        try {
            $storageDriver = storageDriver();

            if (!$storageDriver) {
                return $this->errorJson('Unavailable storage provider', [], 500);
            }

            $imageExtension = $image->getClientOriginalExtension();
            $imageMimeType = $this->fileMimeType($imageExtension) ?? $image->getMimeType();

            $handler = new $storageDriver->handler;
            $response = $handler->upload($image, 'images/editor/', $imageMimeType);

            $richTextImage = RichTextImage::create([
                'name' => $image->getClientOriginalName(),
                'filename' => $response->filename,
                'path' => $response->path,
            ]);

            return response()->json([
                'uploaded' => true,
                'default' => $richTextImage->view_link,
            ]);
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }
}
