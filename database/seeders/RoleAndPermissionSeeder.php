<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        Permission::create(['name' => 'admin']);
        Permission::create(['name' => 'waiter']);
        Permission::create(['name' => 'cashier']);
        Permission::create(['name' => 'report']);
        
        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'waiter']);
        Role::create(['name' => 'cashier']);

        // Assign permissions to roles
        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo(Permission::all());

        $waiterRole = Role::findByName('waiter');
        $waiterRole->givePermissionTo(['waiter']);

        $cashierRole = Role::findByName('cashier');
        $cashierRole->givePermissionTo(['cashier', 'report']);
    }
}
