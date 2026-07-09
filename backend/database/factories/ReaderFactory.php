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
            'name' => $this->faker->word() . ' Reader',
            'ip' => $this->faker->ipv4(),
            'port' => $this->faker->numberBetween(1000, 9999),
            'connected' => $this->faker->boolean(80),
            'connected_at' => $this->faker->dateTimeThisMonth(),
        ];
    }
}
