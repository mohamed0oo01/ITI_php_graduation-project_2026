@extends('layouts.app')

@section('title', 'Candidates')

@section('content')
<h1 class="text-2xl font-bold mb-6 text-jet">All Candidates</h1>

@if ($candidates->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-khaki/40 p-8 text-center text-brown">
        No candidates registered yet.
    </div>
@else
    <div class="bg-white rounded-xl shadow-sm border border-khaki/40 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-khaki/30">
                <thead class="bg-almond">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-brown uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-brown uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-brown uppercase tracking-wider">Job Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-brown uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-brown uppercase tracking-wider">Skills</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-brown uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-khaki/30">
                    @foreach ($candidates as $candidate)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-jet">{{ $candidate->name }}</td>
                            <td class="px-6 py-4 text-sm text-jet/70">{{ $candidate->email }}</td>
                            <td class="px-6 py-4 text-sm text-jet/70">{{ $candidate->job_title ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-jet/70">{{ $candidate->phone ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-jet/70">{{ $candidate->skills ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-right">
                                <a href="{{ route('admin.candidates.show', $candidate) }}" class="text-brown hover:underline">View Profile</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection