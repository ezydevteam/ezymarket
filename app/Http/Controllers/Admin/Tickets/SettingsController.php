<?php

namespace App\Http\Controllers\Admin\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Traits\HandlesValidation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{RedirectResponse, Request};

/**
 * Ticket Settings Controller
 *
 * Manages ticket system configuration including file upload settings
 * and ticket behavior preferences.
 */
class SettingsController extends Controller
{
    use HandlesValidation;

    /**
     * Display ticket settings page.
     *
     * @return View
     */
    public function index(): View
    {
        return view('admin.tickets.settings');
    }

    /**
     * Update ticket configuration settings.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $validator = $this->validateRequest($request, [
            'ticket.status' => ['required', 'boolean'],
            'ticket.file_types' => ['required_if:ticket.status,1', 'string'],
            'ticket.max_files' => ['required_if:ticket.status,1', 'integer', 'min:1', 'max:100'],
            'ticket.max_file_size' => ['required_if:ticket.status,1', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return $this->handleValidationErrors($validator);
        }

        $update = Settings::updateSettings('ticket', $request->ticket);

        if (!$update) {
            return $this->errorBack('Update Failed');
        }

        return $this->updatedBack();
    }
}
