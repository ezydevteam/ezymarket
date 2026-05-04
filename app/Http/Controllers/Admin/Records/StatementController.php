<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Records;

use App\Http\Controllers\Controller;
use App\Enums\StatementType;
use App\Models\Financial\Statement;
use App\Traits\HandlesValidation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{Request, JsonResponse};
use Exception;

/**
 * Statement Analytics Controller
 *
 * Manages financial statement records with filtering,
 * statistics, and administrative actions.
 */
class StatementController extends Controller
{
    use HandlesValidation;

    /**
     * Display a listing of statements with filters and statistics
     */
    public function index(Request $request): View|JsonResponse
    {
        try {
            $counters = $this->getStatementCounters();
            $columns = $this->getDataTableColumns();
            $filters = $this->getDataTableFilters($request);

            $query = Statement::query()->with(['user:id,firstname,lastname,username,email,avatar']);
            $statementsCount = $query->count();

            // Handle DataTables AJAX requests
            if ($request->ajax() && $request->has('draw')) {
                $this->applyDataTableFilters($query);

                $totalFiltered = $query->count();

                $this->applyDataTableSorting($query);

                $limit = $request->input('length', 10);
                $offset = $request->input('start', 0);
                $statements = $query->limit($limit)->offset($offset)->get();

                $data = $statements->map(fn($statement) => $this->formatStatementRow($statement))->toArray();

                return response()->json([
                    'draw' => intval($request->input('draw')),
                    'recordsTotal' => $statementsCount,
                    'recordsFiltered' => $totalFiltered,
                    'data' => $data,
                ]);
            }

            return view('admin.records.statements', compact('counters', 'columns', 'filters', 'statementsCount'));
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
            }
            throw $e;
        }
    }

    /**
     * Display the specified statement details modal
     */
    public function detailsModal(Statement $statement): View
    {
        $statement->load('user');
        return view('admin.records.statements.modals.details', compact('statement'));
    }

    /**
     * Remove the specified statement
     */
    public function destroy(Statement $statement): JsonResponse
    {
        $statement->delete();
        return $this->successJson(translate('Statement record deleted successfully'));
    }

    /**
     * Bulk delete statements
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function (array $ids) {
                return Statement::whereIn('id', $ids)->delete();
            },
            Statement::class,
            translate(':count statement(s) deleted successfully'),
            translate('An error occurred while deleting statements')
        );
    }

    /**
     * Apply DataTables filters
     */
    private function applyDataTableFilters($query): void
    {
        $request = request();

        if ($request->hasAny(['statement', 'type', 'user'])) {
            $this->applyFilters($query);
        }

        $search = $request->input('search.value');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('statements.id', 'like', "%{$search}%")
                    ->orWhere('statements.title', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($filters = $request->input('filters')) {
            foreach ($filters as $column => $value) {
                if ($value === null || $value === '') continue;

                switch ($column) {
                    case '3': // Type
                        $query->where('statements.type', $value);
                        break;
                    case '4': // Date Range
                        if (is_array($value)) {
                            if (!empty($value['from'])) $query->whereDate('statements.created_at', '>=', $value['from']);
                            if (!empty($value['to'])) $query->whereDate('statements.created_at', '<=', $value['to']);
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
                    $query->join('users', 'statements.user_id', '=', 'users.id')
                        ->orderBy('users.firstname', $dir)
                        ->select('statements.*');
                } else {
                    $query->orderBy($columnName, $dir);
                }
            }
        } else {
            $query->latest();
        }
    }

    /**
     * Format a statement row for DataTables
     */
    private function formatStatementRow(Statement $statement): array
    {
        return [
            'bulk' => '<input type="checkbox" class="form-check-input row-checkbox" name="ids[]" value="' . $statement->id . '">',
            'user' => view('components.user', ['user' => $statement->user, 'avatarSize' => 'sm'])->render(),
            'title' => '<div class="fw-medium text-dark">' . $statement->title . '</div>' .
                       '<div class="text-muted small">#ID: ' . $statement->id . '</div>',
            'type' => '<span class="status-badge ' . $statement->type->badge() . '">' .
                      '<i class="bi ' . $statement->type->icon() . ' me-1"></i>' . $statement->type->label() . '</span>',
            'amount' => '<div class="fw-bold text-' . $statement->type->color() . '">' .
                        getAmount((float) $statement->total) . '</div>',
            'created_at' => '<div class="text-muted">' . dateFormat($statement->created_at) . '</div>',
            'actions' => view('admin.records.statements.draw.actions', compact('statement'))->render(),
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
            ['data' => 'title', 'name' => 'title', 'title' => translate('Title'), 'orderable' => true],
            ['data' => 'type', 'name' => 'type', 'title' => translate('Type'), 'orderable' => true, 'class' => 'text-center'],
            ['data' => 'amount', 'name' => 'total', 'title' => translate('Amount'), 'orderable' => true, 'class' => 'text-center'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => translate('Date'), 'orderable' => true, 'class' => 'text-center'],
            ['data' => 'actions', 'title' => translate('Actions'), 'orderable' => false, 'exportable' => false, 'class' => 'text-end no-export'],
        ];
    }

    /**
     * Get DataTables filters
     */
    private function getDataTableFilters(Request $request): array
    {
        return [
            [
                'type' => 'select',
                'column' => '3',
                'label' => translate('Type'),
                'options' => array_map(fn($type) => ['value' => $type->value, 'label' => $type->label()], StatementType::cases()),
                'value' => $request->query('type')
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
     * Apply search and filter criteria to the query (for deep-linking)
     */
    private function applyFilters($query): void
    {
        $request = request();

        if ($request->filled('statement')) {
            $query->where('statements.id', $request->statement);
        }

        if ($request->filled('type')) {
            $query->where('statements.type', $request->type);
        }

        if ($request->filled('user')) {
            $query->where('statements.user_id', $request->user);
        }
    }

    /**
     * Calculate statement statistics counters
     */
    private function getStatementCounters(): array
    {
        $baseQuery = Statement::query();

        $totalCount = (clone $baseQuery)->count();
        $creditCount = (clone $baseQuery)->where('type', StatementType::CREDIT)->count();
        $debitCount = (clone $baseQuery)->where('type', StatementType::DEBIT)->count();

        $sumTotal = function ($type = null) use ($baseQuery) {
            $q = clone $baseQuery;
            if ($type) $q->where('type', $type);
            return (float) $q->sum('total');
        };

        $creditsAmount = $sumTotal(StatementType::CREDIT);
        $debitsAmount = $sumTotal(StatementType::DEBIT);
        $netRevenue = $creditsAmount - $debitsAmount;
        $growth = $creditsAmount > 0 ? round(($netRevenue / $creditsAmount) * 100, 1) : 0;

        $counters = [
            'total' => [
                'total' => $totalCount,
                'amount' => (clone $baseQuery)->sum('amount'),
            ],
            'credit' => [
                'total' => $creditCount,
                'amount' => $creditsAmount,
            ],
            'debit' => [
                'total' => $debitCount,
                'amount' => $debitsAmount,
            ],
            'net_revenue' => [
                'amount' => $netRevenue,
                'total' => $growth, // Using total key for the percentage in x-counter-card logic
            ],
        ];

        // Previous week for percentage comparison
        $lastWeekStart = now()->subDays(7);
        $calculatePercent = fn($current, $prev) => $prev > 0 ? round((($current - $prev) / $prev) * 100) : ($current > 0 ? 100 : 0);

        $prevTotal = (clone $baseQuery)->where('created_at', '<', $lastWeekStart)->count();
        $prevCredit = (clone $baseQuery)->where('type', StatementType::CREDIT)->where('created_at', '<', $lastWeekStart)->count();
        $prevDebit = (clone $baseQuery)->where('type', StatementType::DEBIT)->where('created_at', '<', $lastWeekStart)->count();

        $counters['total']['percent'] = $calculatePercent($totalCount, $prevTotal);
        $counters['credit']['percent'] = $calculatePercent($creditCount, $prevCredit);
        $counters['debit']['percent'] = $calculatePercent($debitCount, $prevDebit);

        return $counters;
    }
}
