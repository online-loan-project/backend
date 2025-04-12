<?php

namespace App\Http\Controllers\Admin;

use App\Constants\ConstRequestLoanStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectReasonRequest;
use App\Http\Requests\Borrower\RequestLoanRequest;
use App\Models\RequestLoan;
use App\Traits\LoanApproval;
use App\Traits\LoanReject;
use Illuminate\Http\Request;

class RequestLoanController extends Controller
{
    Use LoanApproval;
    Use LoanReject;

    // Request loan list
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', env('PAGINATION_PER_PAGE', 10));
        $search = $request->query('search');

        $requestLoan = RequestLoan::query()
            ->where('id', 'like', "%$search%")
            ->paginate($perPage);
        return $this->success($requestLoan);
    }

    // Request loan details by id
    public function show($id)
    {
        $requestLoan = RequestLoan::find($id);
        if ($requestLoan) {
            return $this->success($requestLoan);
        }
        return $this->failed('Request loan not found', 404);
    }
    // Request loan eligibility list
    public function eligibilityList(Request $request)
    {
        $perPage = $request->query('per_page', env('PAGINATION_PER_PAGE', 10));
        $search = $request->query('search');

        $requestLoan = RequestLoan::query()
            ->where('id', 'like', "%$search%")
            ->where('status', ConstRequestLoanStatus::ELIGIBLE)
            ->paginate($perPage);
        return $this->success($requestLoan);
    }
    // Request loan approve
    public function approve($id)
    {
            return $this->success($this->approveLoan($id));
    }
    // Request loan reject
    public function reject($id, RejectReasonRequest $request)
    {
        $reason = $request->input('reason');
        return $this->success($this->rejectLoan($id, $reason));
    }
}
