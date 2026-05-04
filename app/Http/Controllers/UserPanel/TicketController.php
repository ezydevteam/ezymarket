<?php

namespace App\Http\Controllers\UserPanel;

use App\Enums\TicketStatus;
use App\Events\{TicketCreated, TicketReplyCreated};
use App\Facades\Notification;
use App\Http\Controllers\Controller;
use App\Models\Support\{Ticket, TicketCategory, TicketReply, TicketReplyAttachment};
use App\Traits\HandlesValidation;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{RedirectResponse, Request, JsonResponse};
use Illuminate\Support\Facades\{Storage, Validator};
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Database\Eloquent\Builder;

/**
 * Portal Ticket Controller
 *
 * Handles ticket operations for authenticated users including
 * creation, replies, and file attachments.
 */
class TicketController extends Controller
{
    use HandlesValidation;

    /**
     * Display paginated list of user's tickets with filtering.
     *
     * @return View|JsonResponse
     */
    public function index(): View|JsonResponse
    {
        $user = authUser();
        $query = Ticket::where('user_id', $user->id)->with(['category', 'replies']);

        // Handle DataTables AJAX requests
        if (request()->ajax() && request()->has('draw')) {
            try {
                $totalRecords = (clone $query)->count();

                // Apply filters, search and sorting
                $this->applyDataTableFilters($query);
                $filteredRecords = (clone $query)->count();
                $this->applyDataTableSorting($query);

                // Fetch Paginated Results
                $start = request()->input('start', 0);
                $length = request()->input('length', 10);
                $tickets = $query->skip($start)->take($length)->get();

                // Format Rows for DataTables
                $data = $tickets->map(fn($ticket) => $this->formatTicketRow($ticket));

                return response()->json([
                    'draw' => intval(request()->input('draw')),
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $filteredRecords,
                    'data' => $data
                ]);
            } catch (Exception $e) {
                return $this->errorJson($e->getMessage(), [], 500);
            }
        }

        $tickets = collect([]);
        $categories = TicketCategory::active()->get();
        $columns = $this->getDataTableColumns();
        $filters = $this->getDataTableFilters();
        $hasRecords = $user->tickets()->exists();

        return theme_view('userpanel.tickets.index', compact('tickets', 'categories', 'columns', 'filters', 'hasRecords'));
    }

    /**
     * Store a new ticket with initial reply and attachments.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->validateTicketStore($request);

        if ($validator->fails()) {
            return $this->handleValidationErrorsJson($validator);
        }

        $category = TicketCategory::where('id', $request->category)->active()->firstOrFail();

        try {
            $ticket = $this->createTicket($request, authUser(), $category);

            Notification::sendTicketNewNotification($ticket);
            event(new TicketCreated($ticket));

            return $this->successJson('Ticket Created Successfully', [
                'redirect' => route('user.ticket.show', $ticket->id)
            ]);
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Display a specific ticket with all replies.
     *
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        $ticket = Ticket::where('user_id', authUser()->id)
            ->where('id', $id)
            ->with(['replies', 'category'])
            ->withAttachments()
            ->firstOrFail();

        return theme_view('userpanel.tickets.show', compact('ticket'));
    }

    /**
     * Add a reply to an existing ticket.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function reply(Request $request, int $id): JsonResponse
    {
        $user = authUser();
        $ticket = Ticket::where('user_id', $user->id)->where('id', $id)->firstOrFail();

        $validator = $this->validateTicketReply($request);

        if ($validator->fails()) {
            return $this->handleValidationErrorsJson($validator);
        }

        try {
            // Prevent duplicate replies
            $recentReply = $ticket->replies()
                ->where('user_id', $user->id)
                ->where('body', $request->reply)
                ->where('created_at', '>=', now()->subMinutes(1))
                ->first();

            if ($recentReply) {
                return $this->errorJson('This reply has already been sent');
            }

            $ticketReply = $this->createTicketReply($request, $ticket, $user);

            $ticket->update(['status' => TicketStatus::OPENED]);

            event(new TicketReplyCreated($ticketReply));

            return $this->successJson('Your Reply Sent Successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Download a ticket reply attachment.
     *
     * @param int $id
     * @param int $attachment_id
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|RedirectResponse
     */
    public function download(int $id, int $attachment_id): \Symfony\Component\HttpFoundation\StreamedResponse|RedirectResponse
    {
        $ticket = Ticket::where('user_id', authUser()->id)->where('id', $id)->firstOrFail();
        $attachment = TicketReplyAttachment::where('id', $attachment_id)->firstOrFail();

        $disk = Storage::disk('local');

        if (!$disk->exists($attachment->path)) {
            toastr()->error(translate('The requested file does not exist'));
            return back();
        }

        try {
            return $this->streamFile($disk, $attachment->path, $attachment->name);
        } catch (Exception $e) {
            toastr()->error($e->getMessage());
            return back();
        }
    }

