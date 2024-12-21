<?php

namespace App\Http\Controllers;

use App\Models\ResponseTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Register function remains unchanged
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:responseteam,email',
            'department' => 'required|string',
            'password' => 'required|string|min:8',
            'qr_code_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 422);
        }

        $responseTeam = ResponseTeam::create([
            'email' => $request->input('email'),
            'department' => $request->input('department'),
            'password' => $request->input('password'), // Store plaintext
            'qr_code_id' => $request->input('qr_code_id'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Response team member registered successfully.',
            'user' => $responseTeam,
        ], 201);
    }

    // Login function remains unchanged
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = ResponseTeam::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email not found',
            ], 404);
        }

        // Check if password matches
        if ($user->password !== $request->password) {
            return response()->json([
                'status' => 'error',
                'message' => 'Password does not match',
            ], 401);
        }

        // Successful login
        $token = base64_encode($user->email); // Generate a basic token for demonstration
        return response()->json([
            'status' => 'success',
            'message' => 'User logged in successfully',
            'user' => $user,
            'token' => $token,
        ]);
    }

    // Updated logout function
    public function logout(Request $request)
    {
        // Ensure the authorization token is present
        $token = $request->header('Authorization');
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        // Clear client-side authentication data (token)
        return response()->json([
            'status' => 'success',
            'message' => 'User logged out successfully',
        ]);
    }
}
