<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Borrower;
use App\Models\User;
use App\Traits\BaseApiResponse;
use Illuminate\Http\Request;

class BorrowerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use BaseApiResponse;
    public function index( Request $request)
    {
        $perPage = $request->query('per_page', env('PAGINATION_PER_PAGE', 10));
        $search = $request->query('search');

        $borrower = Borrower::query()
            ->where('user_id', 'like', "%$search%")
            ->paginate($perPage);
        return $this->success($borrower);


    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //show borrower details
        $borrower = Borrower::find($id);
        if ($borrower) {
            return $this->success($borrower);
        }
        return $this->failed('Borrower not found', 404);
    }

   //update status
    public function borrowerStatus(Request $request, string $id)
    {
        $borrower = Borrower::find($id);
        if ($borrower) {
           $borrowerUser = User::query()->where('id', $borrower->user_id)->first();
           $borrowerUser->status = (int)$request->status;
           $borrowerUser->save(); //save the updated status
            return $this->success($borrowerUser);
        }
        return $this->failed('Borrower not found', 404);
    }

}
