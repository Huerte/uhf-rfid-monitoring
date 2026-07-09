<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $readers = \App\Models\Reader::factory(3)->create();

        foreach ($readers as $reader) {
            $sessions = \App\Models\ScanSession::factory(5)->create([
                'reader_id' => $reader->id
            ]);

            foreach ($sessions as $session) {
                \App\Models\Tag::factory(10)->create([
                    'scan_session_id' => $session->id
                ]);
            }
        }
    }
}
