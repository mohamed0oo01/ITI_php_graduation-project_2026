<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $totalCandidates = User::where('role', 'candidate')->count();
        $totalJobs = Job::count();
        $totalApplications = JobApplication::count();

        $mostAppliedJob = Job::withCount('applications')
            ->orderByDesc('applications_count')
            ->first();

        return view('admin.dashboard', compact(
            'totalCandidates',
            'totalJobs',
            'totalApplications',
            'mostAppliedJob',
        ));
    }
}
