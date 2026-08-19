<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            'featuredJobs' => Job::latest()->take(3)->get(),
        ]);
    }
}