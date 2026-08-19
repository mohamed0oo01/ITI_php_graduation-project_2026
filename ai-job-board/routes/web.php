<?php

use App\Http\Controllers\AdminApplicationController;
use App\Http\Controllers\AdminCandidateController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminJobController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\JobRecommendationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{job}', [JobController::class, 'show'])->name('jobs.show');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register.create');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/assistant', function () {
        return view('assistant');
    })->name('assistant');

    Route::get('/api/ai/conversations', [AiChatController::class, 'index'])->name('ai.conversations');
    Route::get('/api/ai/conversations/{conversation}', [AiChatController::class, 'show'])->name('ai.conversations.show');
    Route::post('/api/ai/chat', [AiChatController::class, 'chat'])->name('ai.chat');
    Route::delete('/api/ai/chat/{conversation}', [AiChatController::class, 'destroy'])->name('ai.chat.delete');
});

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        Route::get('/jobs', [AdminJobController::class, 'index'])->name('jobs');
        Route::get('/jobs/create', [AdminJobController::class, 'create'])->name('jobs.create');
        Route::post('/jobs', [AdminJobController::class, 'store'])->name('jobs.store');
        Route::get('/jobs/{job}/edit', [AdminJobController::class, 'edit'])->name('jobs.edit');
        Route::put('/jobs/{job}', [AdminJobController::class, 'update'])->name('jobs.update');
        Route::delete('/jobs/{job}', [AdminJobController::class, 'destroy'])->name('jobs.destroy');

        Route::get('/candidates', [AdminCandidateController::class, 'index'])->name('candidates');
        Route::get('/candidates/{user}', [AdminCandidateController::class, 'show'])->name('candidates.show');
        Route::get('/candidates/{user}/resume', [AdminCandidateController::class, 'downloadResume'])->name('candidates.resume');

        Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications');
    });

Route::middleware(['auth', 'candidate'])
    ->prefix('candidate')
    ->name('candidate.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('candidate.dashboard');
        })->name('dashboard');
    });

Route::middleware(['auth', 'candidate'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/resume', [ProfileController::class, 'downloadResume'])->name('profile.resume');

    Route::post('/jobs/{job}/apply', [JobApplicationController::class, 'store'])->name('job.apply');
    Route::delete('/jobs/{job}/apply', [JobApplicationController::class, 'destroy'])->name('job.cancel');

    Route::get('/my-applications', [JobApplicationController::class, 'index'])->name('my-applications');

    Route::get('/recommendations', [JobRecommendationController::class, 'index'])->name('recommendations');
    Route::post('/api/ai/recommendations', [JobRecommendationController::class, 'recommend'])->name('ai.recommendations');
});
