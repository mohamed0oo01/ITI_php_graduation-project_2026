<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminJobManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function validJobData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'New Job Title',
            'description' => 'Job description here.',
            'required_skills' => 'PHP, Laravel, MySQL',
            'category' => 'Programming',
            'location' => 'Cairo',
            'work_type' => 'Remote',
            'salary' => 20000,
            'application_deadline' => now()->addMonth()->toDateString(),
        ], $overrides);
    }

    public function test_only_admin_can_access_job_management(): void
    {
        $job = Job::factory()->create();

        $this->get('/admin/jobs')->assertRedirect('/login');

        $candidate = User::factory()->create(['role' => 'candidate']);
        $this->actingAs($candidate)->get('/admin/jobs')->assertRedirect('/candidate/dashboard');

        $this->actingAs($this->admin())
            ->get('/admin/jobs')
            ->assertOk()
            ->assertSee($job->title);
    }

    public function test_admin_can_create_a_job(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/jobs', $this->validJobData())
            ->assertRedirect('/admin/jobs');

        $this->assertDatabaseHas('jobs', ['title' => 'New Job Title']);
    }

    public function test_create_job_validates_data(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/jobs', $this->validJobData([
                'work_type' => 'Sometimes',
                'salary' => -5,
                'application_deadline' => now()->subDay()->toDateString(),
            ]))
            ->assertSessionHasErrors(['work_type', 'salary', 'application_deadline']);

        $this->assertSame(0, Job::count());
    }

    public function test_admin_can_edit_a_job(): void
    {
        $admin = $this->admin();
        $job = Job::factory()->create(['title' => 'Old Title']);

        $this->actingAs($admin)
            ->put("/admin/jobs/{$job->id}", $this->validJobData(['title' => 'Updated Title']))
            ->assertRedirect('/admin/jobs');

        $this->assertDatabaseHas('jobs', ['id' => $job->id, 'title' => 'Updated Title']);
    }

    public function test_admin_can_delete_a_job_without_applications(): void
    {
        $admin = $this->admin();
        $job = Job::factory()->create();

        $this->actingAs($admin)
            ->delete("/admin/jobs/{$job->id}")
            ->assertRedirect('/admin/jobs');

        $this->assertDatabaseMissing('jobs', ['id' => $job->id]);
    }

    public function test_admin_cannot_delete_a_job_that_has_applications(): void
    {
        $admin = $this->admin();
        $candidate = User::factory()->create(['role' => 'candidate']);
        $job = Job::factory()->create();
        JobApplication::create(['user_id' => $candidate->id, 'job_id' => $job->id]);

        $this->actingAs($admin)
            ->from('/admin/jobs')
            ->delete("/admin/jobs/{$job->id}")
            ->assertRedirect('/admin/jobs')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('jobs', ['id' => $job->id]);
        $this->assertDatabaseHas('job_applications', ['job_id' => $job->id]);
    }

    public function test_job_list_shows_delete_disabled_for_jobs_with_applications(): void
    {
        $admin = $this->admin();
        $candidate = User::factory()->create(['role' => 'candidate']);
        $job = Job::factory()->create(['title' => 'Popular Job']);
        JobApplication::create(['user_id' => $candidate->id, 'job_id' => $job->id]);

        $this->actingAs($admin)
            ->get('/admin/jobs')
            ->assertOk()
            ->assertSee('Cannot delete: has 1 application(s)');
    }
}
