<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminCandidateController extends Controller
{
    public function index(): View
    {
        $candidates = User::where('role', 'candidate')->latest()->get();

        return view('admin.candidates.index', ['candidates' => $candidates]);
    }

    public function show(User $user): View
    {
        abort_unless($user->role === 'candidate', 404);

        return view('admin.candidates.show', ['candidate' => $user]);
    }

    public function downloadResume(User $user): StreamedResponse
    {
        abort_unless($user->role === 'candidate' && $user->resume, 404);

        return Storage::download($user->resume);
    }
}
