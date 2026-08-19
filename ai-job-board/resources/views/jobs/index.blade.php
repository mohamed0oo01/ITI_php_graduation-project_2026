@extends('layouts.app')

@section('title', 'Browse Jobs')

@section('content')
<h1 class="text-2xl font-bold mb-6 text-jet">Available Jobs</h1>

<form method="GET" action="{{ route('jobs.index') }}"
      class="bg-white p-4 rounded-xl shadow-sm border border-khaki/40 mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
    <div class="lg:col-span-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by job title..."
               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brown">
    </div>
    <select name="category" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brown">
        <option value="">All Categories</option>
        @foreach ($categories as $category)
            <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
        @endforeach
    </select>
    <select name="work_type" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brown">
        <option value="">All Work Types</option>
        @foreach ($workTypes as $workType)
            <option value="{{ $workType }}" @selected(request('work_type') === $workType)>{{ $workType }}</option>
        @endforeach
    </select>
    <select name="location" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brown">
        <option value="">All Locations</option>
        @foreach ($locations as $location)
            <option value="{{ $location }}" @selected(request('location') === $location)>{{ $location }}</option>
        @endforeach
    </select>
    <div class="sm:col-span-2 lg:col-span-5 flex gap-2">
        <button type="submit" class="px-4 py-2 bg-brown text-white rounded-md hover:bg-black transition-colors">Search</button>
        <a href="{{ route('jobs.index') }}" class="px-4 py-2 bg-khaki text-black rounded-md hover:bg-almond transition-colors">Reset</a>
    </div>
</form>

@if ($jobs->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-khaki/40 p-8 text-center text-brown">
        No jobs found.
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($jobs as $job)
            <div class="bg-white rounded-xl shadow-sm border border-khaki/40 p-6 flex flex-col hover:shadow-md transition">
                <div class="mb-2">
                    <span class="text-xs px-2 py-1 rounded-full bg-khaki text-black font-medium">{{ $job->category }}</span>
                    <span class="text-xs px-2 py-1 rounded-full bg-almond text-brown font-medium">{{ $job->work_type }}</span>
                </div>
                <h2 class="text-lg font-semibold text-jet mb-1">{{ $job->title }}</h2>
                <p class="text-sm text-jet/70 mb-1">Location: {{ $job->location }}</p>
                <p class="text-sm text-jet/70 mb-1">Salary: {{ number_format($job->salary, 0) }} EGP</p>
                <p class="text-sm text-jet/70 mb-4">Deadline: {{ $job->application_deadline->format('d M Y') }}</p>

                <div class="mt-auto">
                    <a href="{{ route('jobs.show', $job) }}"
                       class="inline-block px-4 py-2 bg-brown text-white rounded-md hover:bg-black transition-colors">
                        View Details
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $jobs->links() }}
    </div>
@endif
@endsection