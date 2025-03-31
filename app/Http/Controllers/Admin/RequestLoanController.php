<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Borrower\RequestLoanRequest;
use App\Models\RequestLoan;
use Illuminate\Http\Request;

class RequestLoanController extends Controller
{

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
}
