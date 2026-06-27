<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\ClientEngagement;
use App\Services\Commercial\ClientEngagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class ClientEngagementController extends Controller
{
    public function __construct(private ClientEngagementService $service)
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $categories = DB::table('project_category')
            ->whereNull('deleted_at')
            ->orderBy('category')
            ->get();

        return view('components.commercial.engagements.index', compact('categories'));
    }

    public function data(Request $request)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        return DataTables::of($this->service->listQuery(Auth::user()))
            ->addIndexColumn()
            ->addColumn('client_name', fn ($row) => e($row->clients?->name ?? '—'))
            ->editColumn('engagement_type', fn ($row) => ucfirst(str_replace('_', ' ', $row->engagement_type)))
            ->editColumn('estimated_value', fn ($row) => $row->estimated_value ? '₹ ' . number_format($row->estimated_value, 2) : '—')
            ->editColumn('closed_value', fn ($row) => $row->closed_value ? '₹ ' . number_format($row->closed_value, 2) : '—')
            ->editColumn('status', function ($row) {
                $class = match ($row->status) {
                    ClientEngagement::STATUS_WON_PENDING_COMMERCIAL => 'warning',
                    ClientEngagement::STATUS_IN_DELIVERY => 'info',
                    ClientEngagement::STATUS_COMPLETED => 'success',
                    ClientEngagement::STATUS_LOST, ClientEngagement::STATUS_CANCELLED => 'danger',
                    default => 'primary',
                };

                return '<span class="badge badge-' . $class . '">' . e($row->statusLabel()) . '</span>';
            })
            ->addColumn('parent_no', fn ($row) => e($row->parent?->engagement_no ?? '—'))
            ->addColumn('csd_owner', fn ($row) => e($row->csdOwner?->name ?? 'Unassigned'))
            ->addColumn('sales_owner', fn ($row) => e($row->salesOwner?->name ?? '—'))
            ->addColumn('assigned_at', fn ($row) => $row->created_at ? $row->created_at->format('M d, Y H:i') : '—')
            ->addColumn('action', function ($row) {
                $btn = '<a href="' . route('commercial.engagements.show', $row->id) . '" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-eye"></i></a>';
                if (in_array($row->status, [
                    ClientEngagement::STATUS_WON_PENDING_COMMERCIAL,
                    ClientEngagement::STATUS_COMMERCIAL_IN_PROGRESS,
                ], true)) {
                    $btn .= ' <button type="button" class="btn btn-sm btn-success closeCommercialBtn" data-id="' . $row->id . '" data-title="' . e($row->title) . '"><i class="mdi mdi-check"></i> Close</button>';
                }

                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function show(ClientEngagement $engagement)
    {
        $engagement->load(['clients', 'parent', 'children', 'project', 'package', 'opportunity', 'events.creator', 'salesOwner', 'csdOwner', 'csdTeamLeader']);
        $timeline = $this->service->timelineForClient($engagement->client_id);
        $categories = DB::table('project_category')
            ->whereNull('deleted_at')
            ->orderBy('category')
            ->get();

        return view('components.commercial.engagements.show', compact('engagement', 'timeline', 'categories'));
    }

    public function startCommercial(ClientEngagement $engagement)
    {
        try {
            $this->service->startCommercial($engagement, Auth::user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json(['success' => true, 'message' => 'Commercial work started.']);
    }

    public function closeCommercial(Request $request, ClientEngagement $engagement)
    {
        $rules = [
            'category' => 'required|numeric',
            'sub_category' => 'required|numeric',
            'package' => 'required|numeric|gte:100',
            'advance' => 'required|numeric|gte:100|lte:package',
            'payment_type' => 'required|in:Cash,Cheque,Online',
            'transactionid' => 'required_if:payment_type,Online|nullable|numeric',
            'payment_cheque_receipt' => 'required_if:payment_type,Cheque|nullable|file|max:2000|mimes:jpeg,jpg,png,gif,pdf',
            'payment_cash_receipt' => 'required_if:payment_type,Cash|nullable|file|max:2000|mimes:jpeg,jpg,png,gif,pdf',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $fresh = $this->service->closeCommercial($engagement, $request, Auth::user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Commercial closed — new OD project created. Client status unchanged.',
            'project_id' => $fresh->project_id,
            'engagement_no' => $fresh->engagement_no,
        ]);
    }

    public function clientTimeline(int $clientId)
    {
        $timeline = $this->service->timelineForClient($clientId);

        return response()->json(['success' => true, 'data' => $timeline]);
    }
}
