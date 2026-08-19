<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UILayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_shows_hero_search_and_featured_jobs(): void
    {
        Job::factory()->create(['title' => 'Backend Developer']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Find Your Next Opportunity')
            ->assertSee('AI-POWERED MATCHING')
            ->assertSee('Featured Jobs')
            ->assertSee('Backend Developer')
            ->assertSee('Search Jobs');
    }

    public function test_candidate_navbar_links_are_rendered(): void
    {
        $candidate = User::factory()->create(['role' => 'candidate']);

        $this->actingAs($candidate)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('My Applications')
            ->assertSee('My Profile')
            ->assertSee('AI Assistant')
            ->assertSee('Logout');
    }

    public function test_admin_navbar_links_are_rendered(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Candidates')
            ->assertSee('Applications')
            ->assertSee('AI Assistant')
            ->assertSee('Logout');
    }

    public function test_guest_navbar_links_are_rendered(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Login')
            ->assertSee('Register')
            ->assertSee('Jobs');
    }

    public function test_mobile_menu_toggle_is_present(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('menu-toggle', false)
            ->assertSee('mobile-menu', false);
    }

    public function test_assistant_page_requires_login(): void
    {
        $this->get(route('assistant'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create(['role' => 'candidate']))
            ->get(route('assistant'))
            ->assertOk()
            ->assertSee('AI Assistant');
    }

    public function test_assistant_page_has_chat_ui_and_calls_ai_endpoint(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'candidate']))
            ->get(route('assistant'))
            ->assertOk()
            ->assertSee('chat-form', false)
            ->assertSee('chat-input', false)
            ->assertSee('messages', false)
            ->assertSee('/api/ai/chat', false);
    }

    public function test_home_page_search_links_to_jobs_index(): void
    {
        Job::factory()->create(['title' => 'Data Analyst']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('jobs.index'));

        $this->get(route('jobs.index', ['search' => 'Data']))
            ->assertOk()
            ->assertSee('Data Analyst');
    }
}