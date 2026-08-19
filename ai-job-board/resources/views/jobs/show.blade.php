@extends('layouts.app')

@section('title', $job->title)

@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('jobs.index') }}" class="text-brown hover:underline">← Back to jobs</a>

    <div class="bg-white rounded-xl shadow-sm border border-khaki/40 p-6 mt-4">
        <div class="mb-2">
            <span class="text-xs px-2 py-1 rounded-full bg-khaki text-black font-medium">{{ $job->category }}</span>
            <span class="text-xs px-2 py-1 rounded-full bg-almond text-brown font-medium">{{ $job->work_type }}</span>
        </div>

        <h1 class="text-2xl font-bold text-jet mb-2">{{ $job->title }}</h1>

        <dl class="mt-4 space-y-2">
            <div class="grid grid-cols-3 gap-4 py-2 border-t border-khaki/30">
                <dt class="text-sm font-medium text-brown">Category</dt>
                <dd class="col-span-2 text-jet">{{ $job->category }}</dd>
            </div>
            <div class="grid grid-cols-3 gap-4 py-2 border-t border-khaki/30">
                <dt class="text-sm font-medium text-brown">Location</dt>
                <dd class="col-span-2 text-jet">{{ $job->location }}</dd>
            </div>
            <div class="grid grid-cols-3 gap-4 py-2 border-t border-khaki/30">
                <dt class="text-sm font-medium text-brown">Work Type</dt>
                <dd class="col-span-2 text-jet">{{ $job->work_type }}</dd>
            </div>
            <div class="grid grid-cols-3 gap-4 py-2 border-t border-khaki/30">
                <dt class="text-sm font-medium text-brown">Salary</dt>
                <dd class="col-span-2 text-jet">{{ number_format($job->salary, 0) }} EGP</dd>
            </div>
            <div class="grid grid-cols-3 gap-4 py-2 border-t border-khaki/30">
                <dt class="text-sm font-medium text-brown">Application Deadline</dt>
                <dd class="col-span-2 text-jet">{{ $job->application_deadline->format('d M Y') }}</dd>
            </div>
            <div class="grid grid-cols-3 gap-4 py-2 border-t border-khaki/30">
                <dt class="text-sm font-medium text-brown">Description</dt>
                <dd class="col-span-2 text-jet">{{ $job->description }}</dd>
            </div>
            <div class="grid grid-cols-3 gap-4 py-2 border-t border-khaki/30">
                <dt class="text-sm font-medium text-brown">Required Skills</dt>
                <dd class="col-span-2">
                    @foreach (explode(',', $job->required_skills) as $skill)
                        <span class="inline-block bg-khaki text-black text-sm px-2 py-1 rounded-full mr-1 mb-1">
                            {{ trim($skill) }}
                        </span>
                    @endforeach
                </dd>
            </div>
        </dl>

        <div class="mt-6 pt-6 border-t border-khaki/30">
            @auth
                @if (Auth::user()->role === 'candidate')
                    @if ($deadlinePassed)
                        <span class="px-4 py-2 bg-khaki text-black rounded-md font-medium">Deadline Passed</span>
                    @elseif ($applied)
                        <div class="flex items-center gap-3 flex-wrap">
                            <span class="px-4 py-2 bg-green-100 text-green-800 rounded-md font-medium">Already Applied</span>
                            <form method="POST" action="{{ route('job.cancel', $job) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                                    Cancel Application
                                </button>
                            </form>
                        </div>
                    @else
                        <form method="POST" action="{{ route('job.apply', $job) }}">
                            @csrf
                            <button type="submit"
                                    class="px-6 py-2 bg-brown text-white rounded-md hover:bg-black transition-colors">
                                Apply Now
                            </button>
                        </form>
                    @endif
                @else
                    <p class="text-sm text-jet/60">Only candidates can apply for jobs.</p>
                @endif
            @else
                <a href="{{ route('login') }}"
                   class="inline-block px-6 py-2 bg-brown text-white rounded-md hover:bg-black transition-colors">
                    Login to Apply
                </a>
            @endauth
        </div>
    </div>
</div>
@endsection