<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepaermentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Department::create(
            ['branchid' => 1, 'name'=>'NSD', 'description'=>'National Sales Department', 'status'=>true]);
        $role = Department::create(
            ['branchid' => 1, 'name'=>'OD', 'description'=>'Operational Department', 'status'=>true]);
        $role = Department::create(
            ['branchid' => 1, 'name'=>'CSD', 'description'=>'Customer Service Department', 'status'=>true]);
    }
}
