<?php

namespace App\Http\Controllers\Auth;

use App\Constants\ConstUserRole;
use App\Constants\ConstUserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyCodeRequest;
use App\Models\Admin;
use App\Models\Borrower;
use App\Models\CreditScore;
use App\Models\User;
use App\Traits\BaseApiResponse;
use App\Traits\OTP;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    use BaseApiResponse;
    use OTP;


    /**
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function Register(RegisterRequest $request)
    {
        // Check if the user has already registered recently
        $existingUser = User::query()->where('email', $request->input('email'))->first();
        if ($existingUser) {
            return $this->failed(null, 'Fail', 'User already exists', 409);
        }

        DB::beginTransaction(); //protect the database from any error if error occurs it will rollback

        try {
            // Create the user
            $user = User::query()->create([
                'email' => $request->input('email'),
                'password' => bcrypt($request->input('password'),),
                'phone' => $request->input('phone'),
                'role' => ConstUserRole::BORROWER,
                'status' => ConstUserStatus::ACTIVE
                ]);

            $image = $request->file('image');
            $imagePath = null;
            if ($image) {
                $imagePath = $this->uploadImage($image, 'borrower', 'public');
            }

            Borrower::query()->create([
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'gender' => $request->input('gender'),
                'dob' => $request->input('dob'),
                'address' => $request->input('address'),
                'image' => $imagePath,
                'user_id' => $user->id,
            ]);

            // Generate a token for the user
            $token = $user->createToken('token_base_name')->plainTextToken;

            //give the user a default credit score
            CreditScore::query()->create([
                'user_id' => $user->id,
                'score' => 50,
                'status' => 1,
            ]);

            // Store registration time in session
            session(['registered_time' => now()]);

            // Commit the transaction
            DB::commit();

            // Prepare user and token response
            $response = ['user' => $user, 'token' => $token,];

            return $this->success($response, 'Registration', 'Registration successful', 201);
        } catch (Exception $exception) {
            // Rollback the transaction in case of error
            DB::rollBack();

            return $this->failed($exception->getMessage(), 'Error', 'Error from server');
        }
    }

    /**
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request)
    {

        $user = User::query()->where('email', $request->input('email'))->first();

        if (!$user || !password_verify($request->input('password'), $user->password)) {
            return $this->failed(null, 'Fail', 'Invalid credentials', 401);
        }

        $profile = null;
        //check $user->role if admin or borrower so join the table
        if ($user->role == ConstUserRole::BORROWER) {
            $profile = Borrower::query()->where('user_id', $user->id)->first();
        }

        if ($user->role == ConstUserRole::ADMIN) {
            $profile = Admin::query()->where('user_id', $user->id)->first();
        }

        $token = $user->createToken('token_base_name')->plainTextToken;

        //add $profile to user
        $user->profile = $profile;
        $user->role = (int) $user->role;
        $user->status = (int) $user->status;

        return $this->successLogin($user, $token, 'Login', 'Login successful');
    }
    //get me function
    public function me()
    {
        $user = auth()->user();
        return $this->success($user, 'User', 'User data retrieved successfully');
    }
    //send OTP with auth user
    public function sendVerify()
    {
        $user = auth()->user();
        $data = $this->sendOTP($user);
        //send OTP to user email
        return $this->success($data, 'OTP', 'OTP sent successfully');
    }
    //verify OTP with auth user
    public function verifyOTP(VerifyCodeRequest $request)
    {
        $user = auth()->user();
        $data = $this->verifyOtpCode($user, $request->input('code'));
        return $this->success($data, 'OTP', 'OTP verified successfully');
    }

}
