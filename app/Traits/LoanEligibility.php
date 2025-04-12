<?php


namespace App\Traits;

use App\Constants\ConstRequestLoanStatus;
use App\Models\Borrower;
use App\Models\CreditScore;
use App\Models\IncomeInformation;
use App\Models\RequestLoan;
use App\Models\User;

trait LoanEligibility
{
    use TelegramNotification;
    use BaseApiResponse;

    public function checkLoanEligibility(int $userId, int $requestLoanId)
    {
        $user = User::query()->find($userId);
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
            return 'Not Eligible (Invalid age)';
        }

        // 2. Employment Type
        if ($incomeInfo->employee_type == 'Unemployed') {
            return 'Not Eligible (Unemployed)';
        }

        // 3. Determine approval percentage based on credit score
        $creditScore = $userCredit->score;
        $approvedPercentage = 0;

        if ($creditScore >= 50) {
            $approvedPercentage = 100;
        } elseif ($creditScore >= 40) {
            $approvedPercentage = 75;
        } elseif ($creditScore >= 30) {
            $approvedPercentage = 50;
        } elseif ($creditScore >= 20) {
            $approvedPercentage = 25;
        }

        // If approvedPercentage is 0, reject
        if ($approvedPercentage === 0) {
            return 'Your credit score is too low (less than 20)';
        }

        // Calculate approved amount
        $approvedAmount = ($approvedPercentage / 100) * $requestLoan->loan_amount;
//      4. Income loan_amount ≤ 5 * income ( Loan should not bigger than 5 times the income )
        if ($approvedAmount > (5 * $incomeInfo->income)) {
            return 'Loan amount is too high';
        }
        // Update loan status and optionally save approved amount
        $requestLoan->update([
            'status' => ConstRequestLoanStatus::ELIGIBLE,
            'approved_amount' => $approvedAmount // Assuming this column exists
        ]);
        $this->sendTelegram($user->telgram_chat_id, "Loan eligibility check completed. Approved amount: {$approvedAmount} ({$approvedPercentage}% of requested)");
        return "Eligible. Approved amount: {$approvedAmount} ({$approvedPercentage}% of requested)";
    }


}
