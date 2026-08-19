<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobRequest;
use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminJobController extends Controller
{
    public function index(): View
    {
        $jobs = Job::withCount('applications')->latest()->get();

        return view('admin.jobs.index', ['jobs' => $jobs]);
    }

    public function create(): View
    {
        return view('admin.jobs.create', ['job' => new Job()]);
    }

    public function store(JobRequest $request): RedirectResponse
    {
        Job::create($request->validated());

        return redirect()->route('admin.jobs')->with('success', 'Job created successfully.');
    }

    public function edit(Job $job): View
    {
        return view('admin.jobs.edit', ['job' => $job]);
    }

    public function update(JobRequest $request, Job $job): RedirectResponse
    {
        $job->update($request->validated());

        return redirect()->route('admin.jobs')->with('success', 'Job updated successfully.');
    }

    public function destroy(Job $job): RedirectResponse
    {
        if ($job->applications()->exists()) {
            return back()->with('error', 'This job has applications and cannot be deleted.');
        }

        $job->delete();

        return redirect()->route('admin.jobs')->with('success', 'Job deleted successfully.');
    }
}
