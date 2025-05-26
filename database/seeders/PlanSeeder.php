<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Perfect for getting started',
                'monthly_price' => 0,
                'yearly_price' => 0,
                'features' => [
                    'Up to 5 URL lists',
                    '50 URLs per list',
                    'Basic Analytics',
                    'Public & Private Lists',
                ],
                'max_lists' => 5,
                'max_urls_per_list' => 50,
                'max_team_members' => 0,
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Great for professionals',
                'monthly_price' => 9.99,
                'yearly_price' => 99.99,
                'features' => [
                    'Unlimited URL lists',
                    '500 URLs per list',
                    'Advanced Analytics',
                    'Team Collaboration',
                    'Custom Branding',
                    'Priority Support',
                ],
                'max_lists' => -1, // unlimited
                'max_urls_per_list' => 500,
                'max_team_members' => 5,
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'For large teams and organizations',
                'monthly_price' => 29.99,
                'yearly_price' => 299.99,
                'features' => [
                    'Everything in Pro',
                    'Unlimited URLs per list',
                    'Unlimited team members',
                    'API Access',
                    'Custom Integration',
                    'Dedicated Support',
                    'SLA',
                ],
                'max_lists' => -1, // unlimited
                'max_urls_per_list' => -1, // unlimited
                'max_team_members' => -1, // unlimited
                'is_active' => true,
                'is_featured' => false,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }
}
