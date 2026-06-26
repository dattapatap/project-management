<?php

namespace App\Http\Controllers\Csd;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Csd\Concerns\FormatsCsdClientColumn;
use App\Models\CsdChangeRequest;
use App\Services\Csd\CsdChangeRequestService;
use App\Services\Csd\CsdClientResolverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class CsdChangeRequestController extends Controller
{
    use FormatsCsdClientColumn;

    public function __construct(
        private CsdChangeRequestService $service,
        private CsdClientResolverService $resolver
    ) {
    }

    public function index()
    {
        return view('components.csd.change-requests.index', [
            'clients' => $this->resolver->getSelectableClients(Auth::user()),
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
            ->editColumn('status', fn ($row) => '<span class="badge badge-info">' . ucfirst(str_replace('_', ' ', $row->status)) . '</span>')
            ->addColumn('action', function ($row) {
                $btn = '<button type="button" class="btn btn-sm btn-outline-primary editChangeRequest" data-id="' . $row->id . '"><i class="mdi mdi-pencil-outline"></i> Update</button>';
                if ($row->status === 'approved') {
                    $btn .= ' <button type="button" class="btn btn-sm btn-success transferToOd" data-id="' . $row->id . '"><i class="mdi mdi-transfer"></i> To OD</button>';
                }

                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:department_projects,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'success' => false, 'errors' => $validator->errors()], 400);
        }

        try {
            $this->service->create($validator->validated(), Auth::user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['code' => 403, 'success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json(['code' => 200, 'success' => true, 'message' => 'Change request submitted.']);
    }

    public function update(Request $request, CsdChangeRequest $changeRequest)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:submitted,estimating,approved,rejected,transferred_to_od,completed',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'success' => false, 'errors' => $validator->errors()], 400);
        }

        $this->service->update($changeRequest, $validator->validated());

        return response()->json(['code' => 200, 'success' => true, 'message' => 'Change request updated.']);
    }

    public function transferToOd(CsdChangeRequest $changeRequest)
    {
        try {
            $odProject = $this->service->transferToOd($changeRequest, Auth::user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['code' => 200, 'success' => false, 'message' => $e->getMessage()]);
        }

        return response()->json([
            'code' => 200,
            'success' => true,
            'message' => 'Change request transferred to Operations.',
            'project_id' => $odProject->id,
        ]);
    }

    public function show(CsdChangeRequest $changeRequest)
    {
        $changeRequest->load(['client', 'project', 'assignee']);

        return response()->json(['success' => true, 'data' => $changeRequest]);
    }
}
