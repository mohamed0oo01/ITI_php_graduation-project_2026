<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyApplicationsTest extends TestCase
{
    use RefreshDatabase;

    private function candidate(): User
    {
        return User::factory()->create(['role' => 'candidate']);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/my-applications')->assertRedirect('/login');
    }

    public function test_admin_is_redirected_to_admin_area(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/my-applications')->assertRedirect('/admin/dashboard');
    }

    public function test_candidate_sees_their_applications_with_details(): void
    {
        $candidate = $this->candidate();
        $job = Job::factory()->create([
            'title' => 'Laravel Developer',
            'category' => 'Programming',
            'location' => 'Cairo',
        ]);
        JobApplication::create(['user_id' => $candidate->id, 'job_id' => $job->id]);

        $this->actingAs($candidate)
            ->get('/my-applications')
            ->assertOk()
            ->assertSee('Laravel Developer')
            ->assertSee('Programming')
            ->assertSee('Cairo')
            ->assertSee(route('jobs.show', $job))
            ->assertSee('Active');
    }

    public function test_candidate_sees_only_their_own_applications(): void
    {
        $candidate = $this->candidate();
        $other = User::factory()->create(['role' => 'candidate']);

        $job = Job::factory()->create(['title' => 'My Job']);
        $otherJob = Job::factory()->create(['title' => 'Other Candidate Job']);

        JobApplication::create(['user_id' => $candidate->id, 'job_id' => $job->id]);
        JobApplication::create(['user_id' => $other->id, 'job_id' => $otherJob->id]);

        $response = $this->actingAs($candidate)
            ->get('/my-applications')
            ->assertOk()
            ->assertSee('My Job');

        $this->assertStringNotContainsString('Other Candidate Job', $response->getContent());
    }

    public function test_candidate_sees_closed_status_for_expired_jobs(): void
    {
        $candidate = $this->candidate();
        $job = Job::factory()->create(['application_deadline' => now()->subDay()]);
        JobApplication::create(['user_id' => $candidate->id, 'job_id' => $job->id]);

        $this->actingAs($candidate)
            ->get('/my-applications')
            ->assertOk()
            ->assertSee('Closed');
    }

    public function test_empty_state_message(): void
    {
        $candidate = $this->candidate();

        $this->actingAs($candidate)
            ->get('/my-applications')
            ->assertOk()
            ->assertSee('You have not applied to any jobs yet.');
    }

    public function test_cancel_button_cancels_application_from_this_page(): void
    {
        $candidate = $this->candidate();
        $job = Job::factory()->create();
        JobApplication::create(['user_id' => $candidate->id, 'job_id' => $job->id]);

        $this->actingAs($candidate)
            ->from('/my-applications')
            ->delete(route('job.cancel', $job))
            ->assertRedirect('/my-applications');

        $this->assertSame(0, JobApplication::where('job_id', $job->id)->count());
    }
}
