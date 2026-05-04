<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Verifications;

use App\Enums\IdVerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\IdVerification;
use App\Traits\HandlesValidation;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\{Request, Response, JsonResponse, RedirectResponse};
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * ID Verification Controller
 *
 * Manages user identity verification requests including review,
 * approval, rejection, and document handling.
 *
 * @package App\Http\Controllers\Admin\Verifications
 */
class IdVerificationController extends Controller
{
    use HandlesValidation;

    /**
     * Display a listing of ID verifications with filters.
     *
     * @return View
     */
    public function index(): View
    {
        $counters = $this->getVerificationCounters();

        $query = IdVerification::query();

        $this->applyFilters($query);

        $idVerifications = $query->get();

        return view('admin.id-verification.index', compact('counters', 'idVerifications'));
    }

    /**
     * Display the specified ID verification document.
     *
     * @param IdVerification $idVerification
     * @param string $document
     * @return Response
     */
    public function document(IdVerification $idVerification, string $document): Response
    {
        try {
            $documentPath = $idVerification->documents->$document ?? null;

            abort_if(!$documentPath, 404, 'Document not found');

            // Read file from storage (supports local and cloud)
            $file = readFromStorage($documentPath);

            // Get mime type from file extension or use default
            $extension = pathinfo($documentPath, PATHINFO_EXTENSION);
            $mimeType = match (strtolower($extension)) {
                'pdf' => 'application/pdf',
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                default => 'application/octet-stream'
            };

            return response($file, 200)
                ->header('Content-Type', $mimeType);
        } catch (Exception $e) {
            abort(404, 'Document not found');
        }
    }

    /**
     * Get the review modal content for an ID verification (AJAX).
     *
     * @param IdVerification $idVerification
     * @return JsonResponse
     */
    public function review(IdVerification $idVerification): JsonResponse
    {
        return response()->json([
            'title' => translate('Review Verification #:id', ['id' => $idVerification->id]),
            'content' => view('admin.id-verification.partials.review-content', compact('idVerification'))->render()
        ]);
    }

    /**
     * Download the specified ID verification document.
     *
     * @param IdVerification $idVerification
     * @param string $document
     * @return BinaryFileResponse|RedirectResponse
     */
    public function download(IdVerification $idVerification, string $document): BinaryFileResponse|RedirectResponse
    {
        try {
            $documentPath = $idVerification->documents->$document ?? null;

            abort_if(!$documentPath, 404, 'Document not found');

            $diskName = $idVerification->getStorageDisk();

            if (!Storage::disk($diskName)->exists($documentPath)) {
                return $this->errorBack('Document file not found');
            }

            $fullPath = Storage::disk($diskName)->path($documentPath);

            return response()->download($fullPath);
        } catch (Exception $e) {
            return $this->errorBack($e->getMessage());
        }
    }

    /**
     * Approve the specified ID verification.
     *
     * @param Request $request
     * @param IdVerification $idVerification
     * @return JsonResponse
     */
    public function approve(Request $request, IdVerification $idVerification): JsonResponse
    {
        if (!$idVerification->isPending()) {
            return $this->errorJson('Only pending verifications can be approved');
        }

        $idVerification->update([
            'status' => IdVerificationStatus::APPROVED,
        ]);

        $idVerification->user->update([
            'is_id_verified' => 1,
        ]);

        return $this->successJson('ID Verification has been approved successfully');
    }

    /**
     * Reject the specified ID verification.
     *
     * @param Request $request
     * @param IdVerification $idVerification
     * @return JsonResponse
     */
    public function reject(Request $request, IdVerification $idVerification): JsonResponse
    {
        if (!$idVerification->isPending()) {
            return $this->errorJson('Only pending verifications can be rejected');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $idVerification->update([
            'status' => IdVerificationStatus::REJECTED,
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return $this->successJson('ID Verification has been rejected');
    }

    /**
     * Remove the specified ID verification.
     *
     * @param IdVerification $idVerification
     * @return JsonResponse
     */
    public function destroy(IdVerification $idVerification): JsonResponse
    {
        $idVerification->delete();

        return $this->successJson('ID Verification deleted successfully');
    }

    /**
     * Bulk approve selected ID verifications.
     *
     * @param Request $request
     * @return JsonResponse
     */
    /**
     * Bulk approve selected ID verifications.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                $verifications = IdVerification::whereIn('id', $ids)
                    ->where('status', IdVerificationStatus::PENDING)
                    ->get();

                if ($verifications->isEmpty()) {
                    throw new \Exception(translate('No pending verifications found to approve'));
                }

                foreach ($verifications as $verification) {
                    $verification->update([
                        'status' => IdVerificationStatus::APPROVED,
                    ]);

                    $verification->user->update([
                        'is_id_verified' => 1,
                    ]);
                }

                return count($verifications);
            },
            IdVerification::class,
            'Successfully approved :count verification(s)',
            'Error approving verifications'
        );
    }

    /**
     * Bulk reject selected ID verifications.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkReject(Request $request): JsonResponse
    {
        // Validate only the extra field needed for rejection
        $validator = $this->validateRequest($request, [
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first()
            ], 422);
        }

        return $this->handleBulkAction(
            $request,
            function ($ids, $request) {
                $verifications = IdVerification::whereIn('id', $ids)
                    ->where('status', IdVerificationStatus::PENDING)
                    ->get();

                if ($verifications->isEmpty()) {
                    throw new \Exception(translate('No pending verifications found to reject'));
                }

                foreach ($verifications as $verification) {
                    $verification->update([
                        'status' => IdVerificationStatus::REJECTED,
                        'rejection_reason' => $request->rejection_reason,
                    ]);

                    $verification->user->update([
                        'is_id_verified' => 0,
                    ]);
                }

                return count($verifications);
            },
            IdVerification::class,
            'Successfully rejected :count verification(s)',
            'Error rejecting verifications'
        );
    }

    /**
     * Apply filters to the ID verification query.
     *
     * @param Builder $query
     * @return Builder
     */
    private function applyFilters(Builder $query): Builder
    {
        if (request()->filled('user')) {
            $query->where('user_id', request('user'));
        }

        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        if (request()->filled('document_type')) {
            $query->where('document_type', request('document_type'));
        }

        return $query;
    }

    /**
     * Calculate status counters for ID verifications.
     *
     * @return array
     */
    private function getVerificationCounters(): array
    {
        return [
            'pending' => IdVerification::where('status', IdVerificationStatus::PENDING)->count(),
            'approved' => IdVerification::where('status', IdVerificationStatus::APPROVED)->count(),
            'rejected' => IdVerification::where('status', IdVerificationStatus::REJECTED)->count(),
            'verified_users' => IdVerification::where('status', IdVerificationStatus::APPROVED)->distinct('user_id')->count(),
        ];
    }
}
