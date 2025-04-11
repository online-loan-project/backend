<?php

namespace App\Console\Commands;

use App\Models\ScheduleRepayment;
use App\Models\User;
use App\Traits\Telegram;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DummyNotification extends Command
{
    use Telegram;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dummy-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends notifications to users about upcoming repayments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $notifiedCount = 1;
        $this->sendRepaymentReminder();

        $this->info("Pre-schedule repayment notification completed. Notified {$notifiedCount} users.");
    }

    /**
     * Send repayment reminder via Telegram
     *
     * @param User $user
     * @param ScheduleRepayment $repayment
     */
    protected function sendRepaymentReminder()
    {
        $dueDate = Carbon::now()->format('M j, Y H:i');
        $amount = number_format(1000, 2);
        $loanId = 1;
        $currency = 'USD';

        $message = <<<MSG
            💰 *PAYMENT REMINDER* 💰
            ━━━━━━━━━━━━━━━━━━
            🆔 *Loan Reference:* `#$loanId`
            💵 *Amount Due:* `$currency $amount`
            ⏳ *Due Date:* `$dueDate`
            ━━━━━━━━━━━━━━━━━━

            💡 Please ensure you have sufficient funds in your account to avoid any late fees or service interruptions.

            📌 Payment will be automatically processed on the due date.

            🔔 Need help? contact our support team.

            _This is an automated notification. Please do not reply directly._
            MSG;

//        $this->sendTelegramMarkdown(343413763, $message);
        $this->sendTelegramAnimation(-1002400700243, 'https://media1.giphy.com/media/v1.Y2lkPTc5MGI3NjExaWdmZzllaHJjcm50YmMzNzA3MjI1eG5sMmRhbXNpMnJvMGtubTZ6ayZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/EStFDzPoTdMuA1JpJ1/giphy.gif', $message, 'markdown', true);
    }
}
