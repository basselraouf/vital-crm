<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // -----------------------------------------------
        // Define all permissions grouped by CRM module
        // -----------------------------------------------
        $permissions = [
            // Leads
            'view-leads',
            'create-lead',
            'edit-lead',
            'delete-lead',
            'assign-lead',
            'export-leads',

            // Patients
            'view-patients',
            'create-patient',
            'edit-patient',
            'delete-patient',

            // Pipeline / Kanban
            'view-pipeline',
            'move-pipeline-stage',

            // Consultations
            'view-consultations',
            'create-consultation',
            'edit-consultation',
            'delete-consultation',

            // Travel & Logistics
            'view-travel',
            'create-travel',
            'edit-travel',

            // Communications
            'view-communications',
            'send-message',

            // Quotes & Packages
            'view-quotes',
            'create-quote',
            'edit-quote',
            'delete-quote',
            'send-quote',

            // Services Catalog
            'view-services',
            'create-service',
            'edit-service',
            'delete-service',

            // Doctors & Partners
            'view-doctors',
            'create-doctor',
            'edit-doctor',
            'delete-doctor',

            // Reports & Analytics
            'view-reports',
            'export-reports',

            // User Management
            'view-users',
            'create-user',
            'edit-user',
            'delete-user',
            'assign-role',

            // Role Management
            'view-roles',
            'create-role',
            'edit-role',
            'delete-role',

            // Blog Management
            'view-blogs',
            'create-blog',
            'edit-blog',
            'delete-blog',
            'manage-blog-categories',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
        }

        // -----------------------------------------------
        // Create roles and assign permissions
        // -----------------------------------------------

        // SUPER ADMIN — identical to owner but named separately for the CRM
        // Always syncs ALL permissions (including any newly added ones)
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'api']);
        $superAdmin->syncPermissions(Permission::all());

        // OWNER — full unrestricted access
        $owner = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'api']);
        $owner->syncPermissions(Permission::all());

        // ADMIN — everything except role deletion
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $admin->syncPermissions(
            Permission::whereNotIn('name', ['delete-role'])->get()
        );

        // AGENT — lead/patient/pipeline/consultation/quote/communication work
        $agent = Role::firstOrCreate(['name' => 'agent', 'guard_name' => 'api']);
        $agent->syncPermissions([
            'view-leads', 'create-lead', 'edit-lead', 'assign-lead',
            'view-patients', 'create-patient', 'edit-patient',
            'view-pipeline', 'move-pipeline-stage',
            'view-consultations', 'create-consultation', 'edit-consultation',
            'view-travel', 'create-travel', 'edit-travel',
            'view-communications', 'send-message',
            'view-quotes', 'create-quote', 'edit-quote', 'send-quote',
            'view-services', 'view-doctors',
        ]);

        // DOCTOR — view their consultations and assigned patients only
        $doctor = Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'api']);
        $doctor->syncPermissions([
            'view-consultations',
            'view-patients',
            'view-leads',
        ]);

        // VIEWER — read-only across the board
        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'api']);
        $viewer->syncPermissions([
            'view-leads',
            'view-patients',
            'view-pipeline',
            'view-consultations',
            'view-quotes',
            'view-reports',
            'view-services',
            'view-doctors',
            'view-users',
        ]);

        $this->command->info('✅ Roles and permissions seeded successfully.');
        $this->command->table(
            ['Role', 'Permission Count'],
            [
                ['super_admin', $superAdmin->permissions()->count()],
                ['owner',       $owner->permissions()->count()],
                ['admin',       $admin->permissions()->count()],
                ['agent',       $agent->permissions()->count()],
                ['doctor',      $doctor->permissions()->count()],
                ['viewer',      $viewer->permissions()->count()],
            ]
        );
    }
}
