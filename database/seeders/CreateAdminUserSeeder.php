<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserDepartment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'digitalnock@gmail.com',
            'mobile' => '8095672827',
            'status' => 'Active',
            'password' => Hash::make('123456'),
            'designation' => 'Admin',
            'code' => 'DIS0001'
        ]);

        $role = Role::where('name','Admin')->first();
        $user->assignRole([$role->id]);

    }
}
