<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\Clients;
use App\Models\Department;
use App\Models\Employees;
use App\Models\Role;
use App\Models\TeamMembers;
use App\Models\User;
use App\Models\UserBranch;
use App\Models\UserDepartment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeUserMail;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $statusFilter = $request->query('status', 'all');

        $baseQuery = User::with(['emp', 'departments.dept', 'roles'])
            ->where('deleted_at', null)
            ->where('id', '!=', '1');

        $baseQuery = app(\App\Services\BranchScopeService::class)
            ->applyBranchUserScope($baseQuery, Auth::user());

        // Count metrics for quick filter tabs
        $statusCounts = [
            'all'       => (clone $baseQuery)->count(),
            'working'   => (clone $baseQuery)->whereIn('status', User::WORKING_STATUSES)->count(),
            'active'    => (clone $baseQuery)->where('status', User::STATUS_ACTIVE)->count(),
            'probation' => (clone $baseQuery)->where('status', User::STATUS_PROBATION)->count(),
            'notice'    => (clone $baseQuery)->where('status', User::STATUS_NOTICE_PERIOD)->count(),
            'suspended' => (clone $baseQuery)->where('status', User::STATUS_SUSPENDED)->count(),
            'separated' => (clone $baseQuery)->whereIn('status', User::SEPARATED_STATUSES)->count(),
        ];

        $query = clone $baseQuery;

        if ($statusFilter === 'working') {
            $query->whereIn('status', User::WORKING_STATUSES);
        } elseif ($statusFilter === 'active') {
            $query->where('status', User::STATUS_ACTIVE);
        } elseif ($statusFilter === 'probation') {
            $query->where('status', User::STATUS_PROBATION);
        } elseif ($statusFilter === 'notice') {
            $query->where('status', User::STATUS_NOTICE_PERIOD);
        } elseif ($statusFilter === 'suspended') {
            $query->where('status', User::STATUS_SUSPENDED);
        } elseif ($statusFilter === 'separated') {
            $query->whereIn('status', User::SEPARATED_STATUSES);
        } elseif (in_array($statusFilter, User::ALL_STATUSES, true)) {
            $query->where('status', $statusFilter);
        }

        $users = $query->orderBy('id', 'desc')->paginate(25);

        return view('components.users.index', compact('users', 'statusFilter', 'statusCounts'));
    }


    public function create()
    {
        $actor = Auth::user();
        $derpartments = Department::with('branch')->where('status', true)->orderBy('id', 'asc');

        if ($actor->isBranchManager() && $actor->branchId()) {
            $derpartments->where('branchid', $actor->branchId());
        }

        $derpartments = $derpartments->get();

        $roles = Role::where('name', '!=', 'Admin')->where('status', true)->orderBy('id', 'asc');

        if ($actor->isBranchManager()) {
            $roles->whereNotIn('name', ['Admin']);
        }

        $roles = $roles->get();

        return view('components.users.create', compact('derpartments', 'roles'));
    }


    public function store(UserStoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = new User();
            $user->name         = ucfirst($request->post('name'));
            $user->email        = $request->post('email');
            $user->mobile       = $request->post('mobile');
            $user->password     = Hash::make($request->post('password'));
            $initialStatus      = $request->post('status') ?? User::STATUS_ACTIVE;
            $user->status       = $initialStatus;

            $user->code  = strtoupper($request->post('code'));
            $user->designation = ucfirst($request->post('designation'));

            $user->save();

            $role = \Spatie\Permission\Models\Role::findById((int) $request->post('role'));
            $this->assertValidRoleAssignment($role->name);
            $user->assignRole($role);

            $isBranchManager = $role && $role->name === 'Branch-Manager';

            $emp            = new Employees();
            $emp->user      = $user->id;
            $emp->name      = ucfirst($request->post('name'));
            $emp->gender    = $request->post('gender');
            $emp->dob       = $request->post('dob');
            $emp->joining_dt = $request->post('joining_date');
            $emp->mem_code  = strtoupper($request->post('code'));
            $emp->designation = ucfirst($request->post('designation'));
            $emp->status    = $initialStatus;
            $emp->created_by = Auth::user()->id;

            $emp->save();

            if (!$isBranchManager && $request->filled('department')) {
                $userDept               = new UserDepartment();
                $userDept->user         = $user->id;
                $userDept->department   = $request->department;
                $userDept->save();
            }

            // Resolve branch from department, or fallback to creator's branch, or default branch
            $branchId = null;
            if ($request->filled('department')) {
                $branchId = DB::table('departments')->where('id', $request->department)->value('branchid');
            }
            if (!$branchId) {
                $branchId = Auth::user()->branchId() ?? DB::table('branches')->value('id') ?? 1;
            }

            $userBranch               = new UserBranch();
            $userBranch->user         = $user->id;
            $userBranch->branch       = $branchId;
            $userBranch->save();

            DB::commit();

            try {
                Mail::to($user->email)->send(new WelcomeUserMail($user, $request->post('password')));
            } catch (Exception $e) {
                // Log or ignore if email fails
            }

            return redirect()->route('users.index')->with('success', 'Member Added successfully');
        } catch (Exception $ex) {
            DB::rollBack();
            return redirect()->back()->with('error', $ex->getMessage())->withInput();
        }
    }


    public function edit(User $user)
    {
        $this->assertCanManageUser($user);

        $actor = Auth::user();
        $users = $user;

        $departments = Department::with('branch')->where('status', true)->orderBy('id', 'asc');

        if ($actor->isBranchManager() && $actor->branchId()) {
            $departments->where('branchid', $actor->branchId());
        }

        $departments = $departments->get();

        $roles = Role::where('name', '!=', 'Admin')->where('status', true)->orderBy('id', 'asc');

        if ($actor->isBranchManager()) {
            $roles->whereNotIn('name', ['Admin']);
        }

        $roles = $roles->get();

        return view('components.users.edit', compact('users', 'roles', 'departments'));
    }


    public function update(UserUpdateRequest $request, User $user)
    {
        $this->assertCanManageUser($user);

        try {

            DB::beginTransaction();

            $user->name = $request->post('name');
            $user->email  = $request->post('email');
            $user->mobile  = $request->post('mobile');
            $user->code  = strtoupper($request->post('code'));
            $user->designation = ucfirst($request->post('designation'));
            $newStatus = $request->post('status') ?? User::STATUS_ACTIVE;
            $user->status = $newStatus;
            $user->save();

            $role = \Spatie\Permission\Models\Role::findById((int) $request->post('role'));
            $this->assertValidRoleAssignment($role->name);
            $user->syncRoles($role);

            $emp            = Employees::where('user', $user->id)->first();
            if (!$emp) {
                $emp = new Employees();
                $emp->user = $user->id;
            }
            $emp->name        = ucfirst($request->post('name'));
            $emp->dob         = $request->post('dob');
            $emp->joining_dt  = $request->post('joining_date');
            $emp->mem_code    = strtoupper($request->post('code'));
            $emp->designation = ucfirst($request->post('designation'));
            $emp->status      = $newStatus;

            if ($request->filled('end_dt')) {
                $emp->end_dt = $request->post('end_dt');
            } elseif (in_array($newStatus, [User::STATUS_ACTIVE, User::STATUS_PROBATION], true)) {
                $emp->end_dt = null;
            }

            $emp->updated_by = Auth::user()->id;
            $emp->save();

            // If moved to a separated/non-working status, automatically close active timers
            if (in_array($newStatus, User::SEPARATED_STATUSES, true)) {
                \App\Models\GlobalAttendanceLog::where('userid', $user->id)
                    ->whereNull('endtime')
                    ->update(['endtime' => now(), 'status' => 'closed_admin']);
                \App\Models\TaskLog::where('userid', $user->id)
                    ->whereNull('endtime')
                    ->update(['endtime' => now()]);
            }

            $isBranchManager = $role && $role->name === 'Branch-Manager';

            $userDept = UserDepartment::where('user', $user->id)->first();
            if ($isBranchManager) {
                if ($userDept) {
                    $userDept->delete();
                }
            } else {
                if ($request->filled('department')) {
                    if (!$userDept) {
                        $userDept = new UserDepartment();
                    }
                    $userDept->user         = $user->id;
                    $userDept->department   = $request->department;
                    $userDept->save();
                }
            }

            // Update user branch as well
            $userBranch = UserBranch::where('user', $user->id)->first();
            if (!$userBranch) {
                $userBranch = new UserBranch();
                $userBranch->user = $user->id;
            }
            
            $branchId = null;
            if ($request->filled('department')) {
                $branchId = DB::table('departments')->where('id', $request->department)->value('branchid');
            }
            if (!$branchId) {
                $branchId = Auth::user()->branchId() ?? DB::table('branches')->value('id') ?? 1;
            }
            
            $userBranch->branch = $branchId;
            $userBranch->save();

            DB::commit();
            return redirect()->route('users.index')->with('success', "Member {$user->name} updated successfully (Status: {$newStatus})");
        } catch (Exception $ex) {
            DB::rollBack();
            return redirect()->back()->with('error', $ex->getMessage())->withInput();
        }
    }





    public function destroy(Request $request, User $user)
    {
        $this->assertCanManageUser($user);

        $user->status = User::STATUS_RESIGNED;
        $user->save();

        $emp = Employees::where('user', $user->id)->first();
        if ($emp) {
            $emp->status = User::STATUS_RESIGNED;
            $emp->end_dt = $emp->end_dt ?: now()->toDateString();
            $emp->save();
        }

        \App\Models\GlobalAttendanceLog::where('userid', $user->id)
            ->whereNull('endtime')
            ->update(['endtime' => now(), 'status' => 'closed_admin']);
        \App\Models\TaskLog::where('userid', $user->id)
            ->whereNull('endtime')
            ->update(['endtime' => now()]);

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User has been marked as Resigned and deleted');
    }

    public function changestatus(Request $request, $user_id)
    {
        $user = User::where('id', $user_id)->first();
        if (!$user) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'User not found.'], 404);
            }
            return redirect()->route('users.index')->with('error', 'User not found.');
        }

        $this->assertCanManageUser($user);

        // Determine target status
        if ($request->filled('status')) {
            $newStatus = $request->input('status');
        } else {
            // Legacy toggle fallback
            $newStatus = $user->status === User::STATUS_ACTIVE ? User::STATUS_SUSPENDED : User::STATUS_ACTIVE;
        }

        if (!in_array($newStatus, User::ALL_STATUSES, true)) {
            $newStatus = User::STATUS_ACTIVE;
        }

        $oldStatus = $user->status;
        $user->status = $newStatus;
        $user->save();

        $emp = Employees::where('user', $user->id)->first();
        if ($emp) {
            $emp->status = $newStatus;
            if ($request->filled('end_dt')) {
                $emp->end_dt = $request->input('end_dt');
            } elseif (in_array($newStatus, [User::STATUS_ACTIVE, User::STATUS_PROBATION], true)) {
                $emp->end_dt = null;
            }
            $emp->updated_by = Auth::id();
            $emp->save();
        }

        // If moved to a separated or suspended status, automatically close open timers
        if (in_array($newStatus, User::SEPARATED_STATUSES, true)) {
            \App\Models\GlobalAttendanceLog::where('userid', $user->id)
                ->whereNull('endtime')
                ->update(['endtime' => now(), 'status' => 'closed_admin']);
            \App\Models\TaskLog::where('userid', $user->id)
                ->whereNull('endtime')
                ->update(['endtime' => now()]);
        }

        $remarks = $request->input('remarks');
        \App\Models\UserActivity::log(
            'User Status Changed',
            "Changed status of {$user->name} from '{$oldStatus}' to '{$newStatus}'" . ($remarks ? " (Remarks: {$remarks})" : "")
        );

        $message = "Status of {$user->name} updated to {$newStatus}.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'status'  => $newStatus,
                'badge'   => $user->status_badge,
                'end_dt'  => $emp?->end_dt?->format('Y-m-d'),
            ]);
        }

        return redirect()->route('users.index')->with('success', $message);
    }


    public function getAllUserByRole(Request $request)
    {

        $loggedUser = Auth::user();

        $clientid = $request->client;
        $client = Clients::where('id', $clientid)->first();

        if ($loggedUser->hasRole('Team-Leader')) {
            $teams =  DB::table('team_members')->where('user', $loggedUser->id)->where('status', true)->pluck('team')->toArray();
            $allmem =  TeamMembers::with('users.roles')
                ->whereHas('users.roles', function ($query) {
                    $query->where('name', 'Sales-Executive');
                })
                ->whereIn('team', $teams)->where('status', true)
                ->pluck('user')->toArray();

            array_push($allmem, $loggedUser->id);

            $users = User::select('id', 'name')->whereIn('status', User::WORKING_STATUSES)
                ->where('id', '!=', $client->ref_user)
                ->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['Sales-Executive', 'Team-Leader']);
                })
                ->whereIn('id', $allmem)->get()->toArray();
        } elseif ($loggedUser->isBranchManager()) {
            $branchScope = app(\App\Services\BranchScopeService::class);
            $salesIds = $branchScope->getBranchSalesUserIds($loggedUser);

            $users = User::select('id', 'name')->whereIn('status', User::WORKING_STATUSES)
                ->where('id', '!=', $client->ref_user)
                ->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['Sales-Executive', 'Team-Leader', 'Branch-Manager']);
                })
                ->whereIn('id', $salesIds)->get()->toArray();
        } else {
            $users = User::select('id', 'name')->whereIn('status', User::WORKING_STATUSES)
                ->where('id', '!=', $client->ref_user)
                ->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['Sales-Executive', 'Team-Leader', 'Admin']);
                })
                ->whereIn('department', [5, 6])->get()->toArray();
        }
        if ($users)
            return response()->json(['status' => true, 'data' => $users]);
        else
            return response()->json(['status' => false, 'data' => $users]);
    }

    private function assertValidRoleAssignment(string $roleName): void
    {
        $actor = Auth::user();

        if ($actor->isBranchManager() && $roleName === 'Admin') {
            throw new Exception('Branch Managers cannot assign the Admin role.');
        }
    }

    private function assertCanManageUser(User $target): void
    {
        $actor = Auth::user();

        if ($actor->isGlobalAdmin()) {
            return;
        }

        if ($actor->isBranchManager()) {
            if ($target->hasRole('Admin')) {
                abort(403, 'Branch Managers cannot manage Admin users.');
            }

            $allowedIds = app(\App\Services\BranchScopeService::class)->getBranchUserIds($actor);

            if (!in_array($target->id, $allowedIds, true)) {
                abort(403, 'Unauthorized.');
            }

            return;
        }

        abort(403, 'Unauthorized.');
    }
}