    /**
     * Cancel a ticket.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(int $id): JsonResponse
    {
        $user = authUser();
        $ticket = Ticket::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        if (!$ticket->canCancel()) {
            return $this->errorJson('This ticket cannot be cancelled');
        }

        $ticket->cancel();

        return $this->successJson('Ticket cancelled successfully');
    }

    /**
     * Delete a ticket.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $user = authUser();
        $ticket = Ticket::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        if (!$ticket->canDelete()) {
            return $this->errorJson('This ticket cannot be deleted');
        }

        $ticket->delete();

        return $this->successJson('Ticket history deleted successfully');
    }

    /**
     * Return the AJAX modal content for ticket creation.
     *
     * @return string
     */
    public function modalCreate(): string
    {
        $categories = TicketCategory::active()->get();
        return theme_view('userpanel.tickets.partials.modals.modal_ticket_create', compact('categories'))->render();
    }


    /**
     * Validate ticket store request with attachment rules.
     *
     * @param Request $request
     * @return \Illuminate\Validation\Validator
     */
    private function validateTicketStore(Request $request)
    {
        $attachments = $request->file('attachments');
        $allowedExts = explode(',', @settings('ticket')->file_types);
        $maxFiles = @settings('ticket')->max_files;
        $maxFileSize = @settings('ticket')->max_file_size;

        return Validator::make($request->all(), [
            'subject' => ['required', 'string', 'block_patterns', 'max:255'],
            'category' => ['required', 'integer', 'exists:ticket_categories,id'],
            'description' => ['required', 'string'],
            'attachments' => [
                'nullable',
                'max:' . ($maxFileSize * 1024),
                function ($attribute, $value, $fail) use ($attachments, $allowedExts, $maxFiles, $maxFileSize) {
                    if (!$attachments) {
                        return;
                    }
                    $this->validateAttachments($attachments, $allowedExts, $maxFiles, $maxFileSize, $fail);
                },
            ],
        ]);
    }

    /**
     * Validate ticket reply request with attachment rules.
     *
     * @param Request $request
     * @return \Illuminate\Validation\Validator
     */
    private function validateTicketReply(Request $request)
    {
        $attachments = $request->file('attachments');
        $allowedExts = explode(',', @settings('ticket')->file_types);
        $maxFiles = @settings('ticket')->max_files;
        $maxFileSize = @settings('ticket')->max_file_size;

        return Validator::make($request->all(), [
            'reply' => ['required', 'string'],
            'attachments' => [
                'nullable',
                'max:' . ($maxFileSize * 1024),
                function ($attribute, $value, $fail) use ($attachments, $allowedExts, $maxFiles, $maxFileSize) {
                    if (!$attachments) {
                        return;
                    }
                    $this->validateAttachments($attachments, $allowedExts, $maxFiles, $maxFileSize, $fail);
                },
            ],
        ]);
    }

    /**
     * Validate attachment files.
     *
     * @param array $attachments
     * @param array $allowedExts
     * @param int $maxFiles
     * @param int $maxFileSize
     * @param callable $fail
     * @return void
     */
    private function validateAttachments(array $attachments, array $allowedExts, int $maxFiles, int $maxFileSize, callable $fail): void
    {
        if (count($attachments) > $maxFiles) {
            $fail(translate('Max :max files can be uploaded', ['max' => $maxFiles]));
            return;
        }

        foreach ($attachments as $attachment) {
            if ($attachment->getSize() > ($maxFileSize * 1048576)) {
                $fail(translate('Max file size is :max MB', ['max' => $maxFileSize]));
                return;
            }

            $ext = strtolower($attachment->getClientOriginalExtension());
            if (!in_array($ext, $allowedExts)) {
                $fail(translate('Some uploaded files are not supported'));
                return;
            }
        }
    }

    /**
     * Create a new ticket with initial reply and attachments.
     *
     * @param Request $request
     * @param \App\Models\User $user
     * @param TicketCategory $category
     * @return Ticket
     */
    private function createTicket(Request $request, $user, TicketCategory $category): Ticket
    {
        $ticket = Ticket::create([
            'subject' => $request->subject,
            'user_id' => $user->id,
            'ticket_category_id' => $category->id,
        ]);

        $ticketReply = TicketReply::create([
            'body' => $request->description,
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
        ]);

        if ($request->hasFile('attachments')) {
            $this->saveAttachments($request->file('attachments'), $ticketReply, $ticket);
        }

        return $ticket;
    }

    /**
     * Create a ticket reply with attachments.
     *
     * @param Request $request
     * @param Ticket $ticket
     * @param \App\Models\User $user
     * @return TicketReply
     */
    private function createTicketReply(Request $request, Ticket $ticket, $user): TicketReply
    {
        $ticketReply = TicketReply::create([
            'body' => $request->reply,
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
        ]);

        if ($request->hasFile('attachments')) {
            $this->saveAttachments($request->file('attachments'), $ticketReply, $ticket);
        }

        return $ticketReply;
    }

