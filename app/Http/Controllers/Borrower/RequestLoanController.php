<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use App\Http\Requests\Borrower\RequestLoanRequest;
use App\Models\IncomeInformation;
use App\Models\NidInformation;
use App\Models\RequestLoan;
use App\Traits\BaseApiResponse;
use Illuminate\Http\Request;

class RequestLoanController extends Controller
{
    use BaseApiResponse;
    public function store(RequestLoanRequest $request)
    {
        //
        $user = auth()->user();
        if(!$user){
            return $this->failed('User not found', 404);
        }
        $nid_image_path = '';
        $bank_statement_path = '';
        //nid_image upload to storage and get the path
        if ($request->hasFile('nid_image')) {
            // Generate a unique filename and store the image
            $imagePath = $request->file('image')->store('uploads/nid_image', 'public');
           $nid_image_path = $imagePath;
        }
        //bank_statement upload to storage and get the path
        if ($request->hasFile('bank_statement')) {
            // Generate a unique filename and store the image
            $imagePath = $request->file('image')->store('uploads/bank_statement', 'public');
            $bank_statement_path = $imagePath;
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

        $data = [
            'request_loan' => $requestLoan,
            'nid_information' => $nidInformation,
            'income_information' => $incomeInformation
        ];

        return $this->success($data, 'Request Loan created successfully');



    }

    /**
     * Display the specified resource.
     */
    public function show(RequestLoan $requestLoan)
    {
        //
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
