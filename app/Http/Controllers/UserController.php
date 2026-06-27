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

    public function index()
    {
        $query = User::with(['emp', 'departments.dept', 'roles'])
            ->where('deleted_at', null)
            ->where('id', '!=', '1')
            ->orderBy('id', 'desc');

        $users = app(\App\Services\BranchScopeService::class)
            ->applyBranchUserScope($query, Auth::user())
            ->paginate(25);

        return view('components.users.index', compact('users'));
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
            $user->status       = "Active";

            $user->code  = strtoupper($request->post('code'));
            $user->designation = ucfirst($request->post('designation'));

            $user->save();

            $role = \Spatie\Permission\Models\Role::findById((int) $request->post('role'));
            $this->assertValidRoleAssignment($role->name);
            $user->assignRole($role);

            $emp            = new Employees();
            $emp->user      = $user->id;
            $emp->name      = ucfirst($request->post('name'));
            $emp->gender    = $request->post('gender');
            $emp->dob       = $request->post('dob');
            $emp->joining_dt = $request->post('joining_date');
            $emp->mem_code  = strtoupper($request->post('code'));
            $emp->designation = ucfirst($request->post('designation'));
            $emp->status    = 'Active';
            $emp->created_by = Auth::user()->id;

            $emp->save();

            $userDept               = new UserDepartment();
            $userDept->user         = $user->id;
            $userDept->department   = $request->department;
            $userDept->save();

            $userBranch               = new UserBranch();
            $userBranch->user         = $user->id;
            $userBranch->branch       = DB::table('departments')->where('id', $request->department)->value('branchid');
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
            $user->status = $request->post('status') ?? 'Active';

            $user->save();

            $role = \Spatie\Permission\Models\Role::findById((int) $request->post('role'));
            $this->assertValidRoleAssignment($role->name);
            $user->syncRoles($role);

            $emp            = Employees::where('user', $user->id)->first();
            $emp->user      = $user->id;
            $emp->name      = ucfirst($request->post('name'));
            $emp->dob       = $request->post('dob');
            $emp->joining_dt = $request->post('joining_date');
            $emp->mem_code  = strtoupper($request->post('code'));
            $emp->designation = ucfirst($request->post('designation'));
            $emp->status    = $request->post('status') ?? 'Active';

            $emp->updated_by = Auth::user()->id;

            $emp->save();

            $userDept               = UserDepartment::where('user', $user->id)->first();
            $userDept->user         = $user->id;
            $userDept->department   = $request->department;
            $userDept->save();

            DB::commit();
            return redirect()->route('users.index')->with('success', 'Member updated successfully');
        } catch (Exception $ex) {
            DB::rollBack();
            return redirect()->back()->with('error', $ex->getMessage())->withInput();
            dd($ex->getMessage());
        }
    }





    public function destroy(Request $request, User $user)
    {
        $this->assertCanManageUser($user);

        $user->status = "Inactive";
        $user->save();
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User has been deleted');
    }

    public function changestatus(Request $request, $user_id)
    {
        $user = User::where('id', $user_id)->first();
        if ($user) {
            $this->assertCanManageUser($user);
            if ($user->status == 'Active') {
                $user->status = 'Inactive';
                $user->save();
            } else {
                $user->status = 'Active';
                $user->save();
            }
            return redirect()->route('users.index');
        } else {
            return redirect()->route('users.index');
        }
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

            $users = User::select('id', 'name')->where('status', 'Active')
                ->where('id', '!=', $client->ref_user)
                ->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['Sales-Executive', 'Team-Leader']);
                })
                ->whereIn('id', $allmem)->get()->toArray();
        } elseif ($loggedUser->isBranchManager()) {
            $branchScope = app(\App\Services\BranchScopeService::class);
            $salesIds = $branchScope->getBranchSalesUserIds($loggedUser);

            $users = User::select('id', 'name')->where('status', 'Active')
                ->where('id', '!=', $client->ref_user)
                ->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['Sales-Executive', 'Team-Leader', 'Branch-Manager']);
                })
                ->whereIn('id', $salesIds)->get()->toArray();
        } else {
            $users = User::select('id', 'name')->where('status', 'Active')
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
