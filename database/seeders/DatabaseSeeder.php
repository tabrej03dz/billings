<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

//        User::factory()->create([
//            'name' => 'Test User',
//            'email' => 'test@example.com',
//        ]);

        $business = Business::create([
            'name' => 'Real Victory Groups',
            'slug' => 'real-victory-groups',
            'email' => 'info@realvictorygroups.com',
            'mobile' => '7753800444',
            'gstin' => '09CYMPP9152J2ZK',
            'address' => '73 - Basement, Ekta Enclave Society, Lakhanpur, Kanpur (208024), Kanpur Nagar, Uttar Pradesh - 208024',
        ]);

        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'super@admin.com',
            'password' => Hash::make('password'),
        ]);

        $superAdminRole = Role::create(['name' => 'super admin']);
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);

        $user->assignRole($superAdminRole);

        Permission::create(['name' => 'show businesses']);
        Permission::create(['name' => 'create business']);
        Permission::create(['name' => 'edit business']);
        Permission::create(['name' => 'delete business']);
        Permission::create(['name' => 'show users']);
        Permission::create(['name' => 'create user']);
        Permission::create(['name' => 'edit user']);
        Permission::create(['name' => 'delete user']);
        Permission::create(['name' => 'show clients']);
        Permission::create(['name' => 'create client']);
        Permission::create(['name' => 'edit client']);
        Permission::create(['name' => 'delete client']);
        Permission::create(['name' => 'show invoices']);
        Permission::create(['name' => 'create invoice']);
        Permission::create(['name' => 'download invoice']);
        Permission::create(['name' => 'show permissions']);
        Permission::create(['name' => 'assign permissions']);

        $permissions = Permission::all();
        $superAdminRole->givePermissionTo($permissions);


    }
}
