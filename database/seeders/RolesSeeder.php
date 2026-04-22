<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles in reverse order to set next_role_id
        $direkturKeuanganId = Str::uuid()->toString();
        $direkturOpsId = Str::uuid()->toString();
        $managerOpsId = Str::uuid()->toString();
        $kepalaGudangId = Str::uuid()->toString();
        $spvGudangId = Str::uuid()->toString();
        $requestorId = Str::uuid()->toString();

        Role::create([
            'id' => $direkturKeuanganId,
            'name' => 'Direktur Keuangan',
            'next_role_id' => null,
        ]);

        Role::create([
            'id' => $direkturOpsId,
            'name' => 'Direktur Operasional',
            'next_role_id' => $direkturKeuanganId,
        ]);

        Role::create([
            'id' => $managerOpsId,
            'name' => 'Manager Operasional',
            'next_role_id' => $direkturOpsId,
        ]);

        Role::create([
            'id' => $kepalaGudangId,
            'name' => 'Kepala Gudang',
            'next_role_id' => $managerOpsId,
        ]);

        Role::create([
            'id' => $spvGudangId,
            'name' => 'SPV Gudang',
            'next_role_id' => $kepalaGudangId,
        ]);

        Role::create([
            'id' => $requestorId,
            'name' => 'Requestor',
            'next_role_id' => $spvGudangId,
        ]);
    }
}
