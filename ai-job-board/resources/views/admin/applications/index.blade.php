@extends('layouts.app')

@section('title', 'Applications')

@section('content')
<h1 class="text-2xl font-bold mb-6 text-jet">All Job Applications</h1>

@if ($applications->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-khaki/40 p-8 text-center text-brown">
        No applications submitted yet.
    </div>
@else
    <div class="bg-white rounded-xl shadow-sm border border-khaki/40 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-khaki/30">
                <thead class="bg-almond">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-brown uppercase tracking-wider">Candidate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-brown uppercase tracking-wider">Job</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-brown uppercase tracking-wider">Applied Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-brown uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-khaki/30">
                    @foreach ($applications as $application)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-jet">{{ $application->user->name }}</td>
                            <td class="px-6 py-4 text-sm text-jet/70">{{ $application->job->title }}</td>
                            <td class="px-6 py-4 text-sm text-jet/70">{{ $application->created_at->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-sm text-right space-x-3 whitespace-nowrap">
                                <a href="{{ route('admin.candidates.show', $application->user) }}" class="text-brown hover:underline">View Candidate</a>
                                <a href="{{ route('jobs.show', $application->job) }}" class="text-brown hover:underline">View Job</a>
                                @if ($application->user->resume)
                                    <a href="{{ route('admin.candidates.resume', $application->user) }}" class="text-brown hover:underline">Download CV</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection