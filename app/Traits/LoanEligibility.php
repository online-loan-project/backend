<?php


namespace App\Traits;

use App\Constants\ConstRequestLoanStatus;
use App\Models\Borrower;
use App\Models\CreditScore;
use App\Models\IncomeInformation;
use App\Models\RequestLoan;

trait LoanEligibility
{
    use BaseApiResponse;

    public function checkLoanEligibility(int $userId, int $requestLoanId)
    {
        $borrower = Borrower::where('user_id', $userId)->first();
        if (!$borrower) {
            return 'Borrower not found';
        }
        // Retrieve the requested loan details
        $requestLoan = RequestLoan::find($requestLoanId);
        if (!$requestLoan) {
            return 'Loan request not found';
        }

        // Retrieve the user's income information
        $incomeInfo = IncomeInformation::where('request_loan_id', $requestLoanId)->first();
        if (!$incomeInfo) {
            return 'Income information not found';
        }

        // Retrieve the user's credit score
        $userCredit = CreditScore::where('user_id', $userId)->first();
        if (!$userCredit) {
            return 'Credit information not found';
        }

//      more logic here example :
//       1. Age (must be above a minimum threshold, e.g., 21-60 years) If age < 21 or age > 60: Not Eligible
        $borrower_age = date_diff(date_create($borrower->dob), date_create('now'))->y;
        if ($borrower_age < 21 || $borrower_age > 60) {
            return 'Not Eligible';
        }

//      2. Income loan_amount ≤ 5 * income ( Loan should not bigger than 5 times the income )
        if ($requestLoan->loan_amount > (5 * $incomeInfo->income)) {
            return 'Loan amount is too high';
        }
//      3. Employment Type (Salaried or Self-employed or Unemployed) If employment_type == Unemployed: Not Eligible
        if ($incomeInfo->employee_type == 'Unemployed') {
            return 'Not Eligible';
        }

//      4. Credit Score credit_score >= 50
        if ($userCredit->score < 50) {
            return 'Your credit is not good enough';
        }
//      5. Existing Loans (must not exceed a certain debt-to-income ratio) If (existing_loans_total / income) > 40%: Not Eligible

//      Update loan status
        $requestLoan->update(['status' => ConstRequestLoanStatus::ELIGIBLE]);

        return 'Eligible for loan request';
    }

}
