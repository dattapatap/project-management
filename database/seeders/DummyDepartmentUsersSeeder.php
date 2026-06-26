<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employees;
use App\Models\User;
use App\Models\UserBranch;
use App\Models\UserDepartment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DummyDepartmentUsersSeeder extends Seeder
{
    /**
     * Demo users for NSD, OD, and CSD — one row per role type per department.
     * Default password for all: Password@123
     */
    public function run(): void
    {
        $password = Hash::make('Password@123');
        $adminId = User::query()->orderBy('id')->value('id') ?? 1;

        $departments = Department::query()
            ->whereIn('name', ['NSD', 'OD', 'CSD'])
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('name');

        if ($departments->count() < 3) {
            $this->command?->error('NSD, OD, and CSD departments must exist. Run DepaermentSeeder first.');
            return;
        }

        $users = [
            // NSD — Sales
            [
                'department' => 'NSD',
                'name' => 'Sarah NSD Manager',
                'email' => 'nsd.manager@digitalnock.test',
                'mobile' => '9800001001',
                'code' => 'NSD0001',
                'designation' => 'Branch Manager',
                'role' => 'Branch-Manager',
                'gender' => 'Female',
            ],
            [
                'department' => 'NSD',
                'name' => 'Rahul NSD Leader',
                'email' => 'nsd.tl@digitalnock.test',
                'mobile' => '9800001002',
                'code' => 'NSD0002',
                'designation' => 'Team Leader',
                'role' => 'Team-Leader',
                'gender' => 'Male',
            ],
            [
                'department' => 'NSD',
                'name' => 'Priya Sales',
                'email' => 'nsd.sales@digitalnock.test',
                'mobile' => '9800001003',
                'code' => 'NSD0003',
                'designation' => 'Sales Executive',
                'role' => 'Sales-Executive',
                'gender' => 'Female',
            ],
            // OD — Operations
            [
                'department' => 'OD',
                'name' => 'Amit Project Manager',
                'email' => 'od.pm@digitalnock.test',
                'mobile' => '9800002001',
                'code' => 'OD0001',
                'designation' => 'Project Manager',
                'role' => 'Project-Manager',
                'gender' => 'Male',
            ],
            [
                'department' => 'OD',
                'name' => 'Neha OD Leader',
                'email' => 'od.tl@digitalnock.test',
                'mobile' => '9800002002',
                'code' => 'OD0002',
                'designation' => 'Team Leader',
                'role' => 'Team-Leader',
                'gender' => 'Female',
            ],
            [
                'department' => 'OD',
                'name' => 'Karan Developer',
                'email' => 'od.dev@digitalnock.test',
                'mobile' => '9800002003',
                'code' => 'OD0003',
                'designation' => 'Developer',
                'role' => 'Developer',
                'gender' => 'Male',
            ],
            [
                'department' => 'OD',
                'name' => 'Ananya Designer',
                'email' => 'od.designer@digitalnock.test',
                'mobile' => '9800002004',
                'code' => 'OD0004',
                'designation' => 'Designer',
                'role' => 'Designer',
                'gender' => 'Female',
            ],
            [
                'department' => 'OD',
                'name' => 'Rohit SEO',
                'email' => 'od.seo@digitalnock.test',
                'mobile' => '9800002005',
                'code' => 'OD0005',
                'designation' => 'SEO Developer',
                'role' => 'Seo-Developer',
                'gender' => 'Male',
            ],
            [
                'department' => 'OD',
                'name' => 'Meera Accountant',
                'email' => 'od.accounts@digitalnock.test',
                'mobile' => '9800002006',
                'code' => 'OD0006',
                'designation' => 'Accountant',
                'role' => 'Accountant',
                'gender' => 'Female',
            ],
            // CSD — Customer Success
            [
                'department' => 'CSD',
                'name' => 'Vikram CSD Manager',
                'email' => 'csd.manager@digitalnock.test',
                'mobile' => '9800003001',
                'code' => 'CSD0001',
                'designation' => 'Branch Manager',
                'role' => 'Branch-Manager',
                'gender' => 'Male',
            ],
            [
                'department' => 'CSD',
                'name' => 'Deepa CSD Leader',
                'email' => 'csd.tl@digitalnock.test',
                'mobile' => '9800003002',
                'code' => 'CSD0002',
                'designation' => 'Team Leader',
                'role' => 'Team-Leader',
                'gender' => 'Female',
            ],
            [
                'department' => 'CSD',
                'name' => 'Arjun CSD Executive',
                'email' => 'csd.exec@digitalnock.test',
                'mobile' => '9800003003',
                'code' => 'CSD0003',
                'designation' => 'CSD Executive',
                'role' => 'CSD-Executive',
                'gender' => 'Male',
            ],
        ];

        DB::transaction(function () use ($users, $departments, $password, $adminId) {
            foreach ($users as $row) {
                $department = $departments->get($row['department']);
                if (!$department) {
                    continue;
                }

                $role = Role::where('name', $row['role'])->where('guard_name', 'web')->first();
                if (!$role) {
                    $this->command?->warn("Role {$row['role']} not found — skipping {$row['email']}");
                    continue;
                }

                $user = User::withTrashed()->where('email', $row['email'])->first();
                if ($user?->trashed()) {
                    $user->restore();
                }

                if (!$user) {
                    $user = new User();
                    $user->email = $row['email'];
                }

                $user->name = $row['name'];
                $user->mobile = $row['mobile'];
                $user->password = $password;
                $user->status = 'Active';
                $user->code = $row['code'];
                $user->designation = $row['designation'];
                $user->save();

                $user->syncRoles([$role->name]);

                $employee = Employees::where('user', $user->id)->first();
                if (!$employee) {
                    $employee = new Employees();
                    $employee->user = $user->id;
                }

                $employee->name = $row['name'];
                $employee->gender = $row['gender'];
                $employee->dob = Carbon::parse('1990-06-15');
                $employee->joining_dt = Carbon::now()->subMonths(6)->toDateString();
                $employee->mem_code = $row['code'];
                $employee->designation = $row['designation'];
                $employee->status = 'Active';
                $employee->created_by = $adminId;
                $employee->save();

                UserDepartment::updateOrCreate(
                    ['user' => $user->id],
                    ['department' => $department->id]
                );

                UserBranch::updateOrCreate(
                    ['user' => $user->id],
                    ['branch' => $department->branchid]
                );

                $this->command?->info("{$row['department']} / {$row['role']}: {$row['email']}");
            }
        });

        $this->command?->newLine();
        $this->command?->info('Done. All demo users use password: Password@123');
    }
}
