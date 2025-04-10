<?php

namespace App\Traits;

use App\Constants\ConstLoanStatus;
use App\Constants\ConstRequestLoanStatus;
use App\Models\RequestLoan;
use App\Models\Loan;
use App\Models\CreditScore;
use App\Models\InterestRate;
use App\Models\ScheduleRepayment;
use Carbon\Carbon;

trait LoanApproval
{

    public function approveLoan($request_loan_id)
    {
        // Find the request loan
        $requestLoan = RequestLoan::find($request_loan_id);
        if (!$requestLoan) {
            return 'Request loan not found';
        }

        // Check if loan is already approved
        if ($requestLoan->status !== ConstRequestLoanStatus::ELIGIBLE && $requestLoan->status !== ConstRequestLoanStatus::PENDING) {
            return 'Loan request cannot be approved from current status: ' . $requestLoan->status;
        }

        // Get user's credit score
        $creditScore = CreditScore::where('user_id', $requestLoan->user_id)
            ->where('status', 1)
            ->first();

        if (!$creditScore) {
            return 'User does not have a valid credit score';
        }

        // Determine interest rate based on credit score
        $interestRate = $this->determineInterestRate();

        // Calculate loan repayment amount
        $loanRepayment = $this->calculateLoanRepayment(
            $requestLoan->loan_amount,
            $interestRate->rate,
            $requestLoan->loan_duration
        );

        // Create the loan record
        $loan = Loan::create([
            'request_loan_id' => $requestLoan->id,
            'user_id' => $requestLoan->user_id,
            'credit_score_id' => $creditScore->id,
            'loan_duration' => $requestLoan->loan_duration,
            'loan_repayment' => $loanRepayment,
            'interest_rate_id' => $interestRate->id,
            'status' => ConstLoanStatus::PAID, // Active
        ]);

        // Calculate revenue (loan_repayment - loan_amount)
        $loan->revenue = $loanRepayment - $requestLoan->loan_amount;
        $loan->save();

        // Create repayment schedule
        $this->createRepaymentSchedule($loan);

        // Update request loan status
        $requestLoan->status = ConstRequestLoanStatus::APPROVED;
        $requestLoan->save();

        return $loan;
    }

    protected function determineInterestRate()
    {
        //get interest rate the latest one
        $interestRate = InterestRate::where('status', 1)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$interestRate) {
            return 'No interest rate defined' ;
        }

        return $interestRate;
    }

    protected function calculateLoanRepayment(float $loan_amount, float $interestRate, int $termInMonths)
    {
        // Convert annual rate to monthly and percentage to decimal
        $monthlyRate = ($interestRate / 100) / 12;

        // Calculate repayment amount using EMI formula
        $emi = $loan_amount * $monthlyRate * pow(1 + $monthlyRate, $termInMonths) / (pow(1 + $monthlyRate, $termInMonths) - 1);

        // Total repayment is EMI multiplied by term
        return round($emi * $termInMonths, 2);
    }

    protected function createRepaymentSchedule(Loan $loan)
    {
        $termInMonths = $loan->loan_duration;
        $loan_amount = $loan->requestLoan->loan_amount;
        $interestRate = $loan->interestRate->rate;

        // Convert annual rate to monthly and percentage to decimal
        $monthlyRate = ($interestRate / 100) / 12;

        // Calculate EMI amount
        $emi = $loan_amount * $monthlyRate * pow(1 + $monthlyRate, $termInMonths) / (pow(1 + $monthlyRate, $termInMonths) - 1);
        $emi = round($emi, 2);

        $startDate = Carbon::now()->addMonth(); // First payment due next month

        for ($i = 1; $i <= $termInMonths; $i++) {
            ScheduleRepayment::create([
                'loan_id' => $loan->id,
                'repayment_date' => $startDate->copy()->addMonths($i - 1),
                'emi_amount' => $emi,
                'status' => 0, // Pending
            ]);
        }
    }
}
