@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-jet">My Profile</h1>
        <a href="{{ route('profile.edit') }}"
           class="px-4 py-2 bg-brown text-white rounded-md hover:bg-black transition-colors">Edit Profile</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-khaki/40 overflow-hidden">
        <div class="flex items-center gap-6 p-6 border-b border-khaki/40">
            @if ($user->profile_image)
                <img src="{{ asset('storage/' . $user->profile_image) }}"
                     alt="{{ $user->name }}" class="w-24 h-24 rounded-full object-cover">
            @else
                <div class="w-24 h-24 rounded-full bg-khaki flex items-center justify-center text-jet text-4xl font-bold">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif
            <div>
                <h2 class="text-xl font-semibold text-jet">{{ $user->name }}</h2>
                <p class="text-brown">{{ $user->job_title ?? 'No job title set' }}</p>
            </div>
        </div>

        <dl class="divide-y divide-khaki/30">
            <div class="px-6 py-4 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-brown">Age</dt>
                <dd class="col-span-2 text-jet">{{ $user->age ?? '—' }}</dd>
            </div>
            <div class="px-6 py-4 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-brown">Email</dt>
                <dd class="col-span-2 text-jet">{{ $user->email }}</dd>
            </div>
            <div class="px-6 py-4 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-brown">Phone</dt>
                <dd class="col-span-2 text-jet">{{ $user->phone ?? '—' }}</dd>
            </div>
            <div class="px-6 py-4 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-brown">Profile Description</dt>
                <dd class="col-span-2 text-jet">{{ $user->profile_description ?? '—' }}</dd>
            </div>
            <div class="px-6 py-4 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-brown">Skills</dt>
                <dd class="col-span-2">
                    @if ($user->skills)
                        @foreach (explode(',', $user->skills) as $skill)
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
                    @if ($user->resume)
                        <a href="{{ route('profile.resume') }}" class="text-brown hover:underline">Download Resume</a>
                    @else
                        —
                    @endif
                </dd>
            </div>
        </dl>
    </div>
</div>
@endsection