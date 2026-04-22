<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Submission>
 */
class SubmissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Str::uuid()->toString(),
            'requestor_id' => User::factory()->state(function () {
                return ['role_id' => Role::factory()->create(['name' => 'Requestor'])->id];
            }),
            'current_role_id' => Role::factory(),
            'status' => 'draft',
            'warehouse_name' => fake()->company(),
            'warehouse_address' => fake()->address(),
            'latitude' => fake()->latitude(-10, 10),
            'longitude' => fake()->longitude(95, 141),
            'budget_estimate' => fake()->randomFloat(2, 10000000, 1000000000),
            'description' => fake()->optional(0.8)->paragraph(),
            'rejected_by' => null,
            'rejection_reason' => null,
            'submitted_at' => null,
            'approved_at' => null,
        ];
    }

    public function pending(string $roleId): static
    {
        return $this->state(fn (array $attributes) => [
            'current_role_id' => $roleId,
            'status' => 'pending',
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'rejection_reason' => 'Test rejection reason',
        ]);
    }
}
