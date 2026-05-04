<?php

namespace App\Http\Controllers\Admin\Mail;

use App\Http\Controllers\Controller;
use App\Models\MailTemplate;
use App\Traits\HandlesValidation;
use Codebay\PayPal\PayPalHttp\Serializer\Json;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\{Request, RedirectResponse, JsonResponse};
use Illuminate\View\View;

/**
 * Mail Template Controller
 *
 * Handles CRUD operations for email templates including custom templates
 * with automatic shortcode detection and validation.
 */
class MailTemplateController extends Controller
{
    use HandlesValidation;

    /**
     * Display list of mail templates.
     *
     * Filters membership-related templates if membership is disabled.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $mailTemplates = $this->getFilteredTemplates();

        return view('admin.mail.templates.index', compact('mailTemplates'));
    }

    /**
     * Show form to create new mail template.
     *
     * @return View
     */
    public function create()
    {
        return view('admin.mail.templates.create');
    }

    /**
     * Store new mail template.
     *
     * Automatically extracts shortcodes from subject and body if not provided.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = $this->validateRequestWithInput($request, [
            'alias' => ['required', 'string', 'max:255', 'unique:mail_templates,alias', 'regex:/^[a-z0-9_]+$/'],
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'content' => ['required'],
        ]);

        if ($validator instanceof \Illuminate\Http\RedirectResponse) {
            return $validator;
        }

        $shortcodes = $this->parseShortcodes($request);

        MailTemplate::create([
            'alias' => str_replace(' ', '_', strtolower($request->alias)),
            'name' => $request->name,
            'subject' => $request->subject,
            'content' => $request->content,
            'shortcodes' => array_values($shortcodes),
            'is_active' => $request->has('is_active'),
        ]);

        return $this->createdRedirect('admin.mail.templates.index', null, 'Template Created Successfully');
    }

    /**
     * Show form to edit mail template.
     *
     * @param Request $request
     * @param MailTemplate $mailTemplate
     * @return View
     */
    public function edit(Request $request, MailTemplate $mailTemplate): View
    {
        return view('admin.mail.templates.edit', compact('mailTemplate'));
    }

    /**
     * Update existing mail template.
     *
     * Default templates cannot be disabled.
     *
     * @param Request $request
     * @param MailTemplate $mailTemplate
     * @return RedirectResponse
     */
    public function update(Request $request, MailTemplate $mailTemplate): RedirectResponse
    {
        $validator = $this->validateRequestWithoutInput($request, [
            'subject' => ['required', 'string', 'max:255'],
            'content' => ['required'],
        ]);

        if ($validator instanceof RedirectResponse) {
            return $validator;
        }

        $isActive = $mailTemplate->isDefault() ? true : $request->has('is_active');

        $mailTemplate->update([
            'subject' => $request->subject,
            'is_active' => $isActive,
            'content' => $request->content,
        ]);

        return $this->updatedBack();
    }

    /**
     * Delete mail template.
     *
     * Default templates cannot be deleted.
     *
     * @param MailTemplate $mailTemplate
     * @return JsonResponse
     */
    public function destroy(MailTemplate $mailTemplate): JsonResponse
    {
        if ($mailTemplate->isDefault()) {
            return $this->errorJson('Default templates cannot be deleted');
        }

        try {
            $mailTemplate->delete();
            return $this->successJson('Template deleted successfully');
        } catch (\Exception $e) {
            return $this->errorJson('Failed to delete template');
        }
    }

    /**
     * Bulk delete mail templates.
     *
     * Default templates cannot be deleted.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                // Get templates by IDs
                $templates = MailTemplate::whereIn('id', $ids)->get();

                // Filter out default templates
                $deletableIds = $templates->filter(function ($template) {
                    return !$template->isDefault();
                })->pluck('id')->toArray();

                if (empty($deletableIds)) {
                    throw new \Exception(translate('Default templates cannot be deleted'));
                }

                // Check if any default templates were in the selection
                $skippedCount = count($ids) - count($deletableIds);

                $deleted = MailTemplate::whereIn('id', $deletableIds)->delete();

                if ($skippedCount > 0) {
                    return [
                        'count' => $deleted,
                        'message' => translate(':count template(s) deleted successfully. :skipped default template(s) were skipped.', [
                            'count' => $deleted,
                            'skipped' => $skippedCount
                        ])
                    ];
                }

                return $deleted;
            },
            MailTemplate::class,
            ':count template(s) deleted successfully',
            'Failed to delete templates'
        );
    }

    /**
     * Get filtered mail templates based on membership settings.
     *
     * Excludes membership-related templates if membership is disabled.
     *
     * @return Collection
     */
    private function getFilteredTemplates(): Collection
    {
        if (get_license_type(2) && @settings('premium')->status) {
            return MailTemplate::all();
        }

        return MailTemplate::whereNotIn('alias', [
            'premium_about_to_expire',
            'premium_expired',
        ])->get();
    }

    /**
     * Parse shortcodes from request input or extract from content.
     *
     * Supports comma-separated input or automatic extraction from subject/body.
     *
     * @param Request $request
     * @return array
     */
    private function parseShortcodes(Request $request): array
    {
        if (!$request->filled('shortcodes')) {
            return $this->extractShortcodes($request->content . ' ' . $request->subject);
        }

        $input = $request->shortcodes;

        // Parse comma-separated or single shortcode
        $shortcodes = strpos($input, ',') !== false
            ? array_map('trim', explode(',', $input))
            : [trim($input)];

        // Remove empty values
        $shortcodes = array_filter($shortcodes);

        // If parsing resulted in empty array, extract from content
        return empty($shortcodes)
            ? $this->extractShortcodes($request->content . ' ' . $request->subject)
            : $shortcodes;
    }

    /**
     * Extract shortcodes from template content.
     *
     * Finds all {{shortcode}} patterns and returns unique shortcode names.
     *
     * @param string $content Template content (subject + body)
     * @return array
     */
    private function extractShortcodes(string $content): array
    {
        preg_match_all('/\{\{(\w+)\}\}/', $content, $matches);

        return !empty($matches[1]) ? array_unique($matches[1]) : [];
    }
}
