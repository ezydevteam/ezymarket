<?php

namespace App\Http\Controllers\Theme;

use App\Enums\FeedbackStatus;
use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Traits\HandlesValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FeedbackController extends Controller
{
    use HandlesValidation;

    public function create()
    {
        return theme_view('feedback');
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequestWithInput($request, [
            'field' => 'required|string|in:' . implode(',', array_keys(Feedback::getFeedbackFields())),
            'description' => 'required|string|min:50|max:1000',
            'screenshots.*' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'screenshots' => 'array|max:4',
        ], [
            'field.required' => translate('Please select a feedback reason'),
            'description.required' => translate('Description is required'),
            'description.min' => translate('Description length min. 50 characters'),
            'description.max' => translate('Description length max. 1000 characters'),
            'screenshots.*.max' => translate('Screenshot must be less than 2MB'),
            'screenshots.max' => translate('Maximum 4 screenshots allowed'),
        ]);

        if ($validated instanceof \Illuminate\Http\RedirectResponse) {
            return $validated;
        }

        if ($this->hasUserSubmittedRecentFeedback()) {
            toastr()->error(translate('You have already submitted feedback recently. Please wait before submitting again.'));
            return redirect()->back()->withInput();
        }

        try {
            $this->processFeedback($validated->validated());

            toastr()->success(translate('Your feedback has been submitted successfully'));
            return redirect()->back();
        } catch (\Exception $e) {
            toastr()->error(translate('Something went wrong!'));
            return redirect()->back()->withInput();
        }
    }

    private function hasUserSubmittedRecentFeedback(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return Feedback::where('user_id', Auth::id())
            ->where('created_at', '>=', now()->subMinutes(720))
            ->exists();
    }

    private function processFeedback(array $data): void
    {
        DB::transaction(function () use ($data) {
            $screenshotPaths = [];
            if (request()->hasFile('screenshots')) {
                foreach (request()->file('screenshots') as $index => $screenshot) {
                    if ($index >= 4) break;
                    $path = storageFileUpload($screenshot, 'feedback-screenshots/', 'public');
                    if ($path) {
                        $screenshotPaths[] = $path;
                    }
                }
            }

            Feedback::create([
                'field' => $data['field'],
                'description' => $data['description'],
                'screenshots' => $screenshotPaths,
                'user_id' => Auth::id(),
                'status' => FeedbackStatus::PENDING,
            ]);
        });
    }
}
