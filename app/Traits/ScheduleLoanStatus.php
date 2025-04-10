<?php

namespace App\Traits;

use App\Constants\ConstScheduleRepaymentStatus;
use App\Models\CreditScore;
use App\Models\ScheduleRepayment;
use App\Models\Loan;
use Carbon\Carbon;

trait ScheduleLoanStatus
{
    public function markAsPaid(ScheduleRepayment $scheduleRepayment)
    {
        // Only update if currently pending
        if ($scheduleRepayment->status === ConstScheduleRepaymentStatus::PENDING) {
            $scheduleRepayment->update([
                'status' => ConstScheduleRepaymentStatus::PAID,
                'paid_at' => Carbon::now(),
            ]);

            $this->updateLoanStatus($scheduleRepayment->loan, 1);
        }

        return $scheduleRepayment->fresh();
    }

    public function markAsLate(ScheduleRepayment $scheduleRepayment)
    {
        // Only update if currently pending
        if ($scheduleRepayment->status === ConstScheduleRepaymentStatus::PENDING) {
            $scheduleRepayment->update([
                'status' => ConstScheduleRepaymentStatus::LATE,
                'paid_at' => Carbon::now(),
            ]);

            $this->updateLoanStatus($scheduleRepayment->loan, 0);
        }

        return $scheduleRepayment->fresh();
    }

    protected function updateLoanStatus(Loan $loan, $status)
    {
        $pendingRepaymentsCount = $loan->scheduleRepayments()
            ->where('status', ConstScheduleRepaymentStatus::PENDING)
            ->count();

        if ($pendingRepaymentsCount === 0) {
            $loan->status = 1; // Mark loan as paid
        } else {
            // There are still pending repayments
            $loan->status = 0; // Mark loan as unpaid
        }

        $loan->save();

        $creditScore = CreditScore::where('user_id', $loan->user_id)
            ->where('status', 1)
            ->first();

        if ($creditScore) {
            $newScore = $creditScore->score;

            if ($status) {
                $newScore = min($creditScore->score + 10, 100); // Won't exceed 100
            } else {
                $newScore = max($creditScore->score - 10, 0); // Won't go below 0
            }

            $creditScore->update([
                'score' => $newScore,
            ]);
        }

        return $loan->fresh();
    }
}
