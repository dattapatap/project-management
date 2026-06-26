<?php

namespace App\Http\Controllers\Csd;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Csd\Concerns\FormatsCsdClientColumn;
use App\Models\CsdRenewal;
use App\Services\Csd\CsdClientResolverService;
use App\Services\Csd\CsdRenewalService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class CsdRenewalController extends Controller
{
    use FormatsCsdClientColumn;

    public function __construct(
        private CsdRenewalService $service,
        private CsdClientResolverService $resolver
    ) {
    }

    public function index(Request $request)
    {
        return view('components.csd.renewals.index', [
            'clients' => $this->resolver->getSelectableClients(Auth::user()),
            'statusFilter' => $request->get('status', 'all'),
        ]);
    }

    public function data(Request $request)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        $statusFilter = $request->get('status', 'all');

        return $this->withCsdClientName(
            DataTables::of($this->service->listQuery(Auth::user(), $statusFilter))->addIndexColumn()
        )
            ->editColumn('renewal_type', fn ($row) => strtoupper(str_replace('_', ' ', $row->renewal_type)))
            ->editColumn('due_date', function ($row) {
                $date = $row->due_date?->format('d M Y') ?? '-';
                if (!$row->due_date || in_array($row->status, ['renewed', 'lapsed'], true)) {
                    return $date;
                }
                $days = Carbon::today()->diffInDays($row->due_date, false);
                $hint = $days < 0
                    ? '<br><small class="text-danger">' . abs($days) . 'd overdue</small>'
                    : ($days <= 30 ? '<br><small class="text-warning">in ' . $days . 'd</small>' : '');

                return $date . $hint;
            })
            ->editColumn('amount', fn ($row) => $row->amount ? '₹ ' . number_format($row->amount, 2) : '-')
            ->editColumn('status', function ($row) {
                $badges = ['upcoming' => 'info', 'due' => 'warning', 'renewed' => 'success', 'lapsed' => 'danger'];

                return '<span class="badge badge-' . ($badges[$row->status] ?? 'secondary') . '">' . ucfirst($row->status) . '</span>';
            })
            ->addColumn('action', function ($row) {
                $html = '<button type="button" class="btn btn-sm btn-outline-primary editRenewal" data-id="' . $row->id . '"><i class="mdi mdi-pencil-outline"></i></button>';
                if (in_array($row->status, ['upcoming', 'due'], true)) {
                    $html .= ' <button type="button" class="btn btn-sm btn-success markRenewed" data-id="' . $row->id . '" title="Mark renewed"><i class="mdi mdi-check"></i></button>';
                    $html .= ' <button type="button" class="btn btn-sm btn-outline-danger markLapsed" data-id="' . $row->id . '" title="Mark lapsed"><i class="mdi mdi-close"></i></button>';
                }

                return $html;
            })
            ->rawColumns(['status', 'action', 'due_date'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client' => 'required|exists:clients,id',
            'renewal_type' => 'required|in:amc,domain,hosting,subscription',
            'reference_id' => 'nullable|integer',
            'title' => 'required|string|max:255',
            'due_date' => 'required|date',
            'amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'success' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 400);
        }

        try {
            $this->service->create($validator->validated(), Auth::user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['code' => 403, 'success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json(['code' => 200, 'success' => true, 'message' => 'Renewal record created.']);
    }

    public function update(Request $request, CsdRenewal $renewal)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'due_date' => 'required|date',
            'amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:upcoming,due,renewed,lapsed',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'success' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 400);
        }

        try {
            $this->service->update($renewal, $validator->validated(), Auth::user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['code' => 403, 'success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json(['code' => 200, 'success' => true, 'message' => 'Renewal updated.']);
    }

    public function markRenewed(CsdRenewal $renewal)
    {
        try {
            $this->service->markRenewed($renewal, Auth::user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json(['success' => true, 'message' => 'Renewal marked as completed. Linked contract updated where applicable.']);
    }

    public function markLapsed(CsdRenewal $renewal)
    {
        try {
            $this->service->markLapsed($renewal, Auth::user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json(['success' => true, 'message' => 'Renewal marked as lapsed.']);
    }

    public function sync()
    {
        $result = $this->service->syncFromSources(Auth::user());

        return response()->json([
            'success' => true,
            'message' => "Synced: {$result['amc']} AMC, {$result['domains']} domain renewals. {$result['statuses']} statuses updated.",
            'data' => $result,
        ]);
    }

    public function amcOptions(Request $request)
    {
        $clientId = (int) $request->get('client');
        if (!$clientId) {
            return response()->json(['success' => true, 'data' => []]);
        }

        try {
            $contracts = $this->service->amcOptionsForClient($clientId, Auth::user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $contracts->map(fn ($c) => [
                'id' => $c->id,
                'label' => strtoupper($c->contract_type) . ' (' . ucfirst($c->billing_cycle ?? 'yearly') . ') — ends ' . $c->end_date->format('d M Y') . ' (₹' . number_format($c->amount, 0) . ')',
                'end_date' => $c->end_date->toDateString(),
                'amount' => $c->amount,
            ]),
        ]);
    }

    public function show(CsdRenewal $renewal)
    {
        try {
            $renewal = $this->service->findForUser($renewal->id, Auth::user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        return response()->json(['success' => true, 'data' => $renewal]);
    }
}
