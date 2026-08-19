<?php

namespace Tests\Feature;

use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobsListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_browse_jobs(): void
    {
        Job::factory()->create(['title' => 'Laravel Developer']);

        $this->get('/jobs')
            ->assertOk()
            ->assertSee('Laravel Developer');
    }

    public function test_job_card_shows_required_fields(): void
    {
        $job = Job::factory()->create([
            'title' => 'Frontend React Developer',
            'category' => 'Programming',
            'location' => 'Alexandria',
            'work_type' => 'On-site',
            'salary' => 18000,
            'application_deadline' => now()->addMonths(1),
        ]);

        $this->get('/jobs')
            ->assertOk()
            ->assertSee($job->title)
            ->assertSee($job->category)
            ->assertSee($job->location)
            ->assertSee($job->work_type)
            ->assertSee('18,000')
            ->assertSee(route('jobs.show', $job));
    }

    public function test_guest_can_view_job_details(): void
    {
        $job = Job::factory()->create([
            'title' => 'Data Scientist',
            'description' => 'Build machine learning models.',
            'required_skills' => 'Python, Machine Learning, Pandas',
        ]);

        $this->get(route('jobs.show', $job))
            ->assertOk()
            ->assertSee($job->title)
            ->assertSee($job->description)
            ->assertSee('Python')
            ->assertSee('Pandas');
    }

    public function test_search_by_job_title(): void
    {
        Job::factory()->create(['title' => 'Senior Laravel Developer']);
        Job::factory()->create(['title' => 'UI/UX Designer']);

        $response = $this->get('/jobs?search=Laravel')
            ->assertOk()
            ->assertSee('Senior Laravel Developer');

        $this->assertStringNotContainsString('UI/UX Designer', $response->getContent());
    }

    public function test_filter_by_category(): void
    {
        Job::factory()->create(['title' => 'Laravel Job', 'category' => 'Programming']);
        Job::factory()->create(['title' => 'Marketing Job', 'category' => 'Marketing']);

        $response = $this->get('/jobs?category=Programming')
            ->assertOk()
            ->assertSee('Laravel Job');

        $this->assertStringNotContainsString('Marketing Job', $response->getContent());
    }

    public function test_filter_by_work_type(): void
    {
        Job::factory()->create(['title' => 'Remote Job', 'work_type' => 'Remote']);
        Job::factory()->create(['title' => 'Onsite Job', 'work_type' => 'On-site']);

        $response = $this->get('/jobs?work_type=Remote')
            ->assertOk()
            ->assertSee('Remote Job');

        $this->assertStringNotContainsString('Onsite Job', $response->getContent());
    }

    public function test_filter_by_location(): void
    {
        Job::factory()->create(['title' => 'Cairo Job', 'location' => 'Cairo']);
        Job::factory()->create(['title' => 'Giza Job', 'location' => 'Giza']);

        $response = $this->get('/jobs?location=Cairo')
            ->assertOk()
            ->assertSee('Cairo Job');

        $this->assertStringNotContainsString('Giza Job', $response->getContent());
    }

    public function test_search_with_no_results_shows_message(): void
    {
        Job::factory()->create(['title' => 'Laravel Developer']);

        $this->get('/jobs?search=zzzzz')
            ->assertOk()
            ->assertSee('No jobs found.');
    }
}