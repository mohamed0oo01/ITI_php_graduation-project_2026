<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobController extends Controller
{
    public function index(Request $request): View
    {
        $jobs = Job::query()
            ->filter(
                $request->query('search'),
                $request->query('category'),
                $request->query('work_type'),
                $request->query('location'),
            )
            ->latest()
            ->paginate(6)
            ->withQueryString();

        return view('jobs.index', [
            'jobs' => $jobs,
            'categories' => Job::distinct()->orderBy('category')->pluck('category'),
            'workTypes' => Job::distinct()->orderBy('work_type')->pluck('work_type'),
            'locations' => Job::distinct()->orderBy('location')->pluck('location'),
            'filters' => $request->only(['search', 'category', 'work_type', 'location']),
        ]);
    }

    public function show(Job $job): View
    {
        return view('jobs.show', [
            'job' => $job,
            'applied' => auth()->check()
                && $job->applications()->where('user_id', auth()->id())->exists(),
            'deadlinePassed' => $job->application_deadline->endOfDay()->isPast(),
        ]);
    }
}
