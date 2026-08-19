<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCandidatesAndApplicationsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_only_admin_can_access_candidates_page(): void
    {
        $this->get('/admin/candidates')->assertRedirect('/login');

        $candidate = User::factory()->create(['role' => 'candidate']);
        $this->actingAs($candidate)->get('/admin/candidates')->assertRedirect('/candidate/dashboard');
    }

    public function test_admin_sees_all_candidates_with_fields(): void
    {
        $candidate = User::factory()->create([
            'role' => 'candidate',
            'name' => 'Mohamed Elhaddad',
            'email' => 'mohamed@example.com',
            'job_title' => 'PHP Backend Developer',
            'phone' => '+20 100 000 0001',
            'skills' => 'PHP, Laravel, MySQL',
        ]);

        $this->actingAs($this->admin())
            ->get('/admin/candidates')
            ->assertOk()
            ->assertSee('Mohamed Elhaddad')
            ->assertSee('mohamed@example.com')
            ->assertSee('PHP Backend Developer')
            ->assertSee('+20 100 000 0001')
            ->assertSee('PHP, Laravel, MySQL');
    }

    public function test_admin_can_view_candidate_profile(): void
    {
        $candidate = User::factory()->create([
            'role' => 'candidate',
            'name' => 'Ali Mohamed',
            'profile_description' => 'Frontend developer.',
        ]);

        $this->actingAs($this->admin())
            ->get("/admin/candidates/{$candidate->id}")
            ->assertOk()
            ->assertSee('Ali Mohamed')
            ->assertSee('Frontend developer.');
    }

    public function test_admin_cannot_view_admin_profile_through_candidates_route(): void
    {
        $otherAdmin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($this->admin())
            ->get("/admin/candidates/{$otherAdmin->id}")
            ->assertNotFound();
    }

    public function test_admin_can_download_candidate_cv(): void
    {
        Storage::fake('local');

        $candidate = User::factory()->create(['role' => 'candidate']);
        Storage::disk('local')->put('resumes/test.pdf', 'pdf');
        $candidate->update(['resume' => 'resumes/test.pdf']);

        $this->actingAs($this->admin())
            ->get("/admin/candidates/{$candidate->id}/resume")
            ->assertOk()
            ->assertDownload('test.pdf');
    }

    public function test_admin_sees_all_applications_with_links(): void
    {
        $candidate = User::factory()->create([
            'role' => 'candidate',
            'name' => 'Yousef Roshdy',
            'resume' => null,
        ]);
        $job = Job::factory()->create(['title' => 'Laravel Developer']);
        JobApplication::create(['user_id' => $candidate->id, 'job_id' => $job->id]);

        $this->actingAs($this->admin())
            ->get('/admin/applications')
            ->assertOk()
            ->assertSee('Yousef Roshdy')
            ->assertSee('Laravel Developer')
            ->assertSee('View Candidate')
            ->assertSee('View Job');
    }

    public function test_admin_applications_page_lists_everything_without_n1_query_problems(): void
    {
        $admin = $this->admin();
        $candidates = User::factory()->count(3)->create(['role' => 'candidate']);
        $jobs = Job::factory()->count(3)->create();

        foreach ($candidates as $index => $candidate) {
            JobApplication::create(['user_id' => $candidate->id, 'job_id' => $jobs[$index]->id]);
        }

        $this->actingAs($admin)
            ->get('/admin/applications')
            ->assertOk()
            ->assertSee($candidates[0]->name)
            ->assertSee($jobs[0]->title);
    }
}