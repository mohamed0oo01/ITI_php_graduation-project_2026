<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleProtectionTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function candidate(): User
    {
        return User::factory()->create(['role' => 'candidate']);
    }

    public function test_guest_is_redirected_to_login_for_admin_pages(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/admin/jobs')->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_for_candidate_pages(): void
    {
        $this->get('/candidate/dashboard')->assertRedirect('/login');
    }

    public function test_admin_can_access_admin_pages(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/dashboard')
            ->assertOk();

        $this->actingAs($this->admin())
            ->get('/admin/jobs')
            ->assertOk();
    }

    public function test_candidate_cannot_access_admin_pages_and_is_redirected(): void
    {
        $this->actingAs($this->candidate())
            ->get('/admin/jobs')
            ->assertRedirect('/candidate/dashboard');

        $this->actingAs($this->candidate())
            ->get('/admin/dashboard')
            ->assertRedirect('/candidate/dashboard');
    }

    public function test_candidate_can_access_candidate_pages(): void
    {
        $this->actingAs($this->candidate())
            ->get('/candidate/dashboard')
            ->assertOk();
    }

    public function test_admin_is_redirected_to_admin_area_from_candidate_pages(): void
    {
        $this->actingAs($this->admin())
            ->get('/candidate/dashboard')
            ->assertRedirect('/admin/dashboard');
    }
}
