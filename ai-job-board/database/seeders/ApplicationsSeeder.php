<?php

namespace Database\Seeders;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Database\Seeder;

class ApplicationsSeeder extends Seeder
{
    /**
     * Seed some demo job applications so the admin dashboard has data.
     */
    public function run(): void
    {
        $candidates = User::where('role', 'candidate')->get();
        $jobs = Job::all();

        if ($candidates->isEmpty() || $jobs->isEmpty()) {
            return;
        }

        foreach ($candidates as $candidate) {
            $count = min(2, $jobs->count());
            $randomJobs = $jobs->random($count);

            foreach ($randomJobs as $job) {
                JobApplication::firstOrCreate([
                    'user_id' => $candidate->id,
                    'job_id' => $job->id,
                ]);
            }
        }
    }
}
