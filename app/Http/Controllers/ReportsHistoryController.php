<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportsHistoryController extends Controller
{
    public function index()
    {
        // Fetch the report history data from the database
        $reports = []; // Placeholder: Replace with actual data fetching logic

        return inertia('ReportsHistory', [
            'reports' => $reports,
        ]);
    }
}

