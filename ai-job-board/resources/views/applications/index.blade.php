@extends('layouts.app')

@section('title', 'My Applications')

@section('content')
<h1 class="text-2xl font-bold mb-6 text-jet">My Applications</h1>

@if ($applications->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-khaki/40 p-8 text-center text-brown">
        You have not applied to any jobs yet.
        <a href="{{ route('jobs.index') }}" class="text-brown hover:underline">Browse jobs</a>.
    </div>
@else
    <div class="bg-white rounded-xl shadow-sm border border-khaki/40 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-khaki/30">
                <thead class="bg-almond">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-brown uppercase tracking-wider">Job</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-brown uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-brown uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-brown uppercase tracking-wider">Applied At</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-brown uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-brown uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-khaki/30">
                    @foreach ($applications as $application)
                        @php
                            $closed = $application->job->application_deadline->endOfDay()->isPast();
                        @endphp
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-jet">{{ $application->job->title }}</td>
                            <td class="px-6 py-4 text-sm text-jet/70">{{ $application->job->category }}</td>
                            <td class="px-6 py-4 text-sm text-jet/70">{{ $application->job->location }}</td>
                            <td class="px-6 py-4 text-sm text-jet/70">{{ $application->created_at->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if ($closed)
                                    <span class="px-2 py-1 rounded-full bg-khaki text-black text-xs font-medium">Closed</span>
                                @else
                                    <span class="px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs font-medium">Active</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('jobs.show', $application->job) }}"
                                   class="text-brown hover:underline">View Job</a>
                                <form method="POST" action="{{ route('job.cancel', $application->job) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Cancel</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection