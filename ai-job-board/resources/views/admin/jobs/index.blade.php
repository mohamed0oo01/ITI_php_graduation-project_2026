@extends('layouts.app')

@section('title', 'Admin Jobs')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-jet">Jobs Management</h1>
    <a href="{{ route('admin.jobs.create') }}"
       class="px-4 py-2 bg-brown text-white rounded-md hover:bg-black transition-colors">+ Add Job</a>
</div>

@if ($jobs->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-khaki/40 p-8 text-center text-brown">
        No jobs yet. <a href="{{ route('admin.jobs.create') }}" class="text-brown hover:underline">Add your first job</a>.
    </div>
@else
    <div class="bg-white rounded-xl shadow-sm border border-khaki/40 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-khaki/30">
                <thead class="bg-almond">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-brown uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-brown uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-brown uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-brown uppercase tracking-wider">Work Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-brown uppercase tracking-wider">Deadline</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-brown uppercase tracking-wider">Applications</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-brown uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-khaki/30">
                    @foreach ($jobs as $job)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-jet">{{ $job->title }}</td>
                            <td class="px-6 py-4 text-sm text-jet/70">{{ $job->category }}</td>
                            <td class="px-6 py-4 text-sm text-jet/70">{{ $job->location }}</td>
                            <td class="px-6 py-4 text-sm text-jet/70">{{ $job->work_type }}</td>
                            <td class="px-6 py-4 text-sm text-jet/70">{{ $job->application_deadline->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-sm text-jet/70">{{ $job->applications_count }}</td>
                            <td class="px-6 py-4 text-sm text-right space-x-3 whitespace-nowrap">
                                <a href="{{ route('admin.jobs.edit', $job) }}" class="text-brown hover:underline">Edit</a>
                                @if ($job->applications_count > 0)
                                    <span class="text-gray-400 cursor-not-allowed" title="Cannot delete: has {{ $job->applications_count }} application(s)">
                                        Delete
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('admin.jobs.destroy', $job) }}" class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this job?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                    </form>
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