@extends('layouts.app')

@section('title', 'AI Job Recommendations')

@section('content')
@php
    $hasProfile = filled($user->job_title) || filled($user->skills) || filled($user->profile_description);
@endphp

<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-jet tracking-tight">AI Job Recommendations</h1>
        <p class="mt-1 text-sm text-brown">Powered by Google Gemini — your profile is analyzed against all available jobs.</p>
    </div>

    <div class="grid gap-5 md:grid-cols-3">
        <div class="md:col-span-1 bg-white rounded-2xl shadow border border-khaki/40 p-5 h-fit">
            <p class="text-xs uppercase tracking-wider text-brown/70 font-semibold mb-3">Your Profile</p>

            @if ($hasProfile)
                <dl class="space-y-2.5 text-sm">
                    <div>
                        <dt class="text-brown/70 text-xs">Job title</dt>
                        <dd class="font-medium text-jet">{{ $user->job_title ?: 'Not set' }}</dd>
                    </div>
                    <div>
                        <dt class="text-brown/70 text-xs">Skills</dt>
                        <dd class="font-medium text-jet">{{ $user->skills ?: 'Not set' }}</dd>
                    </div>
                    <div>
                        <dt class="text-brown/70 text-xs">Profile description</dt>
                        <dd class="text-jet/90 leading-relaxed">{{ $user->profile_description ?: 'Not set' }}</dd>
                    </div>
                </dl>
            @else
                <div class="text-sm text-brown">
                    <p class="mb-3">Your profile is incomplete. Add your job title, skills and description so the AI can find the best matches for you.</p>
                    <a href="{{ route('profile.edit') }}" class="inline-block px-4 py-2 bg-khaki text-black rounded-lg text-sm font-medium hover:bg-almond">
                        Complete your profile
                    </a>
                </div>
            @endif
        </div>

        <div class="md:col-span-2">
            <div class="bg-white rounded-2xl shadow border border-khaki/40 p-5">
                <button type="button" id="recommend-btn"
                        class="w-full sm:w-auto px-6 py-3 bg-brown text-white rounded-lg text-sm font-semibold hover:bg-black transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        {{ $hasProfile ? '' : 'disabled' }}>
                    Get AI Recommendations
                </button>

                <div id="loading" class="hidden mt-5 text-sm text-brown">
                    <span class="inline-block w-4 h-4 border-2 border-brown border-t-transparent rounded-full animate-spin align-middle mr-2"></span>
                    Analyzing your profile against {{ \App\Models\Job::count() }} jobs...
                </div>

                <div id="message" class="hidden mt-5 text-sm px-4 py-3 rounded-lg bg-almond text-brown border border-khaki/40 whitespace-pre-wrap"></div>

                <div id="results" class="mt-5 space-y-4"></div>
            </div>
        </div>
    </div>
</div>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const btn = document.getElementById('recommend-btn');
    const loading = document.getElementById('loading');
    const message = document.getElementById('message');
    const results = document.getElementById('results');

    btn.addEventListener('click', async () => {
        btn.disabled = true;
        results.innerHTML = '';
        message.classList.add('hidden');
        loading.classList.remove('hidden');

        try {
            const res = await fetch('/api/ai/recommendations', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' },
                body: JSON.stringify({}),
            });
            const data = await res.json();

            if (!res.ok && !data.success) throw data;

            if (data.message) {
                message.textContent = data.message;
                message.classList.remove('hidden');
            }

            if (data.jobs && data.jobs.length) {
                results.innerHTML = data.jobs.map((job) => `
                    <div class="border border-khaki/40 rounded-xl p-4 bg-white">
                        <p class="text-sm italic text-brown mb-1.5">${job.reason}</p>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <a href="${job.url}" class="text-base font-semibold text-jet hover:text-brown">${job.title}</a>
                                <p class="text-xs text-brown/80 mt-0.5">${job.category} &middot; ${job.work_type} &middot; ${job.location}</p>
                            </div>
                            ${job.applied
                                ? '<span class="text-xs px-2.5 py-1 rounded-full bg-khaki/40 text-brown font-medium">Applied</span>'
                                : `<a href="${job.url}" class="text-xs px-3 py-1.5 rounded-md bg-brown text-white font-medium hover:bg-black">View job</a>`}
                        </div>
                        <p class="text-xs text-brown/70 mt-2">
                            ${job.salary ? 'Salary: ' + job.salary + ' &middot; ' : ''}Closes: ${job.deadline}
                        </p>
                    </div>
                `).join('');
            }
        } catch (err) {
            message.textContent = 'Sorry, something went wrong: ' + (err.message ?? 'unknown error');
            message.classList.remove('hidden');
        } finally {
            loading.classList.add('hidden');
            btn.disabled = false;
        }
    });
</script>
@endsection