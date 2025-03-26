<?php
namespace App\Traits;

use App\Models\PhoneOtp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

trait OTP{
    public function sendOTP($user)
    {
        $otp = rand(1000, 9999);
        $expires_at = now()->addMinutes(5);
        $phone = ltrim($user->phone, '0');
        PhoneOtp::create([
            'phone' => $phone,
            'otp' => $otp,
            'expires_at' => $expires_at
    ]);
        $plasgateApiUrl = "https://cloudapi.plasgate.com/api/send";
        $plasgateUsername = env('OTP_USERNAME');
        $plasgatePassword = env('OTP_PASSWORD');
        $sender = env('OTP_SENDER', 'SMS Info'); // Sender name
        $content = "Your OTP code is: $otp";
        $response = Http::asForm()->post($plasgateApiUrl, [
            'username' => $plasgateUsername,
            'password' => $plasgatePassword,
            'sender' => $sender,
            'to' => "855$phone",
            'content' => $content
        ]);
        return $response->json();
    }
    public function verifyOtpCode($user, $code)
    {
        $now = Carbon::now(); // Get the current time
        $phone = ltrim($user->phone, '0');
        $otp = PhoneOTP::where('phone', $phone)
            ->where('otp', $code)
            ->where('expires_at', '>=', $now)
            ->first();

        // Check if the OTP code is valid
        if ($otp) {
            $otp->delete();
            User::where('id', $user->id)->update(['phone_verified_at' => $now]);
            return response()->json(['message' => 'OTP verified successfully.']);
        } else {
            return response()->json(['message' => 'Invalid OTP code.'], Response::HTTP_BAD_REQUEST);
        }
    }

}
