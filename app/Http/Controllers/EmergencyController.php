<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmergencyController extends Controller
{
    public function sendEmergencyEmail(Request $request)
    {
        $data = $request->all();
        $responseTeamEmail = 'ferrerkentandrew@gmail.com'; // Replace with actual email

        Mail::send('emails.emergency', $data, function ($message) use ($responseTeamEmail) {
            $message->to($responseTeamEmail)
                    ->subject('Emergency Alert');
        });

        return response()->json(['message' => 'Emergency email sent successfully!'], 200);
    }
}
