<?php

namespace App\Http\Controllers\Csd;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Csd\Concerns\FormatsCsdClientColumn;
use App\Models\CsdOpportunity;
use App\Services\Csd\CsdClientResolverService;
use App\Services\Csd\CsdOpportunityService;
use App\Services\CsdTeamScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class CsdOpportunityController extends Controller
{
    use FormatsCsdClientColumn;

    public function __construct(
        private CsdOpportunityService $service,
        private CsdClientResolverService $resolver,
        private CsdTeamScopeService $scope
    ) {}

    public function index()
    {
        $user = Auth::user();

        $nsdReps = \App\Models\User::where('status', 'Active')
            ->where(function ($q) {
                $q->whereHas('roles', function ($r) {
                    $r->where('name', 'Sales-Executive');
                })
                    ->orWhere(function ($q2) {
                        $q2->whereHas('roles', function ($r) {
                            $r->where('name', 'Team-Leader');
                        })->whereHas('departments', function ($d) {
                            $d->where('department', 1); // NSD
                        });
                    });
            })
            ->orderBy('name', 'asc')
            ->get();

        return view('components.csd.opportunities.index', [
            'clients' => $this->resolver->getSelectableClients($user),
            'executives' => $this->scope->getAllocatableExecutives($user),
            'canAssignToOthers' => $this->scope->canAssignToOthers($user),
            'nsdReps' => $nsdReps,
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
            ->editColumn('type', fn($row) => ucfirst(str_replace('_', ' ', $row->type)))
            ->editColumn('estimated_value', fn($row) => $row->estimated_value ? '₹ ' . number_format($row->estimated_value, 2) : '-')
            ->editColumn('status', function ($row) {
                if ($row->status === 'won_pending_assignment') {
                    return '<span class="badge badge-warning">Awaiting Sales Assignment</span>';
                }
                $badgeClass = $row->status === 'won' ? 'success' : ($row->status === 'lost' ? 'danger' : 'info');
                return '<span class="badge badge-' . $badgeClass . '">' . ucfirst(str_replace('_', ' ', $row->status)) . '</span>';
            })
            ->addColumn('assignee_name', fn($row) => e($row->assignee->name ?? 'Unassigned'))
            ->addColumn('engagement_no', fn($row) => $row->engagement_id
                ? '<a href="' . route('commercial.engagements.show', $row->engagement_id) . '">' . e($row->engagement?->engagement_no ?? 'View') . '</a>'
                : '—')
            ->addColumn('action', function ($row) {
                $user = Auth::user();
                if ($user) {
                    $user->loadMissing('departments');
                }
                $isManagerOrTL = $user->hasRole(['Admin', 'Branch-Manager']) ||
                    ($user->hasRole('Team-Leader') && optional($user->departments)->department == 3);

                if ($row->status === 'won' && $row->engagement_id) {
                    return '<a href="' . route('commercial.engagements.show', $row->engagement_id) . '" class="btn btn-sm btn-outline-success">'
                        . '<i class="mdi mdi-file-tree"></i> View Order</a>';
                }

                if ($row->status === 'won_pending_assignment') {
                    if ($isManagerOrTL) {
                        return '<button type="button" class="btn btn-sm btn-primary btnAssignNsd" data-id="' . $row->id . '" data-title="' . e($row->title) . '" data-client="' . e($row->clients->name ?? '') . '">'
                            . '<i class="mdi mdi-account-arrow-right"></i> Assign to NSD</button>';
                    }
                    return '<span class="text-muted font-italic small">Awaiting allocation</span>';
                }

                if ($row->status === 'lost') {
                    return '<span class="text-muted small">Closed</span>';
                }

                return '<button type="button" class="btn btn-sm btn-outline-primary editOpportunity" data-id="' . $row->id . '">'
                    . '<i class="mdi mdi-pencil-outline"></i> Update</button>';
            })
            ->rawColumns(['status', 'action', 'engagement_no'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client' => 'required|exists:clients,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:upsell,cross_sell',
            'estimated_value' => 'nullable|numeric|min:0',
            'status' => 'required|in:identified,proposed,won,lost,won_pending_assignment',
            'followup_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'success' => false, 'errors' => $validator->errors()], 400);
        }

        try {
            $data = $validator->validated();
            if ($data['status'] === 'won') {
                $data['status'] = 'won_pending_assignment';
            }
            $this->service->create($data, Auth::user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['code' => 403, 'success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json(['code' => 200, 'success' => true, 'message' => 'Opportunity recorded.']);
    }

    public function update(Request $request, CsdOpportunity $opportunity)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:upsell,cross_sell',
            'estimated_value' => 'nullable|numeric|min:0',
            'status' => 'required|in:identified,proposed,won,lost,won_pending_assignment',
            'followup_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'success' => false, 'errors' => $validator->errors()], 400);
        }

        try {
            $data = $validator->validated();
            if ($data['status'] === 'won') {
                $data['status'] = 'won_pending_assignment';
            }
            $this->service->update($opportunity, $data, Auth::user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['code' => 403, 'success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json(['code' => 200, 'success' => true, 'message' => 'Opportunity updated.']);
    }

    public function show(CsdOpportunity $opportunity)
    {
        $opportunity->load(['clients', 'assignee']);

        return response()->json(['success' => true, 'data' => $opportunity]);
    }

    public function assignNsd(Request $request, CsdOpportunity $opportunity)
    {
        $user = Auth::user();
        if ($user) {
            $user->loadMissing('departments');
        }
        $isManagerOrTL = $user->hasRole(['Admin', 'Branch-Manager']) ||
            ($user->hasRole('Team-Leader') && optional($user->departments)->department == 3);

        if (!$isManagerOrTL) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Only Admin, Branch Manager, or CSD Team Leader can assign to NSD.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'sales_rep_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->all()], 400);
        }

        $salesRepId = (int) $request->input('sales_rep_id');
        $salesRep = \App\Models\User::where('id', $salesRepId)->where('status', 'Active')->first();
        if (!$salesRep) {
            return response()->json(['success' => false, 'message' => 'Selected Sales Representative is inactive or invalid.'], 400);
        }

        try {
            $this->service->assignToSales($opportunity, $salesRepId, $user);
            return response()->json(['success' => true, 'message' => 'CSD Opportunity successfully assigned to NSD Representative.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
