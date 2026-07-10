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
        return [
            'scan_session_id' => ScanSession::factory(),
            'protocol' => $this->faker->randomElement(['epc', '6b', 'gb']),
            'epc' => strtoupper($this->faker->regexify('[A-F0-9]{24}')),
            'tid' => strtoupper($this->faker->regexify('[A-F0-9]{24}')),
            'user_data' => strtoupper($this->faker->regexify('[A-F0-9]{16}')),
            'antenna' => $this->faker->numberBetween(1, 4),
            'ant1' => $this->faker->numberBetween(0, 50),
            'ant2' => $this->faker->numberBetween(0, 50),
            'ant3' => $this->faker->numberBetween(0, 50),
            'ant4' => $this->faker->numberBetween(0, 50),
            'rssi' => $this->faker->randomFloat(2, -90, -30),
            'scanned_at' => $this->faker->dateTimeThisMonth(),
        ];
    }
}
