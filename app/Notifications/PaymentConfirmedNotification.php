<?php

namespace App\Notifications;

use App\Enums\TransactionType;
use App\Models\Financial\Transaction;
use App\Models\User;

class PaymentConfirmedNotification extends BaseNotification
{
    public $transaction;

    public function __construct(Transaction $transaction, User $user)
    {
        $this->transaction = $transaction;
        $this->user = $user;
    }

    public function getNotificationPreference(): string
    {
        return 'payment_confirmation';
    }

    public function toArray($notifiable)
    {
        $data = [
            'type' => 'payment_confirmation',
            'title' => $this->getNotificationTitle(),
            'message' => $this->getNotificationMessage(),
            'transaction_id' => $this->transaction->id,
            'transaction_type' => $this->transaction->type,
            'amount' => getAmount($this->transaction->amount),
            'fees' => getAmount($this->transaction->fees),
            'total' => getAmount($this->transaction->total),
            'payment_method' => $this->transaction->paymentGateway->name ?? 'N/A',
            'status' => $this->transaction->status_name,
            'action_url' => $this->getActionUrl(),
            'timestamp' => now()->toISOString(),
            'icon' => $this->getIcon(),
            'color' => 'success'
        ];

        $this->addTypeSpecificData($data);

        return $data;
    }

    public function getEmailData()
    {
        return [
            'template' => 'payment_confirmation',
            'shortcodes' => [
                'username' => $this->user->full_name,
                'user_email' => $this->user->email,
                'transaction_id' => $this->transaction->id,
                'transaction_type' => $this->transaction->type_name,
                'transaction_subtotal' => getAmount($this->transaction->amount),
                'payment_method' => $this->transaction->paymentGateway->name ?? 'N/A',
                'transaction_fees' => getAmount($this->transaction->fees),
                'transaction_total' => getAmount($this->transaction->total),
                'transaction_date' => dateFormat($this->transaction->created_at),
                'transaction_view_link' => $this->getActionUrl(),
                'transaction_status' => $this->transaction->status_name,
                'website_name' => @settings('general')->site_name,
            ]
        ];
    }

    /**
     * Get notification title based on transaction type
     */
    protected function getNotificationTitle(): string
    {
        switch ($this->transaction->type) {
            case TransactionType::DEPOSIT:
                return 'Deposit Successful';
            case TransactionType::PREMIUM:
                return 'Premium Membership Activated';
            case TransactionType::SUPPORT_PURCHASE:
                return 'Support Purchase Successful';
            case TransactionType::SUPPORT_EXTEND:
                return 'Support Extended';
            default:
                return 'Payment Successful';
        }
    }

    /**
     * Get notification message based on transaction type
     */
    protected function getNotificationMessage(): string
    {
        $amount = getAmount($this->transaction->total);

        switch ($this->transaction->type) {
            case TransactionType::DEPOSIT:
                return "Your deposit of {$amount} has been added to your account";

            case TransactionType::PREMIUM:
                $planName = $this->transaction->premiumPlan->name ?? 'premium membership';
                return "Your {$planName} membership for {$amount} is now active";

            case TransactionType::SUPPORT_PURCHASE:
                return "Your support purchase for {$amount} has been confirmed";

            case TransactionType::SUPPORT_EXTEND:
                return "Your support extension for {$amount} has been confirmed";

            default:
                return "Your payment of {$amount} has been confirmed";
        }
    }

    /**
     * Get icon based on transaction type
     */
    protected function getIcon(): string
    {
        switch ($this->transaction->type) {
            case TransactionType::DEPOSIT:
                return 'wallet';
            case TransactionType::PREMIUM:
                return 'gem';
            case TransactionType::SUPPORT_PURCHASE:
            case TransactionType::SUPPORT_EXTEND:
                return 'headset';
            default:
                return 'check-circle';
        }
    }

    protected function getActionUrl()
    {
        switch ($this->transaction->type) {
            case TransactionType::DEPOSIT:
                return route('user.balance.index');
            case TransactionType::PREMIUM:
                return route('user.settings.premium');
            case TransactionType::SUPPORT_PURCHASE:
            case TransactionType::SUPPORT_EXTEND:
                return route('user.license.index');
            default:
                return route('user.transaction.show', $this->transaction->id);
        }
    }

    /**
     * Add type-specific data to notification array
     */
    protected function addTypeSpecificData(&$data): void
    {
        switch ($this->transaction->type) {
            case TransactionType::PREMIUM:
                if ($this->transaction->premiumPlan) {
                    $data['plan'] = [
                        'id' => $this->transaction->premiumPlan->id,
                        'name' => $this->transaction->premiumPlan->name,
                        'interval' => $this->transaction->premiumPlan->interval_name
                    ];
                }
                break;

            case TransactionType::SUPPORT_PURCHASE:
            case TransactionType::SUPPORT_EXTEND:
                if ($this->transaction->support) {
                    $data['support'] = [
                        'months' => $this->transaction->support->months ?? 0,
                        'amount' => getAmount($this->transaction->support->amount ?? 0)
                    ];
                }
                break;
        }
    }
}
