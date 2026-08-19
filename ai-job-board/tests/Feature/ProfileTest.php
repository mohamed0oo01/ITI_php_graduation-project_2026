<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function candidate(): User
    {
        return User::factory()->create(['role' => 'candidate']);
    }

    public function test_guest_is_redirected_to_login_from_profile_pages(): void
    {
        $this->get('/profile')->assertRedirect('/login');
        $this->get('/profile/edit')->assertRedirect('/login');
    }

    public function test_admin_is_redirected_away_from_profile_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/profile')->assertRedirect('/admin/dashboard');
    }

    public function test_candidate_can_view_profile(): void
    {
        $candidate = User::factory()->create([
            'role' => 'candidate',
            'name' => 'Mohamed Elhaddad',
            'age' => 26,
            'job_title' => 'PHP Backend Developer',
            'phone' => '+20 100 000 0001',
            'skills' => 'PHP, Laravel, MySQL',
        ]);

        $this->actingAs($candidate)
            ->get('/profile')
            ->assertOk()
            ->assertSee($candidate->name)
            ->assertSee('PHP Backend Developer')
            ->assertSee('+20 100 000 0001');
    }

    public function test_candidate_can_update_profile_fields(): void
    {
        $candidate = $this->candidate();

        $this->actingAs($candidate)
            ->put('/profile', [
                'name' => 'Updated Name',
                'age' => 30,
                'job_title' => 'Senior Laravel Developer',
                'profile_description' => 'Updated description.',
                'phone' => '+20 111 222 333',
                'skills' => 'PHP, Laravel, MySQL, Docker',
            ])
            ->assertRedirect('/profile');

        $this->assertDatabaseHas('users', [
            'id' => $candidate->id,
            'name' => 'Updated Name',
            'age' => 30,
            'job_title' => 'Senior Laravel Developer',
            'profile_description' => 'Updated description.',
            'phone' => '+20 111 222 333',
            'skills' => 'PHP, Laravel, MySQL, Docker',
        ]);
    }

    public function test_candidate_can_upload_profile_image(): void
    {
        Storage::fake('public');

        $candidate = $this->candidate();

        $tinyPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');

        $this->actingAs($candidate)
            ->put('/profile', [
                'name' => $candidate->name,
                'profile_image' => UploadedFile::fake()->createWithContent('avatar.png', $tinyPng),
            ])
            ->assertRedirect('/profile');

        $this->assertNotNull($candidate->fresh()->profile_image);
        Storage::disk('public')->assertExists($candidate->fresh()->profile_image);
    }

    public function test_profile_image_rejects_non_image_files(): void
    {
        $candidate = $this->candidate();

        $this->actingAs($candidate)
            ->put('/profile', [
                'name' => $candidate->name,
                'profile_image' => UploadedFile::fake()->create('notes.txt', 100),
            ])
            ->assertSessionHasErrors('profile_image');

        $this->assertNull($candidate->fresh()->profile_image);
    }

    public function test_candidate_can_upload_pdf_resume(): void
    {
        Storage::fake('local');

        $candidate = $this->candidate();

        $this->actingAs($candidate)
            ->put('/profile', [
                'name' => $candidate->name,
                'resume' => UploadedFile::fake()->create('resume.pdf', 500, 'application/pdf'),
            ])
            ->assertRedirect('/profile');

        $this->assertNotNull($candidate->fresh()->resume);
        Storage::disk('local')->assertExists($candidate->fresh()->resume);
    }

    public function test_resume_rejects_non_pdf_files(): void
    {
        $candidate = $this->candidate();

        $this->actingAs($candidate)
            ->put('/profile', [
                'name' => $candidate->name,
                'resume' => UploadedFile::fake()->create('resume.docx', 500),
            ])
            ->assertSessionHasErrors('resume');

        $this->assertNull($candidate->fresh()->resume);
    }

    public function test_candidate_can_download_resume(): void
    {
        Storage::fake('local');

        $candidate = $this->candidate();
        Storage::disk('local')->put('resumes/test.pdf', 'pdf-content');
        $candidate->update(['resume' => 'resumes/test.pdf']);

        $this->actingAs($candidate->fresh())
            ->get('/profile/resume')
            ->assertOk()
            ->assertDownload('test.pdf');
    }
}
