<?php

namespace App\Http\Controllers\Csd;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Csd\Concerns\FormatsCsdClientColumn;
use App\Models\CsdAmcContract;
use App\Services\Csd\CsdAmcService;
use App\Services\Csd\CsdClientResolverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class CsdAmcController extends Controller
{
    use FormatsCsdClientColumn;

    public function __construct(
        private CsdAmcService $service,
        private CsdClientResolverService $resolver
    ) {
    }

    public function index(Request $request)
    {
        return view('components.csd.amc.index', [
            'billingFilter' => $request->get('cycle', 'all'),
        ]);
    }

    public function create()
    {
        return view('components.csd.amc.create', [
            'clients' => $this->resolver->getSelectableClients(Auth::user()),
            'contract' => null,
        ]);
    }

    public function edit(CsdAmcContract $amc)
    {
        $this->assertCanAccess($amc);
        $amc->load(['client', 'project']);

        return view('components.csd.amc.edit', [
            'clients' => $this->resolver->getSelectableClients(Auth::user()),
            'contract' => $amc,
        ]);
    }

    public function data(Request $request)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        $cycle = $request->get('cycle', 'all');

        return $this->withCsdClientName(
            DataTables::of($this->service->listQuery(Auth::user(), $cycle))->addIndexColumn()
        )
            ->editColumn('contract_type', fn ($row) => strtoupper($row->contract_type))
            ->editColumn('billing_cycle', function ($row) {
                $label = $row->billing_cycle === 'monthly' ? 'Monthly' : 'Yearly';
                $badge = $row->billing_cycle === 'monthly' ? 'info' : 'primary';

                return '<span class="badge badge-' . $badge . '">' . $label . '</span>';
            })
            ->editColumn('amount', fn ($row) => '₹ ' . number_format($row->amount, 2) . ' <small class="text-muted">/' . ($row->billing_cycle === 'monthly' ? 'mo' : 'yr') . '</small>')
            ->editColumn('start_date', fn ($row) => $row->start_date->format('d M Y'))
            ->editColumn('end_date', function ($row) {
                $html = $row->end_date->format('d M Y');
                if ($row->status === 'active' && $row->isExpiringSoon()) {
                    $days = now()->startOfDay()->diffInDays($row->end_date, false);
                    $html .= '<br><small class="text-warning">Reminder: ' . $days . 'd left</small>';
                }

                return $html;
            })
            ->addColumn('document', function ($row) {
                if (!$row->document_path) {
                    return '<span class="text-muted">—</span>';
                }

                return '<a href="' . route('csd.amc.document', $row) . '" class="btn btn-sm btn-outline-secondary" target="_blank" title="' . e($row->document_name ?? 'Document') . '"><i class="mdi mdi-file-download-outline"></i></a>';
            })
            ->editColumn('status', function ($row) {
                $badges = ['active' => 'success', 'expired' => 'danger', 'renewed' => 'info', 'cancelled' => 'secondary'];

                return '<span class="badge badge-' . ($badges[$row->status] ?? 'secondary') . '">' . ucfirst($row->status) . '</span>';
            })
            ->addColumn('action', function ($row) {
                return '<a href="' . route('csd.amc.edit', $row) . '" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-pencil-outline"></i> Edit</a>';
            })
            ->rawColumns(['billing_cycle', 'amount', 'end_date', 'document', 'status', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validator = $this->validator($request);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        try {
            $this->service->create(
                $validator->validated(),
                Auth::user(),
                $request->file('document')
            );
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('csd.amc.index')->with('success', 'Contract saved successfully.');
    }

    public function update(Request $request, CsdAmcContract $amc)
    {
        $this->assertCanAccess($amc);

        $validator = $this->validator($request, true);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $this->service->update(
            $amc,
            $validator->validated(),
            $request->file('document'),
            $request->boolean('remove_document')
        );

        return redirect()->route('csd.amc.index')->with('success', 'Contract updated successfully.');
    }

    public function document(CsdAmcContract $amc)
    {
        $this->assertCanAccess($amc);

        if (!$amc->document_path || !Storage::disk('public')->exists($amc->document_path)) {
            abort(404, 'Document not found.');
        }

        return Storage::disk('public')->download($amc->document_path, $amc->document_name ?? 'amc-contract');
    }

    public function show(CsdAmcContract $amc)
    {
        $this->assertCanAccess($amc);
        $amc->load(['client', 'project']);

        return response()->json(['success' => true, 'data' => $amc]);
    }

    private function validator(Request $request, bool $isUpdate = false): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'client' => $isUpdate ? 'prohibited' : 'required|exists:clients,id',
            'project_id' => 'nullable|exists:department_projects,id',
            'contract_type' => 'required|in:amc,support',
            'billing_cycle' => 'required|in:monthly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:active,expired,renewed,cancelled',
            'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'remove_document' => 'nullable|boolean',
        ]);
    }

    private function assertCanAccess(CsdAmcContract $amc): void
    {
        if (!$this->resolver->userCanAccessClient(Auth::user(), (int) $amc->client)) {
            abort(403, 'You cannot access this contract.');
        }
    }
}
