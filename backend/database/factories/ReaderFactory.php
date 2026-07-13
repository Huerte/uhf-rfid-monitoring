<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Reader;

class ReaderFactory extends Factory
{
    protected $model = Reader::class;

    public function definition(): array
    {
        return [
            'name' => 'Reader ' . rand(1, 999),
            'ip' => rand(192, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(1, 254),
            'port' => rand(1000, 9999),
            'connected' => (rand(1, 100) <= 80),
            'connected_at' => now()->subDays(rand(0, 30)),
        ];
    }
} 
