<?php

namespace App\Console\Commands;

use App\Models\Financial\Transaction;
use Illuminate\Console\Command;

class DeleteUnpaidTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:delete-unpaid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete unpaid transactions older than 1 hour';

    private const EXPIRY_HOURS = 1;

    public function handle(): int
    {
        $count = Transaction::where('created_at', '<', now()->subHours(self::EXPIRY_HOURS))
            ->unpaid()
            ->delete();

        if ($count === 0) {
            $this->info('No unpaid transactions to delete');
        } else {
            $this->info("Successfully deleted {$count} unpaid transaction(s)");
        }

        return self::SUCCESS;
    }
}
