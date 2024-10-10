<?php

namespace Database\Seeders;

use App\Models\Interest;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        $interest = [
            'Comedy', 'Entertainment Culture', 'Dance', 'Gaming', 'Daily life', 'Food & Drink',
            'Music', 'Sports', 'Auto & Vehicle', 'Science & Education', 'Animals', 'Family', 'Beauty & Style',
            'Fitness & Health', 'Travle'
        ];
        foreach ($interest as $i) {
            Interest::create([
                'title' => $i,
            ]);
        }
    }
}