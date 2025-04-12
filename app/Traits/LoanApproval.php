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
use PhpParser\Node\Stmt\Trait_;

Trait LoanApproval
{
    public function approveLoan($requestLoanId)
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

        //credit score check by user id
        $userCredit = CreditScore::where('user_id', $requestLoan->user_id)->first();
        if (!$userCredit) {
            return 'Credit information not found';
        }
        $creditScore = $userCredit->score;

        //interest rate check the latest one
        $interestRate = InterestRate::query()->latest()->first();
        if (!$interestRate) {
            return 'Interest rate not found';
        }
        logger($userCredit->id);
        $totalLoanRepayment = $this->calculateLoanRepayment($requestLoan->loan_amount, $interestRate->rate, $requestLoan->loan_duration);
        $loan = Loan::query()->create([
            'request_loan_id' => $requestLoan->id,
            'user_id' => $requestLoan->user_id,
            'credit_score_id' => $userCredit->id,
            'loan_duration' => $requestLoan->loan_duration,
            'loan_repayment' => $totalLoanRepayment,
            'revenue' => $totalLoanRepayment - $requestLoan->loan_amount,
            'interest_rate_id' => $interestRate->id,
        ]);
        if (!$loan) {
            return 'Loan creation failed';
        }
        //create the schedule repayment
        $this->createScheduleRepayment($loan->id);
        //update the request loan status
        $requestLoan->status = ConstRequestLoanStatus::APPROVED;
        $requestLoan->save();
        return 'Loan approved successfully';
    }


    //calculate the loan repayment amount
    private function calculateLoanRepayment($loanAmount, $interestRate, $loanDuration)
    {
        // Convert annual interest rate to monthly and decimal
        $monthlyInterestRate = ($interestRate / 100) / 12;
        $emi = ($loanAmount * $monthlyInterestRate * pow(1 + $monthlyInterestRate, $loanDuration)) / (pow(1 + $monthlyInterestRate, $loanDuration) - 1);
        return round($emi*$loanDuration, 2);
    }
    //create the schedule repayment
    private function createScheduleRepayment($loanId)
    {
        $loan = Loan::find($loanId);
        if (!$loan) {
            return 'Loan not found';
        }
        $emiAmount = $loan->loan_repayment / $loan->loan_duration;
        for ($i = 1; $i <= $loan->loan_duration; $i++) {
            ScheduleRepayment::create([
                'repayment_date' => now()->addMonths($i),
                'emi_amount' => $emiAmount,
                'loan_id' => $loan->id,
            ]);
        }
    }

}
