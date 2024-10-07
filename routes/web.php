<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    // Define the coordinates for municipalities
    $municipalities = [
        'Nagcarlan' => ['latitude' => 14.1263, 'longitude' => 121.4208],
        'San Pablo City' => ['latitude' => 14.0667, 'longitude' => 121.3236],
        // Add more municipalities here with their coordinates
    ];

    $userMunicipality = $user->municipality;
    $coordinates = $municipalities[$userMunicipality] ?? ['latitude' => 14.1263, 'longitude' => 121.4208];

    return Inertia::render('Dashboard', [
        'userMunicipality' => $userMunicipality,
        'coordinates' => $coordinates,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
