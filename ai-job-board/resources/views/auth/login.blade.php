@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="max-w-md mx-auto mt-10">
    <h1 class="text-2xl font-bold text-center mb-6 text-jet">Login</h1>

    <form method="POST" action="{{ route('login.store') }}" class="bg-white p-6 rounded-xl shadow border border-khaki/40">
        @csrf

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-brown mb-1">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
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

        <div class="mb-4 flex items-center">
            <input type="checkbox" name="remember" id="remember" class="h-4 w-4 text-brown border-gray-300 rounded">
            <label for="remember" class="ml-2 text-sm text-gray-600">Remember me</label>
        </div>

        <button type="submit"
                class="w-full py-2 px-4 bg-brown text-white rounded-md hover:bg-black transition-colors">
            Login
        </button>
    </form>

    <p class="mt-4 text-center text-sm text-gray-600">
        Don't have an account?
        <a href="{{ route('register.create') }}" class="text-brown hover:underline">Register</a>
    </p>
</div>
@endsection