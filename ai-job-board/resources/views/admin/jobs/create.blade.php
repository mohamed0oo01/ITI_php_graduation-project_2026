@extends('layouts.app')

@section('title', 'Add Job')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.jobs') }}" class="text-brown hover:underline">← Back to jobs</a>
        <h1 class="text-2xl font-bold text-jet">Add Job</h1>
    </div>

    <form method="POST" action="{{ route('admin.jobs.store') }}" class="bg-white p-6 rounded-xl shadow border border-khaki/40">
        @csrf
        @include('admin.jobs._form')
        <button type="submit" class="px-4 py-2 bg-brown text-white rounded-md hover:bg-black transition-colors">Create Job</button>
    </form>
</div>
@endsection