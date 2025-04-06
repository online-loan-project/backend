<?php

namespace App\Http\Controllers\Borrower;

use App\Constants\ConstRequestLoanStatus;
use App\Http\Controllers\Controller;
use App\Models\Borrower;
use App\Models\NidInformation;
use App\Models\RequestLoan;
use Illuminate\Http\Request;

class NidController extends Controller
{

    // store nid information
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'nid_image' => 'required',
        ]);

        // Check if the file is an image
        if (!$request->file('nid_image')->isValid()) {
            return $this->failed('Invalid image file.', 422);
        }

        $data = $this->extractOcrData($request->file('nid_image')); // Extract OCR data

        $userData = auth()->user(); // Get the authenticated user
        if (!$userData) {
            return $this->failed('User not found.', 404);
        }
        // get borrower data from user_id
        $borrowerData = Borrower::query()->where('user_id', $userData->id)->first();
        if (!$borrowerData) {
            return $this->failed('Borrower not found.', 404);
        }

        // match the extracted data with the borrower data only first_name, last_name
        if (strtolower($data['first_name']) !== strtolower($borrowerData->first_name) || strtolower($data['last_name']) !== strtolower($borrowerData->last_name)) {
            return $this->failed(null ,'NID information does not match with the borrower data.', null, 422);
        }

        // store nid number in the database
        $nidInformation = NidInformation::query()->create([
            'nid_number' => $data['nid'],
            'nid_image' => $request->file('nid_image')->store('uploads/nid_image', 'public'),
            'status' => 1,
            'request_loan_id' => 0,
        ]);

        return $this->success($nidInformation, 'NID information extracted successfully.');
    }
    // show nid information
    public function show(Request $request)
    {
        // get nid information
        $nidInformation = NidInformation::query()
            ->where('nid_number', $request->nid_number)->where('status', 1)
            ->limit(1)
            ->first();
        if (!$nidInformation) {
            return $this->failed('NID information not found', 404);
        }
        return $this->success($nidInformation, 'NID information retrieved successfully.');
    }
}
