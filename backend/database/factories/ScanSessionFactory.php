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
        $status = $this->faker->randomElement(['running', 'stopped', 'error']);
        return [
            'reader_id' => Reader::factory(),
            'protocol' => $this->faker->randomElement(['epc', '6b', 'gb']),
            'antenna' => $this->faker->numberBetween(1, 4),
            'read_tid' => $this->faker->boolean(),
            'read_user_data' => $this->faker->boolean(),
            'status' => $status,
            'error_message' => $status === 'error' ? 'Connection lost' : null,
            'started_at' => $this->faker->dateTimeThisMonth(),
            'ended_at' => $status !== 'running' ? clone $this->faker->dateTimeThisMonth() : null,
        ];
    }
}
