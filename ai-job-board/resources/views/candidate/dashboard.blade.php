@extends('layouts.app')

@section('title', 'Candidate Dashboard')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-khaki/40 p-6">
    <h1 class="text-2xl font-bold mb-2 text-jet">Candidate Dashboard</h1>
    <p class="text-jet/70">Welcome, {{ Auth::user()->name }}. This is your candidate area.</p>

    <div class="mt-6 flex gap-3">
        <a href="{{ route('profile.show') }}"
           class="px-4 py-2 bg-brown text-white rounded-md hover:bg-black transition-colors">My Profile</a>
    </div>

    <p class="mt-6 text-sm text-jet/50">Placeholder — will be replaced in upcoming tasks.</p>
</div>
@endsection