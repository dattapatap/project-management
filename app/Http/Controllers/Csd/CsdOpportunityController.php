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
    ) {
    }

    public function index()
    {
        $user = Auth::user();

        return view('components.csd.opportunities.index', [
            'clients' => $this->resolver->getSelectableClients($user),
            'executives' => $this->scope->getAllocatableExecutives($user),
            'canAssignToOthers' => $this->scope->canAssignToOthers($user),
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
            ->editColumn('type', fn ($row) => ucfirst(str_replace('_', ' ', $row->type)))
            ->editColumn('estimated_value', fn ($row) => $row->estimated_value ? '₹ ' . number_format($row->estimated_value, 2) : '-')
            ->editColumn('status', fn ($row) => '<span class="badge badge-info">' . ucfirst($row->status) . '</span>')
            ->addColumn('assignee_name', fn ($row) => e($row->assignee->name ?? 'Unassigned'))
            ->addColumn('engagement_no', fn ($row) => $row->engagement_id
                ? '<a href="' . route('commercial.engagements.show', $row->engagement_id) . '">' . e($row->engagement?->engagement_no ?? 'View') . '</a>'
                : '—')
            ->addColumn('action', function ($row) {
                if ($row->status === 'won' && $row->engagement_id) {
                    return '<a href="' . route('commercial.engagements.show', $row->engagement_id) . '" class="btn btn-sm btn-outline-success">'
                        . '<i class="mdi mdi-file-tree"></i> View Order</a>';
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
            'status' => 'required|in:identified,proposed,won,lost',
            'followup_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'success' => false, 'errors' => $validator->errors()], 400);
        }

        try {
            $this->service->create($validator->validated(), Auth::user());
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
            'status' => 'required|in:identified,proposed,won,lost',
            'followup_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'success' => false, 'errors' => $validator->errors()], 400);
        }

        try {
            $this->service->update($opportunity, $validator->validated(), Auth::user());
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
}
