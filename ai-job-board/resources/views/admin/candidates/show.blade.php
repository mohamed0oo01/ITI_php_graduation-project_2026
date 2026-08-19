@extends('layouts.app')

@section('title', $candidate->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('admin.candidates') }}" class="text-brown hover:underline">← Back to candidates</a>

    <div class="bg-white rounded-xl shadow-sm border border-khaki/40 overflow-hidden mt-4">
        <div class="flex items-center gap-6 p-6 border-b border-khaki/40">
            @if ($candidate->profile_image)
                <img src="{{ asset('storage/' . $candidate->profile_image) }}"
                     alt="{{ $candidate->name }}" class="w-24 h-24 rounded-full object-cover">
            @else
                <div class="w-24 h-24 rounded-full bg-khaki flex items-center justify-center text-jet text-4xl font-bold">
                    {{ strtoupper(substr($candidate->name, 0, 1)) }}
                </div>
            @endif
            <div>
                <h2 class="text-xl font-semibold text-jet">{{ $candidate->name }}</h2>
                <p class="text-brown">{{ $candidate->job_title ?? 'No job title set' }}</p>
            </div>
        </div>

        <dl class="divide-y divide-khaki/30">
            <div class="px-6 py-4 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-brown">Age</dt>
                <dd class="col-span-2 text-jet">{{ $candidate->age ?? '—' }}</dd>
            </div>
            <div class="px-6 py-4 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-brown">Email</dt>
                <dd class="col-span-2 text-jet">{{ $candidate->email }}</dd>
            </div>
            <div class="px-6 py-4 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-brown">Phone</dt>
                <dd class="col-span-2 text-jet">{{ $candidate->phone ?? '—' }}</dd>
            </div>
            <div class="px-6 py-4 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-brown">Profile Description</dt>
                <dd class="col-span-2 text-jet">{{ $candidate->profile_description ?? '—' }}</dd>
            </div>
            <div class="px-6 py-4 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-brown">Skills</dt>
                <dd class="col-span-2">
                    @if ($candidate->skills)
                        @foreach (explode(',', $candidate->skills) as $skill)
                            <span class="inline-block bg-khaki text-black text-sm px-2 py-1 rounded-full mr-1 mb-1">
                                {{ trim($skill) }}
                            </span>
                        @endforeach
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div class="px-6 py-4 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-brown">Resume</dt>
                <dd class="col-span-2">
                    @if ($candidate->resume)
                        <a href="{{ route('admin.candidates.resume', $candidate) }}" class="text-brown hover:underline">Download CV</a>
                    @else
                        —
                    @endif
                </dd>
            </div>
        </dl>
    </div>
</div>
@endsection