<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect($service, Request $request){
        return response()->json([
            'redirectUrl' => Socialite::driver($service)->stateless()->redirect()->getTargetUrl()
        ]);
    }

    public function callback($service, Request $request){

        $serviceUser = Socialite::driver($service)->stateless()->user();

        return response()->json([
            'user' => $serviceUser
        ]);
    }
}