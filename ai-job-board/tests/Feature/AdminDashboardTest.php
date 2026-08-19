<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }

    public function test_candidate_is_redirected_away(): void
    {
        $candidate = User::factory()->create(['role' => 'candidate']);

        $this->actingAs($candidate)->get('/admin/dashboard')->assertRedirect('/candidate/dashboard');
    }

    public function test_admin_sees_total_counts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        User::factory()->count(5)->create(['role' => 'candidate']);
        $jobs = Job::factory()->count(3)->create();

        $candidate = User::factory()->create(['role' => 'candidate']);
        JobApplication::create(['user_id' => $candidate->id, 'job_id' => $jobs->first()->id]);
        JobApplication::create(['user_id' => $candidate->id, 'job_id' => $jobs->last()->id]);

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Total Candidates')
            ->assertSee('Total Jobs')
            ->assertSee('Total Applications')
            ->assertSee('6')
            ->assertSee('3')
            ->assertSee('2');
    }

    public function test_admin_sees_most_applied_job(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $topJob = Job::factory()->create(['title' => 'Laravel Developer']);
        $otherJob = Job::factory()->create(['title' => 'React Developer']);

        $candidates = User::factory()->count(3)->create(['role' => 'candidate']);
        foreach ($candidates as $index => $candidate) {
            JobApplication::create(['user_id' => $candidate->id, 'job_id' => $topJob->id]);
            if ($index === 0) {
                JobApplication::create(['user_id' => $candidate->id, 'job_id' => $otherJob->id]);
            }
        }

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Laravel Developer')
            ->assertSee('3 Applications');
    }

    public function test_admin_dashboard_handles_no_jobs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('No jobs yet.');
    }
}
