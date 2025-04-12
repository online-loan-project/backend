<?php

namespace App\Http\Controllers\Admin;

use App\Constants\ConstLoanStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // dashboard
    public function index(Request $request)
    {
       //get total user
         $totalUser = \App\Models\User::query()
            ->where('role', '!=', 1)
            ->count();
        //get total revenue per month
        $totalRevenue = \App\Models\Loan::where('status', '!=', ConstLoanStatus::UNPAID)
            ->whereMonth('created_at', date('m'))
            ->sum('revenue');
        $data = [
            'total_user' => $totalUser,
            'total_revenue' => $totalRevenue,
        ];
        return $this->success($data, 'Dashboard data retrieved successfully.');
    }
}
