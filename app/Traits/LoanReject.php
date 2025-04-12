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

Trait LoanReject
{
    public function rejectLoan($requestLoanId, $reason = '')
    {
        // Find the loan request by ID
        $requestLoan = RequestLoan::find($requestLoanId);
        if (!$requestLoan) {
            return 'Loan request not found';
        }

        // Check if the loan is already approved
        if ($requestLoan->status == ConstRequestLoanStatus::APPROVED || $requestLoan->status == ConstRequestLoanStatus::REJECTED || $requestLoan->status == ConstRequestLoanStatus::PENDING) {
            return 'Loan request is already processed';
        }

        //update the request loan status
        $requestLoan->status = ConstRequestLoanStatus::REJECTED;
        $requestLoan->rejection_reason = $reason;
        $requestLoan->save();
        return 'Loan rejected successfully';
    }

}
