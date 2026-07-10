<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ScanSession;
use App\Models\Reader;

class ScanSessionFactory extends Factory
{
    protected $model = ScanSession::class;

    public function definition(): array
    {
        $statuses = ['running', 'stopped', 'error'];
        $protocols = ['epc', '6b', 'gb'];
        $status = $statuses[array_rand($statuses)];
        return [
            'reader_id' => Reader::factory(),
            'protocol' => $protocols[array_rand($protocols)],
            'antenna' => rand(1, 4),
            'read_tid' => (bool) rand(0, 1),
            'read_user_data' => (bool) rand(0, 1),
            'status' => $status,
            'error_message' => $status === 'error' ? 'Connection lost' : null,
            'started_at' => now()->subDays(rand(0, 30)),
            'ended_at' => $status !== 'running' ? now()->subDays(rand(0, 29)) : null,
        ];
    }
}
