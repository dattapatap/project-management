<?php

namespace App\Http\Controllers\Csd;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Csd\Concerns\FormatsCsdClientColumn;
use App\Models\CsdSupportTicket;
use App\Services\Csd\CsdClientResolverService;
use App\Services\Csd\CsdSupportService;
use App\Services\CsdTeamScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class CsdSupportController extends Controller
{
    use FormatsCsdClientColumn;

    public function __construct(
        private CsdSupportService $service,
        private CsdClientResolverService $resolver,
        private CsdTeamScopeService $scope
    ) {
    }

    public function index()
    {
        $user = Auth::user();

        return view('components.csd.support.index', [
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
            ->editColumn('priority', function ($row) {
                $badges = ['low' => 'secondary', 'medium' => 'info', 'high' => 'warning', 'critical' => 'danger'];

                return '<span class="badge badge-' . ($badges[$row->priority] ?? 'secondary') . '">' . ucfirst($row->priority) . '</span>';
            })
            ->editColumn('status', function ($row) {
                $badges = ['open' => 'danger', 'in_progress' => 'warning', 'resolved' => 'success', 'closed' => 'secondary'];

                return '<span class="badge badge-' . ($badges[$row->status] ?? 'secondary') . '">' . ucfirst(str_replace('_', ' ', $row->status)) . '</span>';
            })
            ->addColumn('assignee_name', fn ($row) => e($row->assignee->name ?? 'Unassigned'))
            ->addColumn('action', fn ($row) => '<button type="button" class="btn btn-sm btn-outline-primary editTicket" data-id="' . $row->id . '"><i class="mdi mdi-pencil-outline"></i> Update</button>')
            ->rawColumns(['priority', 'status', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client' => 'required|exists:clients,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:ticket,complaint,escalation',
            'priority' => 'required|in:low,medium,high,critical',
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

        return response()->json(['code' => 200, 'success' => true, 'message' => 'Support ticket created.']);
    }

    public function update(Request $request, CsdSupportTicket $ticket)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:ticket,complaint,escalation',
            'priority' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:open,in_progress,resolved,closed',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'success' => false, 'errors' => $validator->errors()], 400);
        }

        $this->service->update($ticket, $validator->validated(), Auth::user());

        return response()->json(['code' => 200, 'success' => true, 'message' => 'Ticket updated.']);
    }

    public function show(CsdSupportTicket $ticket)
    {
        $ticket->load(['client', 'assignee']);

        return response()->json(['success' => true, 'data' => $ticket]);
    }
}
