<?php

namespace Database\Factories;

use App\Models\Handover;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Handover>
 */
class HandoverFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => 'SRY-'.fake()->unique()->numerify('##########'),
            'type' => 'PENYERAHAN',
            'sender_id' => User::factory(),
            'receiver_id' => User::factory(),
            'status' => 'MENUNGGU_KONFIRMASI',
            'transaction_date' => now()->toDateString(),
        ];
    }
}
