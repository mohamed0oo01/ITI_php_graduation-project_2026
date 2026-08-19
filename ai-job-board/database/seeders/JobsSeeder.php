<?php

namespace Database\Seeders;

use App\Models\Job;
use Illuminate\Database\Seeder;

class JobsSeeder extends Seeder
{
    /**
     * Seed the application's demo jobs.
     */
    public function run(): void
    {
        $jobs = [
            [
                'title' => 'Senior Laravel Developer',
                'description' => 'We are looking for an experienced Laravel developer to build and maintain high-quality backend services, REST APIs, and internal tools for our SaaS platform.',
                'required_skills' => 'PHP, Laravel, MySQL, REST API, Vue.js',
                'category' => 'Programming',
                'location' => 'Cairo',
                'work_type' => 'Remote',
                'salary' => 25000,
                'application_deadline' => now()->addMonths(2)->toDateString(),
            ],
            [
                'title' => 'Frontend React Developer',
                'description' => 'Join our product team to build fast, accessible, and pixel-perfect web applications using React and modern tooling.',
                'required_skills' => 'JavaScript, React, HTML, CSS, Tailwind CSS',
                'category' => 'Programming',
                'location' => 'Alexandria',
                'work_type' => 'On-site',
                'salary' => 18000,
                'application_deadline' => now()->addMonths(1)->toDateString(),
            ],
            [
                'title' => 'Full Stack Web Developer',
                'description' => 'Work on a cross-functional team shipping end-to-end features across the stack using Laravel, Vue.js, and Docker.',
                'required_skills' => 'PHP, Laravel, Vue.js, MySQL, Docker',
                'category' => 'Programming',
                'location' => 'Cairo',
                'work_type' => 'Hybrid',
                'salary' => 22000,
                'application_deadline' => now()->addMonths(3)->toDateString(),
            ],
            [
                'title' => 'UI/UX Designer',
                'description' => 'Design intuitive user interfaces and delightful experiences for web and mobile products. Work closely with product managers and developers.',
                'required_skills' => 'Figma, Adobe XD, UI Design, UX Research, HTML, CSS',
                'category' => 'Design',
                'location' => 'Giza',
                'work_type' => 'Hybrid',
                'salary' => 16000,
                'application_deadline' => now()->addMonths(1)->toDateString(),
            ],
            [
                'title' => 'Data Scientist',
                'description' => 'Analyze large datasets, build machine learning models, and turn insights into product decisions for our analytics platform.',
                'required_skills' => 'Python, Machine Learning, SQL, Pandas, TensorFlow',
                'category' => 'Data',
                'location' => 'Cairo',
                'work_type' => 'On-site',
                'salary' => 30000,
                'application_deadline' => now()->addMonths(2)->toDateString(),
            ],
            [
                'title' => 'DevOps Engineer',
                'description' => 'Manage cloud infrastructure, automate CI/CD pipelines, and keep our services fast, secure, and highly available.',
                'required_skills' => 'Docker, Kubernetes, AWS, CI/CD, Linux',
                'category' => 'Programming',
                'location' => 'Remote',
                'work_type' => 'Remote',
                'salary' => 28000,
                'application_deadline' => now()->addMonths(2)->toDateString(),
            ],
            [
                'title' => 'Digital Marketing Specialist',
                'description' => 'Plan and execute marketing campaigns, SEO strategy, and social media growth across multiple channels.',
                'required_skills' => 'SEO, Google Ads, Social Media, Content Writing, Analytics',
                'category' => 'Marketing',
                'location' => 'Cairo',
                'work_type' => 'On-site',
                'salary' => 12000,
                'application_deadline' => now()->addMonth()->toDateString(),
            ],
            [
                'title' => 'Mobile App Developer (Flutter)',
                'description' => 'Develop cross-platform mobile applications with Flutter, integrating with backend REST APIs.',
                'required_skills' => 'Dart, Flutter, REST API, Firebase, Git',
                'category' => 'Programming',
                'location' => 'Alexandria',
                'work_type' => 'Hybrid',
                'salary' => 20000,
                'application_deadline' => now()->addMonths(2)->toDateString(),
            ],
        ];

        foreach ($jobs as $job) {
            Job::create($job);
        }
    }
}
