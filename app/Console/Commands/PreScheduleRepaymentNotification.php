<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\ScheduleRepayment;
use App\Traits\Telegram;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PreScheduleRepaymentNotification extends Command
{
    use Telegram;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pre-schedule-repayment-notification
                            {--hours=24 : Number of hours before repayment to send notification}';

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
        $notificationHours = (int)$this->option('hours') ?? 24;
        $now = Carbon::now();
        $notificationTime = $now->copy()->addHours($notificationHours);

        // Get repayments due within the specified hours
        $upcomingRepayments = ScheduleRepayment::with(['loan.user'])
            ->where('status', 'pending')
            ->whereBetween('repayment_date', [$now, $notificationTime])
            ->get();

        $notifiedCount = 0;

        foreach ($upcomingRepayments as $repayment) {
            $user = $repayment->loan->user;

            if ($user && $user->telegram_chat_id) {
                $this->sendRepaymentReminder($user, $repayment);
                $notifiedCount++;
            }
        }

        $this->info("Pre-schedule repayment notification completed. Notified {$notifiedCount} users.");
    }

    /**
     * Send repayment reminder via Telegram
     *
     * @param User $user
     * @param ScheduleRepayment $repayment
     */
    protected function sendRepaymentReminder(User $user, ScheduleRepayment $repayment)
    {
        $dueDate = Carbon::parse($repayment->repayment_date)->format('M j, Y H:i');
        $amount = number_format($repayment->emi_amount, 2);
        $loanId = $repayment->id;
        $currency = 'USD';

        $message = <<<MSG
        ⏰ *Upcoming Repayment Reminder*

        *Loan ID:* #$loanId
        *Amount Due:* $currency $amount
        *Due Date:* $dueDate

        Please ensure sufficient funds are available in your account.

        _This is an automated reminder._
        MSG;

        $this->sendTelegramMarkdown($user->telegram_chat_id, $message);
    }
}
