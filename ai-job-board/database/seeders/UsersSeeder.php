<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Seed the application's users (one admin + demo candidates).
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $candidates = [
            [
                'name' => 'Mohamed Elhaddad',
                'email' => 'mohamed.elhaddad@example.com',
                'password' => 'password',
                'age' => 26,
                'job_title' => 'PHP Backend Developer',
                'profile_description' => 'Backend developer with 4 years of experience building REST APIs with Laravel and MySQL.',
                'phone' => '+20 100 000 0001',
                'skills' => 'PHP, Laravel, MySQL, REST API, Git',
            ],
            [
                'name' => 'Ali Mohamed',
                'email' => 'ali.mohamed@example.com',
                'password' => 'password',
                'age' => 24,
                'job_title' => 'Frontend Developer',
                'profile_description' => 'Frontend developer focused on modern JavaScript frameworks and responsive UI.',
                'phone' => '+20 100 000 0002',
                'skills' => 'JavaScript, React, HTML, CSS, Tailwind CSS',
            ],
            [
                'name' => 'Basel Ashraf',
                'email' => 'basel.ashraf@example.com',
                'password' => 'password',
                'age' => 28,
                'job_title' => 'Full Stack Developer',
                'profile_description' => 'Full stack developer who enjoys building complete products from database to user interface.',
                'phone' => '+20 100 000 0003',
                'skills' => 'PHP, Laravel, Vue.js, MySQL, Docker',
            ],
            [
                'name' => 'Yousef Roshdy',
                'email' => 'yousef.roshdy@example.com',
                'password' => 'password',
                'age' => 23,
                'job_title' => 'UI/UX Designer',
                'profile_description' => 'Creative designer crafting clean and usable interfaces for web and mobile.',
                'phone' => '+20 100 000 0004',
                'skills' => 'Figma, Adobe XD, UI Design, UX Research, HTML, CSS',
            ],
            [
                'name' => 'Ahmed Elasayed',
                'email' => 'ahmed.elasayed@example.com',
                'password' => 'password',
                'age' => 30,
                'job_title' => 'Data Scientist',
                'profile_description' => 'Data scientist specializing in machine learning models and data analysis.',
                'phone' => '+20 100 000 0005',
                'skills' => 'Python, Machine Learning, SQL, Pandas, TensorFlow',
            ],
            [
                'name' => 'Salama Mohamed',
                'email' => 'salama.mohamed@example.com',
                'password' => 'password',
                'age' => 25,
                'job_title' => 'DevOps Engineer',
                'profile_description' => 'DevOps engineer automating deployments and managing cloud infrastructure.',
                'phone' => '+20 100 000 0006',
                'skills' => 'Docker, Kubernetes, AWS, CI/CD, Linux',
            ],
        ];

        foreach ($candidates as $candidate) {
            User::create($candidate);
        }
    }
}
