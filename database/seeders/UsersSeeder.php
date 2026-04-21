<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    private const TEST_2FA_SECRETS = [
        'requestor' => 'JBSWY3DPEHPK3PXP',
        'spv' => 'KRSXG5CTMVRXEZLU',
        'kepala' => 'GEZDGNBVGY3TQOJQ',
        'manager' => 'MFRGGZDFMY2TQNZZ',
        'direktur_ops' => 'OVSG433SMVZWKZTH',
        'direktur_keuangan' => 'KRSXG5CTMVRXEZTB',
    ];

    public function run(): void
    {
        $roles = Role::all()->keyBy('name');
        $password = Hash::make('password123');

        $users = [
            ['email' => 'requestor@test.com', 'role' => 'Requestor', 'secret' => self::TEST_2FA_SECRETS['requestor']],
            ['email' => 'spv@test.com', 'role' => 'SPV Gudang', 'secret' => self::TEST_2FA_SECRETS['spv']],
            ['email' => 'kepala@test.com', 'role' => 'Kepala Gudang', 'secret' => self::TEST_2FA_SECRETS['kepala']],
            ['email' => 'manager@test.com', 'role' => 'Manager Operasional', 'secret' => self::TEST_2FA_SECRETS['manager']],
            ['email' => 'direktur.ops@test.com', 'role' => 'Direktur Operasional', 'secret' => self::TEST_2FA_SECRETS['direktur_ops']],
            ['email' => 'direktur.keuangan@test.com', 'role' => 'Direktur Keuangan', 'secret' => self::TEST_2FA_SECRETS['direktur_keuangan']],
        ];

        foreach ($users as $userData) {
            User::create([
                'id' => Str::uuid()->toString(),
                'email' => $userData['email'],
                'password' => $password,
                'role_id' => $roles[$userData['role']]->id,
                'google2fa_secret' => $userData['secret'],
                'google2fa_enabled' => true,
            ]);
        }

        $this->command->info("2FA Secrets for testing:");
        foreach ($users as $userData) {
            $this->command->info("  {$userData['role']}: {$userData['secret']}");
        }
    }
}
