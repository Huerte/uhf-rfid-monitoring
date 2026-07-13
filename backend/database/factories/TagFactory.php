<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Tag;
use App\Models\ScanSession;

class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        $protocols = ['epc', '6b', 'gb'];
        return [
            'scan_session_id' => ScanSession::factory(),
            'protocol' => $protocols[array_rand($protocols)],
            'epc' => strtoupper(bin2hex(random_bytes(12))),
            'tid' => strtoupper(bin2hex(random_bytes(12))),
            'user_data' => strtoupper(bin2hex(random_bytes(8))),
            'antenna' => rand(1, 4),
            'ant1' => 0,
            'ant2' => 0,
            'ant3' => 0,
            'ant4' => 0,
            'rssi' => round(rand(-9000, -3000) / 100, 2),
            'scanned_at' => now()->subDays(rand(0, 30)),
        ];
    }
}
