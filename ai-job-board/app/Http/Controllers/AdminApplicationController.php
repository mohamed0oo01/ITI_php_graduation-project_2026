<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use Illuminate\View\View;

class AdminApplicationController extends Controller
{
    public function index(): View
    {
        $applications = JobApplication::with(['user', 'job'])
            ->latest()
            ->get();

        return view('admin.applications.index', ['applications' => $applications]);
    }
}
