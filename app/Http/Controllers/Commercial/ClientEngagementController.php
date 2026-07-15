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
            ->addColumn('client_order', function ($row) {
                $client = e($row->clients?->name ?? '—');
                $order = e($row->engagement_no ?? '—');
                $parent = $row->parent?->engagement_no;
                $parentBadge = $parent ? ' <span class="badge badge-soft-info font-size-10 ml-1">Parent: ' . e($parent) . '</span>' : '';
                return '<div>
                            <span class="font-weight-bold text-dark">' . $client . '</span>
                        </div>
                        <div class="mt-1">
                            <small class="text-muted font-weight-semibold">' . $order . '</small>' . $parentBadge . '
                        </div>';
            })
            ->addColumn('engagement_info', function ($row) {
                $title = e($row->title ?? '—');
                $type = ucfirst(str_replace('_', ' ', $row->engagement_type));
                return '<div>
                            <span class="font-weight-semibold text-premium-dark">' . $title . '</span>
                        </div>
                        <div>
                            <small class="text-muted">' . $type . '</small>
                        </div>';
            })
            ->addColumn('attribution', function ($row) {
                $csd = e($row->csdOwner?->name ?? 'Unassigned');
                $sales = e($row->salesOwner?->name ?? '—');
                return '<div class="text-muted font-size-12">
                            <div><i class="mdi mdi-account text-primary mr-1"></i>Csd: <strong class="text-premium-dark">' . $csd . '</strong></div>
                            <div><i class="mdi mdi-account-star text-success mr-1"></i>Sales: <strong class="text-premium-dark">' . $sales . '</strong></div>
                        </div>';
            })
            ->addColumn('value_date', function ($row) {
                $est = $row->estimated_value ? '₹ ' . number_format($row->estimated_value, 2) : '—';
                $closed = $row->closed_value ? '₹ ' . number_format($row->closed_value, 2) : null;
                $date = $row->created_at ? $row->created_at->format('d M Y h:i A') : '—';
                
                $closedHtml = $closed ? '<div class="text-success font-size-11 mt-0.5">Closed: <strong>' . $closed . '</strong></div>' : '';
                return '<div>
                            <span class="text-primary font-weight-bold">Est: ' . $est . '</span>
                        </div>' . $closedHtml . '
                        <div class="mt-1">
                            <small class="text-muted"><i class="mdi mdi-clock-outline"></i> ' . $date . '</small>
                        </div>';
            })
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
            ->rawColumns(['client_order', 'engagement_info', 'attribution', 'value_date', 'status', 'action'])
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
