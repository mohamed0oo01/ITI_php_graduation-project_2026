<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{
    public function show(): View
    {
        return view('profile.show', ['user' => auth()->user()]);
    }

    public function edit(): View
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'age' => ['nullable', 'integer', 'min:18', 'max:100'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'profile_description' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'skills' => ['nullable', 'string'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'resume' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $user = auth()->user();

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $data['profile_image'] = $request->file('profile_image')->store('profile_images', 'public');
        }

        if ($request->hasFile('resume')) {
            if ($user->resume) {
                Storage::delete($user->resume);
            }
            $data['resume'] = $request->file('resume')->store('resumes');
        }

        $user->update($data);

        return redirect()->route('profile.show')
            ->with('success', 'Profile updated successfully.');
    }

    public function downloadResume(): StreamedResponse
    {
        $user = auth()->user();

        abort_unless($user->resume, 404);

        return Storage::download($user->resume);
    }
}
