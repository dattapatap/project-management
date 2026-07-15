<?php

namespace App\Http\Controllers\Csd;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Csd\Concerns\FormatsCsdClientColumn;
use App\Models\CsdClientAssignment;
use App\Models\ClientEngagement;
use App\Services\Csd\CsdClientResolverService;
use App\Services\Csd\CsdClientService;
use App\Services\CsdTeamScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class CsdClientController extends Controller
{
    use FormatsCsdClientColumn;

    public function __construct(
        private CsdClientService $service,
        private CsdClientResolverService $resolver,
        private CsdTeamScopeService $scope
    ) {}

    public function index()
    {
        $user = Auth::user();

        return view('components.csd.clients.index', [
            'executives' => $this->scope->getAllocatableExecutives($user),
            'canAssign'  => $this->scope->canAssignClients($user),
            'canDelete'  => $this->scope->canAssignClients($user), // same gate: Admin, BM, CSD-TL
        ]);
    }

    public function data(Request $request)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        return $this->withCsdClientName(
            DataTables::of($this->service->listQuery(Auth::user()))->addIndexColumn()
        )
            ->addColumn('project_name', function ($row) {
                $project = $row->relationLoaded('project') ? $row->getRelation('project') : null;

                return e($project?->project_name ?? '-');
            })
            ->addColumn('assignee_name', function ($row) {
                $assignee = $row->relationLoaded('assignee') ? $row->getRelation('assignee') : null;

                return e($assignee?->name ?? 'Unassigned');
            })
            ->editColumn('handoff_date', fn($row) => $row->handoff_date?->format('d M Y') ?? '-')
            ->editColumn('health_status', function ($row) {
                $badges = ['healthy' => 'success', 'at_risk' => 'warning', 'churning' => 'danger'];
                $class = $badges[$row->health_status] ?? 'secondary';

                return '<span class="badge badge-' . $class . '">' . ucfirst(str_replace('_', ' ', $row->health_status)) . '</span>';
            })
            ->editColumn('satisfaction_score', fn($row) => $row->satisfaction_score ? $row->satisfaction_score . '/10' : '-')
            ->addColumn('upsell_track', function ($row) {
                $eng = $row->relationLoaded('latestOpenUpsellEngagement')
                    ? $row->getRelation('latestOpenUpsellEngagement')
                    : null;
                if (!$eng) {
                    return '<span class="text-muted">—</span>';
                }

                $url = route('commercial.engagements.show', $eng->id);
                $badge = match ($eng->status) {
                    ClientEngagement::STATUS_WON_PENDING_COMMERCIAL => 'warning',
                    ClientEngagement::STATUS_IN_DELIVERY => 'info',
                    default => 'primary',
                };

                return '<a href="' . $url . '" class="font-weight-bold">' . e($eng->engagement_no) . '</a>'
                    . ' <span class="badge badge-soft-' . $badge . '">' . e($eng->statusLabel()) . '</span>';
            })
            ->addColumn('action', function ($row) {
                $user = Auth::user();
                $btns = '<button type="button" class="btn btn-sm btn-outline-primary editAssignment mr-1" data-id="' . $row->id . '"><i class="mdi mdi-pencil-outline"></i> Update</button>';

                if ($this->scope->canAssignClients($user)) {
                    $btns .= '<button type="button" class="btn btn-sm btn-outline-danger deleteAssignment" data-id="' . $row->id . '" data-client="' . e($row->client_name ?? '') . '"><i class="mdi mdi-delete-outline"></i> Remove</button>';
                }

                return $btns;
            })
            ->rawColumns(['health_status', 'action', 'upsell_track'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:department_projects,id',
            'assigned_to' => 'nullable|exists:users,id',
            'health_status' => 'required|in:healthy,at_risk,churning',
            'satisfaction_score' => 'nullable|integer|min:1|max:10',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'success' => false, 'errors' => $validator->errors()], 400);
        }

        try {
            $this->service->create($validator->validated(), Auth::user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['code' => 403, 'success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json(['code' => 200, 'success' => true, 'message' => 'Client assigned to CSD successfully.']);
    }

    public function update(Request $request, CsdClientAssignment $assignment)
    {
        $validator = Validator::make($request->all(), [
            'assigned_to' => 'nullable|exists:users,id',
            'health_status' => 'required|in:healthy,at_risk,churning',
            'satisfaction_score' => 'nullable|integer|min:1|max:10',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'success' => false, 'errors' => $validator->errors()], 400);
        }

        $this->service->update($assignment, $validator->validated(), Auth::user());

        return response()->json(['code' => 200, 'success' => true, 'message' => 'Assignment updated successfully.']);
    }

    public function destroy(CsdClientAssignment $assignment)
    {
        $user = Auth::user();

        if (!$this->scope->canAssignClients($user)) {
            return response()->json(['code' => 403, 'success' => false, 'message' => 'You do not have permission to remove CSD client assignments.'], 403);
        }

        $assignment->delete();

        return response()->json(['code' => 200, 'success' => true, 'message' => 'Client assignment removed successfully.']);
    }

    public function show(CsdClientAssignment $assignment)
    {
        $assignment->load(['client', 'project', 'assignee', 'communications.creator']);

        return response()->json([
            'success' => true,
            'assignment' => $assignment,
            'contacts' => $assignment->client
                ? \App\Models\CsdContactPerson::where('client', $assignment->client)->get()
                : [],
        ]);
    }

    public function activeClients()
    {
        return response()->json([
            'success' => true,
            'data' => $this->resolver->getSelectableClients(Auth::user()),
        ]);
    }

    public function storeContact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'is_primary' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'success' => false, 'errors' => $validator->errors()], 400);
        }

        try {
            $contact = $this->service->addContact($validator->validated(), Auth::user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['code' => 403, 'success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json(['code' => 200, 'success' => true, 'message' => 'Contact person added.', 'data' => $contact]);
    }
}
