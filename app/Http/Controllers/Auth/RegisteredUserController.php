<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        \Log::info('Incoming registration data:', $request->all());
    
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'municipality' => 'required|string|max:255',
            // For debugging purposes, let's temporarily relax these validations
            // 'qr_code_data' => 'required|string',
            // 'qr_code_id' => 'required|string',
        ]);
    
        \Log::info('Data after validation:', $request->all());
    
        // Create the user with the QR code data and ID
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'municipality' => $request->municipality,
            'qr_code_data' => $request->qr_code_data, // Save QR code data to the database
            'qr_code_id' => $request->qr_code_id,     // Save QR code ID to the database
        ]);
    
    
        event(new Registered($user));
        Auth::login($user);
    
        return redirect(route('dashboard'));
    }
    
}
