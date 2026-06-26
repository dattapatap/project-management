<?php

namespace App\Http\Controllers\Csd;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Csd\Concerns\FormatsCsdClientColumn;
use App\Models\CsdCollectionFollowup;
use App\Services\Csd\CsdClientResolverService;
use App\Services\Csd\CsdCollectionService;
use App\Services\CsdTeamScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class CsdCollectionController extends Controller
{
    use FormatsCsdClientColumn;

    public function __construct(
        private CsdCollectionService $service,
        private CsdClientResolverService $resolver,
        private CsdTeamScopeService $scope
    ) {
    }

    public function index()
    {
        $user = Auth::user();

        return view('components.csd.collections.index', [
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
            ->addColumn('assignee_name', fn ($row) => e($row->assignee->name ?? 'Unassigned'))
            ->editColumn('amount_due', fn ($row) => '₹ ' . number_format($row->amount_due, 2))
            ->editColumn('due_date', fn ($row) => $row->due_date?->format('d M Y') ?? '-')
            ->editColumn('followup_date', fn ($row) => $row->followup_date?->format('d M Y') ?? '-')
            ->editColumn('status', function ($row) {
                $badges = ['pending' => 'warning', 'partial' => 'info', 'paid' => 'success', 'overdue' => 'danger'];

                return '<span class="badge badge-' . ($badges[$row->status] ?? 'secondary') . '">' . ucfirst($row->status) . '</span>';
            })
            ->addColumn('action', fn ($row) => '<button type="button" class="btn btn-sm btn-outline-primary editCollection" data-id="' . $row->id . '"><i class="mdi mdi-pencil-outline"></i> Update</button>')
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client' => 'required|exists:clients,id',
            'package_id' => 'nullable|exists:client_packages,id',
            'invoice_ref' => 'nullable|string|max:100',
            'amount_due' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'followup_date' => 'nullable|date',
            'status' => 'required|in:pending,partial,paid,overdue',
            'remarks' => 'nullable|string',
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

        return response()->json(['code' => 200, 'success' => true, 'message' => 'Collection follow-up created.']);
    }

    public function update(Request $request, CsdCollectionFollowup $collection)
    {
        $validator = Validator::make($request->all(), [
            'amount_due' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'followup_date' => 'nullable|date',
            'status' => 'required|in:pending,partial,paid,overdue',
            'remarks' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'success' => false, 'errors' => $validator->errors()], 400);
        }

        $this->service->update($collection, $validator->validated(), Auth::user());

        return response()->json(['code' => 200, 'success' => true, 'message' => 'Collection follow-up updated.']);
    }

    public function show(CsdCollectionFollowup $collection)
    {
        $collection->load(['client', 'assignee']);

        return response()->json(['success' => true, 'data' => $collection]);
    }
}
