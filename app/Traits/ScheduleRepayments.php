<?php
namespace App\Traits;
use App\Constants\ConstLoanRepaymentStatus;
use App\Constants\ConstLoanStatus;
use App\Constants\ConstRequestLoanStatus;
use App\Models\CreditScore;
use App\Models\InterestRate;
use App\Models\Loan;
use App\Models\RequestLoan;
use App\Models\ScheduleRepayment;

Trait ScheduleRepayments
{
    public function MarkedAsPaid($scheduleRepaymentId)
    {
        // Find the schedule repayment by ID
        $scheduleRepayment = ScheduleRepayment::find($scheduleRepaymentId);
        if (!$scheduleRepayment) {
            return 'Schedule repayment not found';
        }

        // Check if the schedule repayment is already paid
        if ($scheduleRepayment->status == ConstLoanRepaymentStatus::PAID) {
            return 'Schedule repayment is already paid';
        }
        if($scheduleRepayment->status != ConstLoanRepaymentStatus::LATE) {
            $creditScore = CreditScore::where('user_id', $scheduleRepayment->loan->user_id)->first();
            if($creditScore == 0 ) {
                return 'Credit score is already 0';
            }
            if ($creditScore) {
                $creditScore->score += 1; // Add 1 points for paid per one repayment
                $creditScore->save();
            }
        }
        //update the schedule repayment status
        $scheduleRepayment->status = ConstLoanRepaymentStatus::PAID;
        $scheduleRepayment->save();

        return 'Schedule repayment marked as paid successfully';
    }

    public function MarkedAsUnpaid($scheduleRepaymentId)
    {
        // Find the schedule repayment by ID
        $scheduleRepayment = ScheduleRepayment::find($scheduleRepaymentId);
        if (!$scheduleRepayment) {
            return 'Schedule repayment not found';
        }

        // Check if the schedule repayment is already unpaid
        if ($scheduleRepayment->status == ConstLoanRepaymentStatus::UNPAID) {
            return 'Schedule repayment is already unpaid';
        }

        //update the schedule repayment status
        $scheduleRepayment->status = ConstLoanRepaymentStatus::UNPAID;
        $scheduleRepayment->save();
        return 'Schedule repayment marked as unpaid successfully';
    }
    //late payment
    public function MarkedAsLate($scheduleRepaymentId)
    {
        // Find the schedule repayment by ID
        $scheduleRepayment = ScheduleRepayment::find($scheduleRepaymentId);
        if (!$scheduleRepayment) {
            return 'Schedule repayment not found';
        }

        // Check if the schedule repayment is already late
        if ($scheduleRepayment->status == ConstLoanRepaymentStatus::LATE) {
            return 'Schedule repayment is already late';
        }

        //update the schedule repayment status
        $scheduleRepayment->status = ConstLoanRepaymentStatus::LATE;
        $scheduleRepayment->save();
        $creditScore = CreditScore::where('user_id', $scheduleRepayment->loan->user_id)->first();
        if($creditScore == 0 ) {
            return 'Credit score is already 0';
        }
        if ($creditScore) {
            $creditScore->score -= 1; // Deduct 1 points for late  per one repayment
            $creditScore->save();
        }
        return 'Schedule repayment marked as late successfully';
    }


}
