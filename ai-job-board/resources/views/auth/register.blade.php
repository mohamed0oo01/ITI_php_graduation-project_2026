@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="max-w-md mx-auto mt-10">
    <h1 class="text-2xl font-bold text-center mb-6 text-jet">Create an Account</h1>

    <form method="POST" action="{{ route('register.store') }}" class="bg-white p-6 rounded-xl shadow border border-khaki/40">
        @csrf

        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-brown mb-1">Full Name</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brown">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-brown mb-1">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brown">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-brown mb-1">Password</label>
            <input type="password" name="password" id="password" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brown">
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label for="password_confirmation" class="block text-sm font-medium text-brown mb-1">Confirm Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brown">
        </div>

        <button type="submit"
                class="w-full py-2 px-4 bg-brown text-white rounded-md hover:bg-black transition-colors">
            Register
        </button>
    </form>

    <p class="mt-4 text-center text-sm text-gray-600">
        Already have an account?
        <a href="{{ route('login') }}" class="text-brown hover:underline">Login</a>
    </p>
</div>
@endsection