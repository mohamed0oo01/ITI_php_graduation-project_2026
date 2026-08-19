@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('profile.show') }}" class="text-brown hover:underline">← Back to profile</a>
        <h1 class="text-2xl font-bold text-jet">Edit Profile</h1>
    </div>

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data"
          class="bg-white p-6 rounded-xl shadow border border-khaki/40">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-brown mb-1">Full Name</label>
            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brown">
            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label for="age" class="block text-sm font-medium text-brown mb-1">Age</label>
            <input type="number" name="age" id="age" value="{{ old('age', $user->age) }}" min="18" max="100"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brown">
            @error('age')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label for="job_title" class="block text-sm font-medium text-brown mb-1">Job Title</label>
            <input type="text" name="job_title" id="job_title" value="{{ old('job_title', $user->job_title) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brown">
            @error('job_title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label for="profile_description" class="block text-sm font-medium text-brown mb-1">Profile Description</label>
            <textarea name="profile_description" id="profile_description" rows="4"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brown">{{ old('profile_description', $user->profile_description) }}</textarea>
            @error('profile_description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label for="phone" class="block text-sm font-medium text-brown mb-1">Phone Number</label>
            <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brown">
            @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label for="skills" class="block text-sm font-medium text-brown mb-1">Skills</label>
            <input type="text" name="skills" id="skills" value="{{ old('skills', $user->skills) }}"
                   placeholder="e.g. PHP, Laravel, MySQL"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brown">
            @error('skills')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label for="profile_image" class="block text-sm font-medium text-brown mb-1">Profile Image</label>
            <input type="file" name="profile_image" id="profile_image" accept="image/*"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md">
            @if ($user->profile_image)
                <p class="mt-1 text-sm text-gray-500">Current: <a href="{{ asset('storage/' . $user->profile_image) }}" target="_blank" class="text-brown">view image</a></p>
            @endif
            @error('profile_image')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="mb-6">
            <label for="resume" class="block text-sm font-medium text-brown mb-1">Resume (PDF only)</label>
            <input type="file" name="resume" id="resume" accept="application/pdf"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md">
            @if ($user->resume)
                <p class="mt-1 text-sm text-gray-500">Current: <a href="{{ route('profile.resume') }}" class="text-brown hover:underline">download</a></p>
            @endif
            @error('resume')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <button type="submit"
                class="px-4 py-2 bg-brown text-white rounded-md hover:bg-black transition-colors">
            Save Changes
        </button>
    </form>
</div>
@endsection