<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    // ── Credentials (change password after first login!) ──────────────────
    const EMAIL    = 'superadmin@vital-crm.com';
    const USERNAME = 'super_admin';
    const PASSWORD = 'SuperAdmin@2025!';

    public function run(): void
    {
        // 1. Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Ensure super_admin role exists with ALL permissions
        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'api']);
        $role->syncPermissions(Permission::all());

        // 3. Create or update the super admin user
        $user = User::firstOrCreate(
            ['email' => self::EMAIL],
            [
                'username' => self::USERNAME,
                'password' => Hash::make(self::PASSWORD),
            ]
        );

        // 4. Assign role (idempotent — won't duplicate)
        $user->syncRoles(['super_admin']);

        $this->command->info("✅ Super Admin created/verified.");
        $this->command->table(
            ['Field', 'Value'],
            [
                ['Email',    self::EMAIL],
                ['Username', self::USERNAME],
                ['Password', self::PASSWORD . '  ← change after first login!'],
                ['Role',     'super_admin'],
                ['Permissions', $role->permissions()->count() . ' (all)'],
            ]
        );
    }
}
