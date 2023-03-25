<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CreateRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::create(['name' => 'Admin', 'guard_name'=>'web']);
        $role = Role::create(['name' => 'Branch-Manager', 'guard_name'=>'web']);
        $role = Role::create(['name' => 'Project-Manager', 'guard_name'=>'web']);
        $role = Role::create(['name' => 'Team-Leader', 'guard_name'=>'web']);
        $role = Role::create(['name' => 'Sales-Executive', 'guard_name'=>'web']);
        $role = Role::create(['name' => 'Developer', 'guard_name'=>'web']);
        $role = Role::create(['name' => 'Designer', 'guard_name'=>'web']);
        $role = Role::create(['name' => 'Seo-Developer', 'guard_name'=>'web']);
        $role = Role::create(['name' => 'Accountant', 'guard_name'=>'web']);
    }
}
