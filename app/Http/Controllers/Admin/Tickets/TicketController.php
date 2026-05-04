<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Tickets;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Support\{Ticket, TicketCategory, TicketReply, TicketReplyAttachment};
use App\Traits\HandlesValidation;
use App\Facades\Notification;
use Exception;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Ticket Controller
 *
 * Manages support ticket operations including creation, replies,
 * attachments, and status management.
 */
class TicketController extends Controller
{
    use HandlesValidation;

    /**
     * Display list of tickets with filtering options.
     */
    public function index(Request $request): View|JsonResponse
    {
        try {
            $counters = $this->getTicketCounters();
            $columns = $this->getDataTableColumns();
            $filters = $this->getDataTableFilters($request);
            $categories = TicketCategory::active()->get();
            $users = User::active()->get();

            $query = Ticket::withUserTrashed()->with(['user', 'category']);
            $ticketsCount = $query->count();

            // Handle DataTables AJAX requests
            if ($request->ajax() && $request->has('draw')) {
                $this->applyDataTableFilters($query);

                $totalFiltered = $query->count();

                $this->applyDataTableSorting($query);

                $limit = $request->input('length', 10);
                $offset = $request->input('start', 0);
                $tickets = $query->limit($limit)->offset($offset)->get();

                $data = $tickets->map(fn($ticket) => $this->formatTicketRow($ticket))->toArray();

                return response()->json([
                    'draw' => intval($request->input('draw')),
                    'recordsTotal' => $ticketsCount,
                    'recordsFiltered' => $totalFiltered,
                    'data' => $data,
                ]);
            }

            return view('admin.tickets.index', compact('counters', 'columns', 'filters', 'ticketsCount', 'categories', 'users'));
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
            }
            throw $e;
        }
    }

    /**
     * Display the ticket creation modal content.
     */
    public function createModal(): View
    {
        $categories = TicketCategory::active()->get();
        $users = User::active()->get();
        return view('admin.tickets.modals.create', compact('categories', 'users'));
    }

    /**
     * Store a new ticket with initial reply and attachments.
     */
    public function store(Request $request): JsonResponse
    {
        abort_if(!@settings('ticket')->status, 403, translate('Ticket system is currently disabled'));

        $validator = $this->validateRequest($request, [
            'subject' => ['required', 'string', 'block_patterns', 'max:255'],
            'user' => ['required', 'integer', 'exists:users,id'],
            'category' => ['required', 'integer', 'exists:ticket_categories,id'],
            'description' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->handleValidationErrors($validator);
        }

        $user = User::where('id', $request->user)->active()->firstOrFail();
        $category = TicketCategory::where('id', $request->category)->active()->firstOrFail();

        try {
            $ticket = $this->createTicket($request, $user, $category);

            return $this->successJson(translate('Ticket Created Successfully'), [
                'redirect' => route('admin.tickets.show', $ticket->id)
            ]);
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Display a specific ticket with all replies.
     */
    public function show(Ticket $ticket): View
    {
        return view('admin.tickets.show', compact('ticket'));
    }

    /**
     * Add a reply to an existing ticket.
     */
    public function reply(Request $request, Ticket $ticket): JsonResponse
    {
        $validator = $this->validateRequest($request, [
            'reply' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->handleValidationErrors($validator);
        }

        try {
            $recentReply = $ticket->replies()
                ->where('admin_id', authAdmin()?->id)
                ->where('body', $request->reply)
                ->where('created_at', '>=', now()->subMinutes(1))
                ->first();

            if ($recentReply) {
                return $this->errorJson(translate('This reply has already been sent'));
            }

            $ticketReply = $this->createTicketReply($request, $ticket);
            $ticket->update(['status' => TicketStatus::OPENED]);

            Notification::sendTicketReplyNotification($ticket, $ticketReply);

            return $this->successJson(translate('Your Reply Sent Successfully'));
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Download a ticket reply attachment.
     */
    public function download(Ticket $ticket, TicketReplyAttachment $attachment): StreamedResponse|JsonResponse
    {
        $disk = Storage::disk('local');

        if (!$disk->exists($attachment->path)) {
            return $this->errorJson(translate('The requested file does not exist'));
        }

        try {
            return $this->streamFile($disk, $attachment->path, $attachment->name);
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Close an open ticket.
     */
    public function close(Request $request, Ticket $ticket): JsonResponse
    {
        $ticket->update(['status' => TicketStatus::CLOSED]);

        if ($request->has('notify_user') && $request->notify_user) {
            Notification::sendTicketStatusNotification($ticket);
        }

        return $this->successJson(translate('Ticket Closed Successfully'));
    }

    /**
     * Bulk close tickets.
     */
    public function bulkClose(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                $closedCount = Ticket::whereIn('id', $ids)
                    ->opened()
                    ->update(['status' => TicketStatus::CLOSED]);

                if ($closedCount === 0) {
                    throw new Exception(translate('Selected tickets are already closed'));
                }

                return $closedCount;
            },
            Ticket::class,
            translate(':count ticket(s) closed successfully'),
            translate('Error closing tickets')
        );
    }

    /**
     * Delete a ticket and its associated data.
     */
    public function destroy(Ticket $ticket): JsonResponse
    {
        try {
            if ($ticket->trashed()) {
                if ($ticket->isArchivedByAdmin()) {
                    $ticket->forceDelete();
                    return $this->successJson(translate('Ticket permanently deleted successfully'));
                }

                $ticket->moveToAdminTrash();
                return $this->successJson(translate('Ticket moved to administrative trash successfully'));
            }

            if ($ticket->isOpened()) {
                $ticket->update(['status' => TicketStatus::CLOSED]);
            }

            $ticket->delete();

            return $this->successJson(translate('Ticket moved to trash successfully'));
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Restore multiple deleted tickets.
     */
    public function bulkRestore(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                $tickets = Ticket::onlyTrashed()->whereIn('id', $ids)->get();

                if ($tickets->isEmpty()) {
                    throw new Exception(translate('No trashed tickets found to restore'));
                }

                foreach ($tickets as $ticket) {
                    $ticket->restore();
                }

                return count($tickets);
            },
            Ticket::class,
            translate(':count ticket(s) restored successfully'),
            translate('Error restoring tickets')
        );
    }

    /**
     * Restore a deleted ticket.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $ticket = Ticket::onlyTrashed()->findOrFail($id);
            $ticket->restore();

            return $this->successJson(translate('Ticket restored successfully'));
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Display trashed (soft deleted) tickets.
     */
    public function trash(Request $request): View|JsonResponse
    {
        try {
            $counters = $this->getTicketCounters();
            $columns = $this->getDataTableColumns();
            $filters = $this->getDataTableFilters($request);
            $categories = TicketCategory::active()->get();
            $users = User::active()->get();

            $query = Ticket::onlyAdminTrashed()->with(['user', 'category']);
            $ticketsCount = $query->count();

            // Handle DataTables AJAX requests
            if ($request->ajax() && $request->has('draw')) {
                $this->applyDataTableFilters($query);

                $totalFiltered = $query->count();

                $this->applyDataTableSorting($query);

                $limit = $request->input('length', 10);
                $offset = $request->input('start', 0);
                $tickets = $query->limit($limit)->offset($offset)->get();

                $data = $tickets->map(fn($ticket) => $this->formatTicketRow($ticket))->toArray();

                return response()->json([
                    'draw' => intval($request->input('draw')),
                    'recordsTotal' => $ticketsCount,
                    'recordsFiltered' => $totalFiltered,
                    'data' => $data,
                ]);
            }

            return view('admin.tickets.trash', compact('counters', 'columns', 'filters', 'ticketsCount', 'categories', 'users'));
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
            }
            throw $e;
        }
    }

    /**
     * Permanently delete a ticket.
     */
    public function permanentlyDelete(int $id): JsonResponse
    {
        try {
            $ticket = Ticket::onlyTrashed()->findOrFail($id);
            $ticket->forceDelete();

            return $this->successJson(translate('Ticket permanently deleted successfully'));
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Bulk delete tickets.
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                $tickets = Ticket::withTrashed()->whereIn('id', $ids)->get();

                foreach ($tickets as $ticket) {
                    if ($ticket->trashed()) {
                        if ($ticket->isArchivedByAdmin()) {
                            $ticket->forceDelete();
                        } else {
                            $ticket->moveToAdminTrash();
                        }
                    } else {
                        if ($ticket->isOpened()) {
                            $ticket->update(['status' => TicketStatus::CLOSED]);
                        }
                        $ticket->delete();
                    }
                }

                return count($tickets);
            },
            Ticket::class,
            translate(':count ticket(s) processed successfully'),
            translate('Error deleting tickets')
        );
    }

    /**
     * Apply DataTables filters
     */
    private function applyDataTableFilters($query): void
    {
        $request = request();

        if ($request->filled('user')) {
            $query->where('user_id', $request->user);
        }

        $search = $request->input('search.value');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('tickets.id', 'like', "%{$search}%")
                    ->orWhere('tickets.subject', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
            });
        }

        if ($filters = $request->input('filters')) {
            foreach ($filters as $column => $value) {
                if ($value === null || $value === '') continue;

                switch ($column) {
                    case '2': // Category
                        $query->where('ticket_category_id', $value);
                        break;
                    case '3': // Status
                        $query->where('status', $value);
                        break;
                    case '4': // Date Range
                        if (is_array($value)) {
                            if (!empty($value['from'])) $query->whereDate('created_at', '>=', $value['from']);
                            if (!empty($value['to'])) $query->whereDate('created_at', '<=', $value['to']);
                        }
                        break;
                }
            }
        }
    }

    /**
     * Apply DataTables sorting
     */
    private function applyDataTableSorting($query): void
    {
        $request = request();
        $order = $request->input('order');
        $columns = $this->getDataTableColumns();

        if ($order && isset($order[0])) {
            $columnIdx = $order[0]['column'];
            $dir = $order[0]['dir'];
            $columnName = $columns[$columnIdx]['name'] ?? null;

            if ($columnName) {
                if ($columnName === 'user.firstname') {
                    $query->join('users', 'tickets.user_id', '=', 'users.id')
                        ->orderBy('users.firstname', $dir)
                        ->select('tickets.*');
                } elseif ($columnName === 'category.name') {
                    $query->join('ticket_categories', 'tickets.ticket_category_id', '=', 'ticket_categories.id')
                        ->orderBy('ticket_categories.name', $dir)
                        ->select('tickets.*');
                } else {
                    $query->orderBy($columnName, $dir);
                }
            }
        } else {
            $query->latest();
        }
    }

    /**
     * Format a ticket row for DataTables
     */
    private function formatTicketRow(Ticket $ticket): array
    {
        return [
            'bulk' => '<input type="checkbox" class="form-check-input row-checkbox" name="ids[]" value="' . $ticket->id . '">',
            'user' => view('components.user', ['user' => $ticket->user, 'avatarSize' => 'sm'])->render(),
            'subject' => '<div class="fw-medium text-dark">' . $ticket->subject . '</div>' .
                         '<div class="text-muted small">#ID: ' . $ticket->id . '</div>',
            'category' => '<span class="badge bg-light text-dark fw-medium border">' . ($ticket->category?->name ?? translate('N/A')) . '</span>',
            'status' => '<span class="status-badge ' . $ticket->status->badgeClass() . '">' .
                        '<i class="bi ' . $ticket->status->icon() . ' me-1"></i>' . $ticket->status->label() . '</span>',
            'created_at' => '<div class="text-muted">' . dateFormat($ticket->created_at) . '</div>',
            'actions' => view('admin.tickets.draw.actions', compact('ticket'))->render(),
            'DT_RowClass' => ($ticket->trashed() && !$ticket->isArchivedByAdmin()) ? 'trashed-row' : ''
        ];
    }

    /**
     * Get DataTable columns configuration
     */
    private function getDataTableColumns(): array
    {
        return [
            ['data' => 'bulk', 'title' => '<input type="checkbox" class="form-check-input bulk-select-checkbox">', 'orderable' => false, 'searchable' => false, 'exportable' => false, 'class' => 'no-export'],
            ['data' => 'user', 'name' => 'user.firstname', 'title' => translate('User'), 'orderable' => true],
            ['data' => 'subject', 'name' => 'subject', 'title' => translate('Subject'), 'orderable' => true],
            ['data' => 'category', 'name' => 'category.name', 'title' => translate('Category'), 'orderable' => true, 'class' => 'text-center'],
            ['data' => 'status', 'name' => 'status', 'title' => translate('Status'), 'orderable' => true, 'class' => 'text-center'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => translate('Date'), 'orderable' => true, 'class' => 'text-center'],
            ['data' => 'actions', 'title' => translate('Actions'), 'orderable' => false, 'exportable' => false, 'class' => 'text-end no-export'],
        ];
    }

    /**
     * Get DataTables filters
     */
    private function getDataTableFilters(Request $request): array
    {
        $categories = TicketCategory::active()->get()->map(fn($cat) => ['value' => $cat->id, 'label' => $cat->name])->toArray();
        return [
            [
                'type' => 'select',
                'column' => '2',
                'label' => translate('Category'),
                'options' => $categories,
            ],
            [
                'type' => 'select',
                'column' => '3',
                'label' => translate('Status'),
                'options' => array_map(fn($status) => ['value' => $status->value, 'label' => $status->label()], TicketStatus::cases()),
                'value' => $request->query('status')
            ],
            [
                'type' => 'daterange',
                'column' => '4',
                'label' => translate('Date Range'),
                'value' => [
                    'from' => $request->query('from_date'),
                    'to' => $request->query('to_date'),
                ]
            ]
        ];
    }

    /**
     * Get ticket counters for opened and closed tickets.
     */
    private function getTicketCounters(): array
    {
        $baseQuery = Ticket::withUserTrashed();

        if (request()->filled('user')) {
            $baseQuery->where('user_id', request('user'));
        }

        $openedCount = (clone $baseQuery)->opened()->count();
        $closedCount = (clone $baseQuery)->closed()->count();
        $totalCount = (clone $baseQuery)->count();

        $counters = [
            'opened_tickets' => $openedCount,
            'closed_tickets' => $closedCount,
            'total_tickets' => $totalCount,
        ];

        // Previous week for percentage comparison
        $lastWeekStart = now()->subDays(7);
        $calculatePercent = fn($current, $prev) => $prev > 0 ? round((($current - $prev) / $prev) * 100) : ($current > 0 ? 100 : 0);

        $prevTotal = (clone $baseQuery)->where('created_at', '<', $lastWeekStart)->count();
        $prevOpened = (clone $baseQuery)->opened()->where('created_at', '<', $lastWeekStart)->count();
        $prevClosed = (clone $baseQuery)->closed()->where('created_at', '<', $lastWeekStart)->count();

        $counters['total_percent'] = $calculatePercent($totalCount, $prevTotal);
        $counters['opened_percent'] = $calculatePercent($openedCount, $prevOpened);
        $counters['closed_percent'] = $calculatePercent($closedCount, $prevClosed);

        return $counters;
    }

    /**
     * Create a new ticket with initial reply and attachments.
     */
    private function createTicket(Request $request, User $user, TicketCategory $category): Ticket
    {
        $ticket = Ticket::create([
            'subject' => $request->subject,
            'user_id' => $user->id,
            'ticket_category_id' => $category->id,
        ]);

        $ticketReply = TicketReply::create([
            'body' => $request->description,
            'ticket_id' => $ticket->id,
            'admin_id' => authAdmin()->id,
        ]);

        if ($request->hasFile('attachments')) {
            $this->saveAttachments($request->file('attachments'), $ticketReply, $ticket);
        }

        return $ticket;
    }

    /**
     * Create a ticket reply with attachments.
     */
    private function createTicketReply(Request $request, Ticket $ticket): TicketReply
    {
        $ticketReply = TicketReply::create([
            'body' => $request->reply,
            'ticket_id' => $ticket->id,
            'admin_id' => authAdmin()->id,
        ]);

        if ($request->hasFile('attachments')) {
            $this->saveAttachments($request->file('attachments'), $ticketReply, $ticket);
        }

        return $ticketReply;
    }

    /**
     * Save ticket reply attachments.
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
     */
    private function streamFile($disk, string $filePath, string $fileName): StreamedResponse
    {
        $headers = [
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        return new StreamedResponse(function () use ($disk, $filePath) {
            $stream = $disk->readStream($filePath);
            while (!feof($stream) && connection_status() === 0) {
                echo fread($stream, 1024 * 8);
                flush();
            }
            fclose($stream);
        }, 200, $headers);
    }
}
