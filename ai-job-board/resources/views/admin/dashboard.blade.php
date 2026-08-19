@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<h1 class="text-2xl font-bold mb-6 text-jet">Admin Dashboard</h1>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-khaki/40 p-6 text-center">
        <p class="text-3xl font-bold text-brown">{{ $totalCandidates }}</p>
        <p class="mt-1 text-sm text-jet/70">Total Candidates</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-khaki/40 p-6 text-center">
        <p class="text-3xl font-bold text-brown">{{ $totalJobs }}</p>
        <p class="mt-1 text-sm text-jet/70">Total Jobs</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-khaki/40 p-6 text-center">
        <p class="text-3xl font-bold text-brown">{{ $totalApplications }}</p>
        <p class="mt-1 text-sm text-jet/70">Total Applications</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-khaki/40 p-6">
    <h2 class="text-lg font-semibold text-jet mb-3">Most Applied Job</h2>
    @if ($mostAppliedJob)
        <div class="flex items-center justify-between flex-wrap gap-2">
            <div>
                <p class="text-xl font-bold text-jet">{{ $mostAppliedJob->title }}</p>
                <p class="text-sm text-jet/70">{{ $mostAppliedJob->category }} · {{ $mostAppliedJob->location }}</p>
            </div>
            <span class="px-3 py-1 rounded-full bg-khaki text-black text-sm font-medium">
                {{ $mostAppliedJob->applications_count }} Applications
            </span>
        </div>
    @else
        <p class="text-sm text-brown">No jobs yet.</p>
    @endif
</div>

<div class="mt-6 flex gap-3 flex-wrap">
    <a href="{{ route('admin.jobs') }}" class="px-4 py-2 bg-brown text-white rounded-md hover:bg-black transition-colors">Manage Jobs</a>
    <a href="{{ route('admin.candidates') }}" class="px-4 py-2 bg-brown text-white rounded-md hover:bg-black transition-colors">View Candidates</a>
    <a href="{{ route('admin.applications') }}" class="px-4 py-2 bg-brown text-white rounded-md hover:bg-black transition-colors">View Applications</a>
</div>
@endsection