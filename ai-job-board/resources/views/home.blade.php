@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <section class="relative overflow-hidden rounded-2xl bg-jet text-almond px-6 py-16 sm:px-12 mb-12">
        <div class="max-w-3xl">
            <span class="inline-block px-3 py-1 rounded-full bg-khaki text-black text-xs font-semibold tracking-wide mb-4">
                AI-POWERED MATCHING
            </span>
            <h1 class="text-3xl sm:text-5xl font-extrabold leading-tight mb-4">
                Find Your Next Opportunity
            </h1>
            <p class="text-almond/80 text-lg mb-8">
                Browse curated jobs and let smart matching help you stand out as the perfect candidate.
            </p>

            <form action="{{ route('jobs.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Job title, skills, description..."
                       class="flex-1 px-4 py-3 rounded-lg bg-white text-jet border-2 border-transparent focus:border-khaki outline-none placeholder:text-gray-400 text-sm">
                <button type="submit"
                        class="px-8 py-3 rounded-lg bg-brown text-white font-semibold text-sm hover:bg-black transition-colors">
                    Search Jobs
                </button>
            </form>
        </div>
    </section>

    @if ($featuredJobs->isNotEmpty())
        <section class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-jet">Featured Jobs</h2>
                <a href="{{ route('jobs.index') }}" class="text-sm font-medium text-brown hover:text-black">View all &rarr;</a>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                @foreach ($featuredJobs as $job)
                    <a href="{{ route('jobs.show', $job) }}" class="group bg-white rounded-xl shadow-sm border border-khaki/40 hover:shadow-lg hover:border-brown/40 transition overflow-hidden flex flex-col">
                        <div class="px-6 py-4 bg-jet text-white flex justify-between items-center">
                            <h3 class="font-bold text-almond group-hover:text-white">{{ $job->title }}</h3>
                            <span class="text-xs text-almond/70">{{ $job->location }}</span>
                        </div>
                        <div class="px-6 py-5 flex flex-col gap-2 flex-1">
                            <div class="text-brown text-sm">&#9679; {{ $job->category }} &middot; {{ $job->work_type }}</div>
                            <p class="text-sm text-jet/70 line-clamp-3">{{ Str::limit($job->description, 120) }}</p>
                            <div class="flex flex-wrap gap-2 mt-auto pt-3">
                                @if ($job->work_type)
                                    <span class="px-2.5 py-1 rounded-full bg-khaki text-black text-xs font-medium">{{ $job->work_type }}</span>
                                @endif
                                <span class="px-2.5 py-1 rounded-full bg-almond text-brown text-xs font-medium">
                                    Closes {{ $job->application_deadline->format('d M Y') }}
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @else
        <section class="bg-white rounded-xl shadow-sm border border-khaki/40 p-10 text-center text-brown">
            No featured jobs available right now.
        </section>
    @endif
@endsection