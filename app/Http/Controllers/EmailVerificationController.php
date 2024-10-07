<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class EmailVerificationController extends Controller
{
    // Function to send verification code
    public function sendVerificationCode(Request $request)
    {
        // Validate the email address
        $request->validate([
            'email' => 'required|email|unique:users,email',
        ]);

        // Generate a unique 6-digit random verification code
        $verificationCode = rand(100000, 999999);

        // Store the verification code in the database (or cache)
        DB::table('email_verifications')->updateOrInsert(
            ['email' => $request->email],
            ['verification_code' => $verificationCode, 'created_at' => now()]
        );

        // Send the email with the verification code
        Mail::send('emails.verification_code', ['code' => $verificationCode], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Your Email Verification Code');
        });

        return response()->json(['message' => 'Verification code sent successfully.'], 200);
    }

    // Function to verify the code
    public function verifyCode(Request $request)
    {
        // Validate input
        $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required|digits:6',
        ]);

        // Check if the verification code matches
        $verification = DB::table('email_verifications')
            ->where('email', $request->email)
            ->where('verification_code', $request->verification_code)
            ->first();

        if (!$verification) {
            return response()->json(['message' => 'Invalid verification code.'], 400);
        }

        // Optionally mark the email as verified in your `users` table or another table

        return response()->json(['message' => 'Email verified successfully.'], 200);
    }
}
