<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobApplicationTest extends TestCase
{
    use RefreshDatabase;

    private function candidate(): User
    {
        return User::factory()->create(['role' => 'candidate']);
    }

    public function test_guest_cannot_apply_and_is_redirected_to_login(): void
    {
        $job = Job::factory()->create();

        $this->post(route('job.apply', $job))->assertRedirect('/login');

        $this->assertSame(0, JobApplication::count());
    }

    public function test_admin_cannot_apply(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $job = Job::factory()->create();

        $this->actingAs($admin)->post(route('job.apply', $job))->assertRedirect('/admin/dashboard');

        $this->assertSame(0, JobApplication::count());
    }

    public function test_candidate_can_apply_to_a_job(): void
    {
        $candidate = $this->candidate();
        $job = Job::factory()->create();

        $this->actingAs($candidate)
            ->post(route('job.apply', $job))
            ->assertRedirect();

        $this->assertDatabaseHas('job_applications', [
            'user_id' => $candidate->id,
            'job_id' => $job->id,
        ]);
    }

    public function test_candidate_cannot_apply_twice_to_same_job(): void
    {
        $candidate = $this->candidate();
        $job = Job::factory()->create();

        $this->actingAs($candidate)->post(route('job.apply', $job));
        $this->actingAs($candidate)->post(route('job.apply', $job));

        $this->assertSame(1, JobApplication::where('job_id', $job->id)->count());
    }

    public function test_show_page_shows_apply_now_for_candidate_who_has_not_applied(): void
    {
        $candidate = $this->candidate();
        $job = Job::factory()->create();

        $this->actingAs($candidate)
            ->get(route('jobs.show', $job))
            ->assertOk()
            ->assertSee('Apply Now');
    }

    public function test_show_page_shows_already_applied_and_cancel_for_applied_candidate(): void
    {
        $candidate = $this->candidate();
        $job = Job::factory()->create();
        JobApplication::create(['user_id' => $candidate->id, 'job_id' => $job->id]);

        $response = $this->actingAs($candidate)
            ->get(route('jobs.show', $job))
            ->assertOk()
            ->assertSee('Already Applied')
            ->assertSee('Cancel Application');

        $this->assertStringNotContainsString('Apply Now', $response->getContent());
    }

    public function test_candidate_can_cancel_their_application(): void
    {
        $candidate = $this->candidate();
        $job = Job::factory()->create();
        JobApplication::create(['user_id' => $candidate->id, 'job_id' => $job->id]);

        $this->actingAs($candidate)
            ->delete(route('job.cancel', $job))
            ->assertRedirect();

        $this->assertSame(0, JobApplication::where('job_id', $job->id)->count());
    }

    public function test_guest_sees_login_to_apply_button(): void
    {
        $job = Job::factory()->create();

        $this->get(route('jobs.show', $job))
            ->assertOk()
            ->assertSee('Login to Apply');
    }

    public function test_candidate_cannot_apply_after_deadline_has_passed(): void
    {
        $candidate = $this->candidate();
        $job = Job::factory()->create(['application_deadline' => now()->subDay()]);

        $this->actingAs($candidate)
            ->post(route('job.apply', $job))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(0, JobApplication::where('job_id', $job->id)->count());
    }

    public function test_candidate_can_apply_on_the_deadline_day(): void
    {
        $candidate = $this->candidate();
        $job = Job::factory()->create(['application_deadline' => now()->toDateString()]);

        $this->actingAs($candidate)
            ->post(route('job.apply', $job))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('job_applications', [
            'user_id' => $candidate->id,
            'job_id' => $job->id,
        ]);
    }

    public function test_show_page_shows_deadline_passed_instead_of_apply(): void
    {
        $candidate = $this->candidate();
        $job = Job::factory()->create(['application_deadline' => now()->subDay()]);

        $response = $this->actingAs($candidate)
            ->get(route('jobs.show', $job))
            ->assertOk()
            ->assertSee('Deadline Passed');

        $this->assertStringNotContainsString('Apply Now', $response->getContent());
        $this->assertStringNotContainsString('Already Applied', $response->getContent());
    }
}
