<?php

namespace App\Http\Controllers\Borrower;

use App\Constants\ConstRequestLoanStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Borrower\RequestLoanRequest;
use App\Models\IncomeInformation;
use App\Models\NidInformation;
use App\Models\RequestLoan;
use App\Traits\BaseApiResponse;
use App\Traits\LoanEligibility;
use Illuminate\Http\Request;

class RequestLoanController extends Controller
{
    use BaseApiResponse;
    use LoanEligibility;
    public function store(RequestLoanRequest $request)
    {
        //
        $user = auth()->user();
        if(!$user){
            return $this->failed('User not found', 404);
        }
        //query request loan status pending and eligibility for current user
        $requestLoan = RequestLoan::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [ConstRequestLoanStatus::PENDING, ConstRequestLoanStatus::ELIGIBLE])
            ->first();
        if($requestLoan){
            return $this->failed('You already have a pending request', 400);
        }

        $nid_image = $request->file('nid_image');
        $nid_image_path = null;
        if ($nid_image) {
            $nid_image_path = $this->uploadImage($nid_image, 'nid_info', 'public');
        }

        $bank_statement = $request->file('bank_statement');
        $bank_statement_path = null;
        if ($bank_statement) {
            $bank_statement_path = $this->uploadImage($bank_statement, 'bank_statement', 'public');
        }
        $requestLoan = RequestLoan::query()->create([
            'loan_amount' => $request->loan_amount,
            'loan_duration' => $request->loan_duration,
            'loan_type' => $request->loan_type,
            'status' => 'pending',
            'user_id' => $user->id
        ]);

        $nidInformation = NidInformation::query()->create([
            'nid_number' => $request->nid_number,
            'nid_image' => $nid_image_path,
            'status' => 1,
            'request_loan_id' => $requestLoan->id
        ]);

        $incomeInformation = IncomeInformation::query()->create([
            'employee_type' => $request->employee_type,
            'position' => $request->position,
            'income' => $request->income,
            'bank_statement' => $bank_statement_path,
            'request_loan_id' => $requestLoan->id
        ]);

        // Check loan eligibility
        $eligibility = $this->checkLoanEligibility($user->id, $requestLoan->id);

        $data = [
            'request_loan' => $requestLoan,
            'nid_information' => $nidInformation,
            'income_information' => $incomeInformation,
            'eligibility' => $eligibility,
        ];

        return $this->success($data, 'Request Loan created successfully');



    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        // show request loan details with status = pending
        $requestLoan = RequestLoan::query()->where('status', 'pending')->get();
        if ($requestLoan) {
            return $this->success($requestLoan);
        }
        return $this->failed('Request Loan not found', 404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RequestLoan $requestLoan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RequestLoan $requestLoan)
    {
        //
    }


}
