<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UrlList;
use App\Models\Url;
use App\Models\User;

class DemoUrlListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();
        $list = UrlList::firstOrCreate([
            'custom_url' => 'demo',
        ], [
            'user_id' => $user->id,
            'name' => 'Sample Demo List',
            'published' => true,
        ]);

        $urls = [
            [
                'url' => 'https://laravel.com',
                'title' => 'Laravel',
                'description' => 'The PHP Framework for Web Artisans.'
            ],
            [
                'url' => 'https://livewire.laravel.com',
                'title' => 'Livewire',
                'description' => 'A full-stack framework for Laravel.'
            ],
            [
                'url' => 'https://github.com',
                'title' => 'GitHub',
                'description' => 'Where the world builds software.'
            ],
        ];

        foreach ($urls as $data) {
            Url::firstOrCreate([
                'url_list_id' => $list->id,
                'url' => $data['url'],
            ], [
                'title' => $data['title'],
                'description' => $data['description'],
            ]);
        }
    }
}
