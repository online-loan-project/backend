<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreditScoreRequest;
use App\Models\CreditScore;
use App\Models\User;
use Illuminate\Http\Request;

class CreditScoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // show list user with credit score
        $user = CreditScore::query()->with('user')->get();
        if ($user->isEmpty()) {
            return $this->failed('Users not found', 404);
        }

        if(!$user){
            return $this->failed('User not found', 404);
        }
        return $this->success($user);
    }
    //reset credit score by user id
    public function resetCreditScore($id)
    {
        $creditScore = CreditScore::query()->where('user_id', $id)->first();
        if ($creditScore) {
            $creditScore->score = 0;
            $creditScore->save(); //save the updated credit score
            return $this->success($creditScore);
        }
        return $this->failed('Credit score not found', 404);
    }
    //update credit score by user id
    public function updateCreditScore(CreditScoreRequest $request, $id)
    {
        $creditScore = CreditScore::query()->where('user_id', $id)->first();
        if ($creditScore) {
            $creditScore->score = $request->score;
            $creditScore->status = $request->status;
            $creditScore->save(); //save the updated credit score
            return $this->success($creditScore);
        }
        return $this->failed('Credit score not found', 404);
    }

}
