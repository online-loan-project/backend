<?php

namespace App\Http\Controllers\Borrower;

use App\Http\Controllers\Controller;
use App\Models\CreditScore;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //credit score
    public function creditScore(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return $this->failed('User not found', 404);
        }
        //get credit score by user id
        $creditScore = CreditScore::query()
            ->where('user_id', $user->id)
            ->first();
        return $this->success($creditScore);
    }
}
