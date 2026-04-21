<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles in reverse order to set next_role_id
        $direkturKeuangan = Role::create([
            'name' => 'Direktur Keuangan',
            'next_role_id' => null,
        ]);

        $direkturOps = Role::create([
            'name' => 'Direktur Operasional',
            'next_role_id' => $direkturKeuangan->id,
        ]);

        $managerOps = Role::create([
            'name' => 'Manager Operasional',
            'next_role_id' => $direkturOps->id,
        ]);

        $kepalaGudang = Role::create([
            'name' => 'Kepala Gudang',
            'next_role_id' => $managerOps->id,
        ]);

        $spvGudang = Role::create([
            'name' => 'SPV Gudang',
            'next_role_id' => $kepalaGudang->id,
        ]);

        Role::create([
            'name' => 'Requestor',
            'next_role_id' => $spvGudang->id,
        ]);
    }
}
