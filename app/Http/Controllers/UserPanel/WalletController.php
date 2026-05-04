<?php

namespace App\Http\Controllers\UserPanel;

use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\Financial\{Statement, Transaction};
use App\Traits\HandlesValidation;
use Carbon\Carbon;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\View\View;

class WalletController extends Controller
{
    use HandlesValidation;
    /**
     * Display the user's wallet statements.
     */
    public function index(): View
    {
        $query = Statement::where('user_id', authUser()->id);
        $this->applyFilters($query);

        $statements = $query->orderByDesc('id')->paginate(15);
        $statements->appends(request()->only(['date_from', 'date_to']));

        return theme_view('userpanel.wallets.index', compact('statements'));
    }

    /**
     * Return the AJAX modal content for ticket creation.
     *
     * @return string
     */
    public function modalDeposit(): string
    {
        return theme_view('userpanel.wallets.modals.modal_deposit')->render();
    }

    /**
     * Process a deposit request.
     */
    public function deposit(Request $request): JsonResponse
    {
        $depositSettings = settings('deposit');
        if (!@$depositSettings->status) {
            return $this->errorJson('Deposit is currently disabled. Please contact support for assistance.');
        }

        $minimumAmount = (float) ($depositSettings->minimum ?? 0);

        $validator = $this->validateRequestJson($request, [
            'amount' => ['required', 'numeric', 'min:' . $minimumAmount, 'gt:0'],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $transaction = $this->createDepositTransaction($request->amount);

        return $this->successJson('Deposit created successfully. Redirecting to payment page...', [
            'redirect' => route('checkout.index', hash_encode($transaction->id)),
        ]);
    }

    /**
     * Get filtered statements with date range.
     */
    private function applyFilters($query): void
    {
        if (request()->filled('date_from')) {
            $query->where('created_at', '>=', Carbon::parse(request('date_from'))->startOfDay());
        }

        if (request()->filled('date_to')) {
            $query->where('created_at', '<=', Carbon::parse(request('date_to'))->endOfDay());
        }

    }

    /**
     * Create a deposit transaction.
     */
    private function createDepositTransaction(float $amount): Transaction
    {
        return Transaction::create([
            'user_id' => authUser()->id,
            'amount' => $amount,
            'total' => $amount,
            'type' => TransactionType::DEPOSIT,
        ]);
    }
}















