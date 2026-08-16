<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions needed for the test
        Permission::firstOrCreate(['name' => 'create-user', 'guard_name' => 'api']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $adminRole->givePermissionTo('create-user');

        Role::firstOrCreate(['name' => 'agent', 'guard_name' => 'api']);
    }

    public function test_unauthenticated_user_cannot_create_user()
    {
        $response = $this->postJson('/api/users', [
            'username' => 'newuser',
            'email'    => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles'    => ['agent'],
        ]);

        $response->assertUnauthorized();
    }

    public function test_user_without_permission_cannot_create_user()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->postJson('/api/users', [
            'username' => 'newuser',
            'email'    => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles'    => ['agent'],
        ], ['Authorization' => "Bearer {$token}"]);

        $response->assertForbidden();
    }

    public function test_user_with_permission_can_create_user_and_assign_role()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $token = JWTAuth::fromUser($admin);

        $response = $this->postJson('/api/users', [
            'username' => 'newagent',
            'email'    => 'newagent@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles'    => ['agent'],
        ], ['Authorization' => "Bearer {$token}"]);

        $response->assertCreated()
                 ->assertJsonFragment([
                     'username' => 'newagent',
                     'email'    => 'newagent@example.com',
                 ]);

        // Check if the user exists in DB
        $this->assertDatabaseHas('users', [
            'email' => 'newagent@example.com',
        ]);

        // Check if role is assigned
        $newUser = User::where('email', 'newagent@example.com')->first();
        $this->assertTrue($newUser->hasRole('agent'));
    }

    public function test_cannot_assign_non_existent_role()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $token = JWTAuth::fromUser($admin);

        $response = $this->postJson('/api/users', [
            'username' => 'newagent',
            'email'    => 'newagent@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles'    => ['fake-role'],
        ], ['Authorization' => "Bearer {$token}"]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['roles.0']);
    }
}
