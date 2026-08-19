<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobApplicationController extends Controller
{
    public function index(): View
    {
        $applications = JobApplication::with('job')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('applications.index', ['applications' => $applications]);
    }

    public function store(Request $request, Job $job): RedirectResponse
    {
        if ($job->application_deadline->endOfDay()->isPast()) {
            return back()->with('error', 'The application deadline for this job has passed.');
        }

        $exists = JobApplication::where('user_id', $request->user()->id)
            ->where('job_id', $job->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'You have already applied to this job.');
        }

        JobApplication::create([
            'user_id' => $request->user()->id,
            'job_id' => $job->id,
        ]);

        return back()->with('success', 'Application submitted successfully.');
    }

    public function destroy(Request $request, Job $job): RedirectResponse
    {
        JobApplication::where('user_id', $request->user()->id)
            ->where('job_id', $job->id)
            ->delete();

        return back()->with('success', 'Application cancelled.');
    }
}