     /**
     * Apply filter and search logic to the ticket query for DataTables.
     *
     * @param Builder $query
     * @return void
     */
    private function applyDataTableFilters(Builder $query): void
    {
        // Global Search
        if ($search = request()->input('search.value')) {
            $cleanSearch = ltrim($search, '#');
            $query->where(function ($q) use ($search, $cleanSearch) {
                $q->where('id', 'like', "%{$cleanSearch}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('replies', function ($q) use ($search) {
                        $q->where('body', 'like', "%{$search}%");
                    });

                // Search in Enum Labels (Ticket Status)
                foreach (TicketStatus::cases() as $status) {
                    if (str_contains(strtolower($status->label()), strtolower($search))) {
                        $q->orWhere('status', $status->value);
                    }
                }
            });
        }

        // Column-specific AJAX filters
        if ($filters = request()->input('filters')) {
            foreach ($filters as $column => $value) {
                if (!$value) continue;

                if ($column == '1') { // Category Column
                    $query->where('ticket_category_id', $value);
                } elseif ($column == '2') { // Status Column
                    $query->where('status', $value);
                } elseif ($column == '3') { // Date Range Column
                    if (is_array($value)) {
                        if (!empty($value['from']) && strtotime($value['from'])) {
                            $query->whereDate('created_at', '>=', $value['from']);
                        }
                        if (!empty($value['to']) && strtotime($value['to'])) {
                            $query->whereDate('created_at', '<=', $value['to']);
                        }
                    }
                }
            }
        }
    }

    /**
     * Apply sorting to the ticket query for DataTables.
     *
     * @param Builder $query
     * @return void
     */
    private function applyDataTableSorting(Builder $query): void
    {
        $order = request()->input('order.0', []);
        $sortColumns = [
            0 => 'subject',
            1 => 'ticket_category_id',
            2 => 'status',
            3 => 'created_at'
        ];

        $columnIndex = $order['column'] ?? 3;
        $sortColumn = $sortColumns[$columnIndex] ?? 'id';
        $sortDir = $order['dir'] ?? 'desc';

        $query->orderBy($sortColumn, $sortDir);
    }

    /**
     * Format a single ticket row for the DataTables AJAX response.
     *
     * @param Ticket $ticket
     * @return array
     */
    private function formatTicketRow(Ticket $ticket): array
    {
        return [
            'item' => theme_view('userpanel.tickets.partials.row_item', compact('ticket'))->render(),
            'category' => '<span class="badge bg-light text-dark px-3 py-2 rounded">' . $ticket->category->name . '</span>',
            'status' => theme_view('userpanel.tickets.partials.row_status', compact('ticket'))->render(),
            'date' => '<span class="text-muted">' . dateFormat($ticket->created_at) . '</span>',
            'actions' => theme_view('userpanel.tickets.partials.row_actions', compact('ticket'))->render()
        ];
    }

    /**
     * Get columns for the DataTables
     *
     * @return array
     */
    private function getDataTableColumns(): array
    {
        return [
            ['data' => 'item', 'name' => 'subject', 'title' => translate('Subject'), 'orderable' => true, 'searchable' => true],
            ['data' => 'category', 'name' => 'ticket_category_id', 'title' => translate('Category'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'status', 'name' => 'status', 'title' => translate('Status'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'date', 'name' => 'created_at', 'title' => translate('Date'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'actions', 'name' => 'actions', 'title' => translate('Actions'), 'orderable' => false, 'searchable' => false, 'class' => 'text-end'],
        ];
    }

    /**
     * Get filters for the DataTables
     *
     * @return array
     */
    private function getDataTableFilters(): array
    {
        return [
            [
                'type' => 'select',
                'column' => 1, // Category Column
                'label' => translate('Category'),
                'options' => TicketCategory::active()->get()->map(fn($cat) => ['value' => $cat->id, 'label' => $cat->name])->toArray()
            ],
            [
                'type' => 'select',
                'column' => 2, // Status Column
                'label' => translate('Ticket Status'),
                'options' => array_map(fn($case) => ['value' => $case->value, 'label' => $case->label()], TicketStatus::cases())
            ],
            [
                'type' => 'daterange',
                'column' => 3, // Date Range Column (Index in sortColumns is 3)
                'label' => translate('Date Range'),
            ]
        ];
    }

     /**
     * Save ticket reply attachments.
     *
     * @param array $files
     * @param TicketReply $ticketReply
     * @param Ticket $ticket
     * @return void
     */
    private function saveAttachments(array $files, TicketReply $ticketReply, Ticket $ticket): void
    {
        foreach ($files as $attachment) {
            TicketReplyAttachment::create([
                'name' => $attachment->getClientOriginalName(),
                'path' => storageFileUpload($attachment, "tickets/{$ticket->id}/", "local"),
                'ticket_reply_id' => $ticketReply->id,
            ]);
        }
    }

    /**
     * Stream file for download.
     *
     * @param \Illuminate\Filesystem\FilesystemAdapter $disk
     * @param string $filePath
     * @param string $fileName
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function streamFile($disk, string $filePath, string $fileName): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        return new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($disk, $filePath) {
            $stream = $disk->readStream($filePath);
            while (!feof($stream) && connection_status() === 0) {
                echo fread($stream, 1024 * 8);
                flush();
            }
            fclose($stream);
        }, 200, $headers);
    }

}
