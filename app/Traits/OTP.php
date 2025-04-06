<?php

namespace App\Traits;

use App\Models\PhoneOtp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

trait OTP
{
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

        $method = env('OTP_METHOD', 'telegram');
        $chat_id = env('OTP_TELEGRAM_CHAT_ID', '123456789'); // Replace with your chat ID

        if ($method == 'telegram') {
            $this->sendTelegramOtp($chat_id, $otp);
        } elseif ($method == 'plasgate') {
            $this->sendPlasgateOtp($phone, $otp);
        } else {
            return response()->json(['message' => 'Invalid OTP method.'], Response::HTTP_BAD_REQUEST);
        }
    }

    public function sendPlasgateOtp($phone, $otp)
    {
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
    //send otp to telegram
    public function sendTelegramOtp($chat_id, $otp)
    {
        $telegramApiUrl = "https://api.telegram.org/bot" . env('OTP_TELEGRAM_BOT_TOKEN') . "/sendMessage";
        $content = "Your OTP code is: $otp";
        $response = Http::post($telegramApiUrl, [
            'chat_id' => $chat_id,
            'text' => $content
        ]);
        return $response->json();
    }

    //plasgate api send otp

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
