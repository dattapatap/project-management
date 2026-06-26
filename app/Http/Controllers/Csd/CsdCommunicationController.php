<?php

namespace App\Http\Controllers\Csd;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Csd\Concerns\FormatsCsdClientColumn;
use App\Models\CsdCommunication;
use App\Services\Csd\CsdClientResolverService;
use App\Services\Csd\CsdCommunicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class CsdCommunicationController extends Controller
{
    use FormatsCsdClientColumn;

    public function __construct(
        private CsdCommunicationService $service,
        private CsdClientResolverService $resolver
    ) {
    }

    public function index()
    {
        return view('components.csd.communications.index', [
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
            ->editColumn('type', fn ($row) => ucfirst($row->type))
            ->editColumn('subject', fn ($row) => e($row->subject ?? '-'))
            ->editColumn('communication_date', fn ($row) => $row->communication_date->format('d M Y H:i'))
            ->editColumn('next_followup', fn ($row) => $row->next_followup?->format('d M Y') ?? '-')
            ->addColumn('creator_name', fn ($row) => e($row->creator->name ?? '-'))
            ->addColumn('action', function ($row) {
                return '<button type="button" class="btn btn-sm btn-outline-info viewComm" data-id="' . $row->id . '"><i class="mdi mdi-eye-outline"></i></button>'
                    . ' <button type="button" class="btn btn-sm btn-outline-primary editComm" data-id="' . $row->id . '"><i class="mdi mdi-pencil-outline"></i></button>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client' => 'required|exists:clients,id',
            'assignment_id' => 'nullable|exists:csd_client_assignments,id',
            'type' => 'required|in:call,meeting,email,whatsapp,note',
            'subject' => 'nullable|string|max:255',
            'remarks' => 'required|string',
            'communication_date' => 'required|date',
            'next_followup' => 'nullable|date',
            'mom' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'success' => false, 'errors' => $validator->errors()], 400);
        }

        try {
            $this->service->create($validator->validated(), Auth::user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['code' => 403, 'success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json(['code' => 200, 'success' => true, 'message' => 'Communication logged.']);
    }

    public function update(Request $request, CsdCommunication $communication)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:call,meeting,email,whatsapp,note',
            'subject' => 'nullable|string|max:255',
            'remarks' => 'required|string',
            'communication_date' => 'required|date',
            'next_followup' => 'nullable|date',
            'mom' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'success' => false, 'errors' => $validator->errors()], 400);
        }

        try {
            $this->service->update($communication, $validator->validated(), Auth::user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['code' => 403, 'success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json(['code' => 200, 'success' => true, 'message' => 'Communication updated.']);
    }

    public function show(CsdCommunication $communication)
    {
        $communication->load(['client', 'creator']);

        return response()->json(['success' => true, 'data' => $communication]);
    }
}
