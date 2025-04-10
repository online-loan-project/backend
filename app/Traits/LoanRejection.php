<?php

namespace App\Traits;

use App\Constants\ConstRequestLoanStatus;
use App\Models\RequestLoan;

trait LoanRejection
{
    /**
     * Reject a loan request
     *
     * @param RequestLoan $requestLoan
     * @param string $reason
     * @return RequestLoan
     */
    public function rejectLoan(RequestLoan $requestLoan, string $reason = '')
    {
        if ($requestLoan->status === ConstRequestLoanStatus::PENDING || $requestLoan->status === ConstRequestLoanStatus::ELIGIBLE) {
            $requestLoan->status = ConstRequestLoanStatus::REJECTED;
            $requestLoan->rejection_reason = $reason;
            $requestLoan->save();
        }

        return $requestLoan;
    }
}
