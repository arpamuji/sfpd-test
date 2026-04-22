<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Str::uuid()->toString(),
            'name' => $this->faker->randomElement([
                'Requestor',
                'SPV Gudang',
                'Kepala Gudang',
                'Manager Operasional',
                'Direktur Operasional',
                'Direktur Keuangan',
            ]),
            'next_role_id' => null,
        ];
    }

    public function withNextRole(string $nextRoleId): static
    {
        return $this->state(fn (array $attributes) => [
            'next_role_id' => $nextRoleId,
        ]);
    }
}
