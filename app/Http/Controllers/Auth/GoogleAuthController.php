<?php

namespace App\Http\Controllers\Auth;

use App\Constants\ConstUserRole;
use App\Models\Admin;
use App\Models\Borrower;
use App\Traits\BaseApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GoogleAuthController extends Controller
{
    use BaseApiResponse;
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $name = $googleUser->getName();
            $email = $googleUser->getEmail();
            $image = $googleUser->getAvatar();
            $password = Hash::make($googleUser->getId() . $googleUser->getEmail() . $googleUser->getName());

            $user = User::where('email', $email)->first();
            if (!$user) {
                // User doesn't exist, create a new one
                $request = [
                    'email' => $email,
                    'password' => $password,
                    'phone' => 0120000000,
                    'phone_verified_at' => now(), // Automatically verify the email
                ];
                $user = User::query()->create($request);

                Borrower::query()->create([
                    'user_id' => $user->id,
                    'first_name' => $name,
                    'last_name' => $name,
                    'gender' => null,
                    'dob' => null,
                    'address' => null,
                    'image' => $image,
                ]);
            }
            $token = $user->createToken('token_base_name')->plainTextToken;

            $profile = null;
            //check $user->role if admin or borrower so join the table
            if ($user->role == ConstUserRole::BORROWER) {
                $profile = Borrower::query()->where('user_id', $user->id)->first();
            }

            if ($user->role == ConstUserRole::ADMIN) {
                $profile = Admin::query()->where('user_id', $user->id)->first();
            }

            $user->profile = $profile;
            $user->role = (int) $user->role;
            $user->status = (int) $user->status;

            return $this->successLogin($user, $token , 'Login', 'Login successful');
        } catch (\Exception $e) {
            return $this->failed($e->getMessage(), 'Login failed', 'Login failed', 422);
        }
    }


    public function handleGoogleCode(Request $request)
    {
        $code = $request->code;
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $name = $googleUser->getName();
            $email = $googleUser->getEmail();
            $image = $googleUser->getAvatar();
            $password = Hash::make($googleUser->getId() . $googleUser->getEmail() . $googleUser->getName());

            $user = User::where('email', $email)->first();

            if (!$user) {
                // User doesn't exist, create a new one
                $request = [
                    'email' => $email,
                    'password' => $password,
                    'phone' => 0120000000,
                    'phone_verified_at' => now(), // Automatically verify the email
                ];
                $user = User::query()->create($request);

                Borrower::query()->create([
                    'user_id' => $user->id,
                    'first_name' => $name,
                    'last_name' => $name,
                    'gender' => null,
                    'dob' => null,
                    'address' => null,
                    'image' => $image,
                ]);
            } else {
                // Optionally, mark the email as verified if user exists
                if (is_null($user->phone_verified_at)) {
                    $user->phone_verified_at = now(); // Automatically verify the email
                    $user->save();
                }
            }

            $token = $user->createToken('token_base_name')->plainTextToken;

            $profile = null;
            //check $user->role if admin or borrower so join the table
            if ($user->role == ConstUserRole::BORROWER) {
                $profile = Borrower::query()->where('user_id', $user->id)->first();
            }

            if ($user->role == ConstUserRole::ADMIN) {
                $profile = Admin::query()->where('user_id', $user->id)->first();
            }

            $user->profile = $profile;
            $user->role = (int) $user->role;
            $user->status = (int) $user->status;

            return $this->successLogin($user, $token , 'Login', 'Login successful');

        } catch (\Exception $e) {
            return $this->failed($e->getMessage(), 'Error', 'Error form server');
        }
    }
}