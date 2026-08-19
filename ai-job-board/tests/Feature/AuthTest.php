<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_see_login_page(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    public function test_guest_can_see_register_page(): void
    {
        $this->get('/register')->assertStatus(200);
    }

    public function test_candidate_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'New Candidate',
            'email' => 'candidate@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect('/');

        $user = User::where('email', 'candidate@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('candidate', $user->role);

        $this->assertAuthenticatedAs($user);
    }

    public function test_registered_user_can_never_be_admin(): void
    {
        $this->post('/register', [
            'name' => 'Sneaky Admin',
            'email' => 'sneaky@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $this->assertSame('candidate', User::where('email', 'sneaky@example.com')->first()->role);
        $this->assertSame(0, User::where('role', 'admin')->count());
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->post('/register', [
            'name' => 'Duplicate',
            'email' => 'taken@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertSame(1, User::where('email', 'taken@example.com')->count());
    }

    public function test_candidate_can_login(): void
    {
        $user = User::factory()->create(['email' => 'candidate@example.com', 'password' => 'secret123']);

        $this->post('/login', ['email' => 'candidate@example.com', 'password' => 'secret123'])
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_login(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->post('/login', ['email' => 'admin@example.com', 'password' => 'password'])
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($admin);
        $this->assertTrue($admin->isAdmin());
    }

    public function test_login_rejects_wrong_password(): void
    {
        User::factory()->create(['email' => 'candidate@example.com', 'password' => 'secret123']);

        $this->post('/login', ['email' => 'candidate@example.com', 'password' => 'wrongpassword'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_candidate_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_admin_can_logout(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_authenticated_user_cannot_see_login_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/login')->assertRedirect('/');
        $this->actingAs($user)->get('/register')->assertRedirect('/');
    }
}
