<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AI Job Board')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-almond text-jet antialiased flex flex-col">
    <nav class="bg-jet text-white sticky top-0 z-50 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ route('home') }}" class="flex items-center gap-2 font-bold text-lg text-almond">
                    <span class="w-2.5 h-2.5 rounded-sm bg-khaki inline-block"></span>
                    AI Job Board
                </a>

                <div class="hidden md:flex items-center gap-1">
                    @guest
                        <a href="{{ route('jobs.index') }}" class="px-3 py-2 rounded-md text-sm text-almond hover:bg-black/30">Jobs</a>
                        <a href="{{ route('login') }}" class="px-3 py-2 rounded-md text-sm text-almond hover:bg-black/30">Login</a>
                        <a href="{{ route('register.create') }}" class="ml-2 text-sm px-4 py-2 bg-khaki text-black rounded-md hover:bg-almond">Register</a>
                    @else
                        @if (Auth::user()->role === 'candidate')
                            <a href="{{ route('home') }}" class="px-3 py-2 rounded-md text-sm text-almond hover:bg-black/30">Home</a>
                            <a href="{{ route('jobs.index') }}" class="px-3 py-2 rounded-md text-sm text-almond hover:bg-black/30">Jobs</a>
                            <a href="{{ route('my-applications') }}" class="px-3 py-2 rounded-md text-sm text-almond hover:bg-black/30">My Applications</a>
                            <a href="{{ route('profile.show') }}" class="px-3 py-2 rounded-md text-sm text-almond hover:bg-black/30">My Profile</a>
                            <a href="{{ route('recommendations') }}" class="px-3 py-2 rounded-md text-sm text-almond hover:bg-black/30">AI Recommendations</a>
                            <a href="{{ route('assistant') }}" class="px-3 py-2 rounded-md text-sm text-almond hover:bg-black/30">AI Assistant</a>
                        @else
                            <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded-md text-sm text-almond hover:bg-black/30">Dashboard</a>
                            <a href="{{ route('admin.jobs') }}" class="px-3 py-2 rounded-md text-sm text-almond hover:bg-black/30">Jobs</a>
                            <a href="{{ route('admin.candidates') }}" class="px-3 py-2 rounded-md text-sm text-almond hover:bg-black/30">Candidates</a>
                            <a href="{{ route('admin.applications') }}" class="px-3 py-2 rounded-md text-sm text-almond hover:bg-black/30">Applications</a>
                            <a href="{{ route('assistant') }}" class="px-3 py-2 rounded-md text-sm text-almond hover:bg-black/30">AI Assistant</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="ml-2">
                            @csrf
                            <button type="submit" class="text-sm px-4 py-2 bg-black/40 text-almond rounded-md hover:bg-black/60">Logout</button>
                        </form>
                    @endguest
                </div>

                <button type="button" id="menu-toggle" class="md:hidden p-2 rounded-md text-almond hover:bg-black/30"
                    aria-label="Toggle menu">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <div id="mobile-menu" class="hidden md:hidden border-t border-black/20 bg-jet px-4 pt-2 pb-4 space-y-1">
            @guest
                <a href="{{ route('jobs.index') }}" class="block px-3 py-2 rounded-md text-sm text-almond hover:bg-black/30">Jobs</a>
                <a href="{{ route('login') }}" class="block px-3 py-2 rounded-md text-sm text-almond hover:bg-black/30">Login</a>
                <a href="{{ route('register.create') }}" class="block px-3 py-2 rounded-md text-sm bg-khaki text-black hover:bg-almond">Register</a>
            @else
                @if (Auth::user()->role === 'candidate')
                    <a href="{{ route('home') }}" class="block px-3 py-2 rounded-md text-sm text-almond hover:bg-black/30">Home</a>
                    <a href="{{ route('jobs.index') }}" class="block px-3 py-2 rounded-md text-sm text-almond hover:bg-black/30">Jobs</a>
                    <a href="{{ route('my-applications') }}" class="block px-3 py-2 rounded-md text-sm text-almond hover:bg-black/30">My Applications</a>
                    <a href="{{ route('profile.show') }}" class="block px-3 py-2 rounded-md text-sm text-almond hover:bg-black/30">My Profile</a>
                    <a href="{{ route('recommendations') }}" class="block px-3 py-2 rounded-md text-sm text-almond hover:bg-black/30">AI Recommendations</a>
                    <a href="{{ route('assistant') }}" class="block px-3 py-2 rounded-md text-sm text-almond hover:bg-black/30">AI Assistant</a>
                @else
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-md text-sm text-almond hover:bg-black/30">Dashboard</a>
                    <a href="{{ route('admin.jobs') }}" class="block px-3 py-2 rounded-md text-sm text-almond hover:bg-black/30">Jobs</a>
                    <a href="{{ route('admin.candidates') }}" class="block px-3 py-2 rounded-md text-sm text-almond hover:bg-black/30">Candidates</a>
                    <a href="{{ route('admin.applications') }}" class="block px-3 py-2 rounded-md text-sm text-almond hover:bg-black/30">Applications</a>
                    <a href="{{ route('assistant') }}" class="block px-3 py-2 rounded-md text-sm text-almond hover:bg-black/30">AI Assistant</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="mt-2 w-full text-sm px-4 py-2 bg-black/40 text-almond rounded-md hover:bg-black/60">Logout</button>
                </form>
            @endguest
        </div>
    </nav>

    <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('success'))
            <div class="mb-6 p-4 rounded-lg bg-green-100 text-green-800 border border-green-300 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 p-4 rounded-lg bg-red-100 text-red-800 border border-red-300 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="bg-jet text-almond border-t border-black/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-center text-sm text-almond/70">
            AI Job Board &copy; {{ date('Y') }} — Find your next opportunity.
        </div>
    </footer>

    <script>
        document.getElementById('menu-toggle').addEventListener('click', function () {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });
    </script>
</body>
</html>