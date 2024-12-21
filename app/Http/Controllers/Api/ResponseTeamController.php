<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ResponseTeamController extends Controller
{
    public function verifyQrCode(Request $request)
    {
        $qrId = $request->input('qr_code_id');
        \Log::info('Verifying QR Code:', ['qr_code_id' => $qrId]);
    
        // Look for the QR Code in the users table
        $user = User::where('qr_code_id', $qrId)->first();
        if ($user) {
            return response()->json([
                'status' => 'success',
                'message' => 'QR code verified.',
                'user' => $user
            ]);
        }
    
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid QR code.',
        ], 404);
    }
    
}
