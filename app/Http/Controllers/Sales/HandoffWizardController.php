<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Clients;
use App\Models\ClientDocs;
use App\Models\ClientPackages;
use App\Models\ClientPayments;
use App\Models\DepartmentProjects;
use App\Models\User;
use App\Services\Commercial\ClientEngagementService;
use App\Services\Sales\ServiceCatalogService;
use App\Notifications\ClientMatured;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;

class HandoffWizardController extends Controller
{
    public function __construct(
        private ServiceCatalogService $catalogService,
        private ClientEngagementService $engagementService
    ) {}

    public function showWizard($clientId)
    {
        $client = Clients::findOrFail($clientId);
        if ($client->status !== 'Matured') {
            return redirect()->back()->with('error', 'Only matured leads can be handed off.');
        }

        // Fetch categories & subcategories
        $categories = DB::table('project_category')->get();
        $subcategories = DB::table('project_sub_categories')->get();

        // Fetch active service catalog items
        $catalogItems = $this->catalogService->getActiveCatalogs();

        return view('sales.handoff_wizard', compact('client', 'categories', 'subcategories', 'catalogItems'));
    }

    public function processHandoff(Request $request)
    {
        $rules = [
            'client_id'    => 'required|integer|exists:clients,id',
            'project_name' => 'required|string|max:255',
            'category'     => 'required|integer',
            'sub_category' => 'required|integer',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'description'  => 'required|string',
            'package'      => 'required|numeric|min:1',
            'advance'      => 'required|numeric|min:0|max:package',
            'payment_type' => 'required|string|in:Cash,Cheque,Online',
            'transactionid' => 'required_if:payment_type,Online|nullable|string',
            'payment_cash_receipt'   => 'required_if:payment_type,Cash|nullable|file|mimes:jpeg,jpg,png,pdf|max:2000',
            'payment_cheque_receipt' => 'required_if:payment_type,Cheque|nullable|file|mimes:jpeg,jpg,png,pdf|max:2000',
            'proforma'     => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:2000',
            'services'     => 'required|array|min:1',
            'services.*.id'    => 'required|integer|exists:service_catalogs,id',
            'services.*.price' => 'required|numeric|min:1',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->all()], 400);
        }

        try {
            DB::beginTransaction();

            $userid = Auth::user()->id;
            $client = Clients::findOrFail($request->client_id);
            $projectCat = DB::table('project_category')->where('id', $request->category)->first();

            // 1. Create DepartmentProjects
            $dept = new DepartmentProjects();
            $dept->client       = $client->id;
            $dept->department   = $projectCat->dept_id;
            $dept->category     = $request->category;
            $dept->sub_category = $request->sub_category;
            $dept->assigned_by  = $userid;
            $dept->created_date = Carbon::now();
            $dept->project_name = $request->project_name;
            $dept->start_date   = Carbon::parse($request->start_date)->format('Y-m-d H:i');
            $dept->end_date     = Carbon::parse($request->end_date)->format('Y-m-d H:i');
            $dept->description  = $request->description;
            $dept->status       = "ToDo";
            $dept->save();

            // 2. Add Service catalog selection details to project description / comments
            $servicesSummary = "\n\nSelected Services Details:\n";
            foreach ($request->services as $serv) {
                $catalogItem = \App\Models\ServiceCatalog::find($serv['id']);
                $servicesSummary .= "- {$catalogItem->name} (Category: {$catalogItem->category}): Price ₹" . number_format($serv['price'], 2) . "\n";
            }
            $dept->description .= $servicesSummary;
            $dept->save();

            // 3. Create Client Package
            $clipack = new ClientPackages();
            $clipack->client     = $client->id;
            $clipack->project_id = $dept->id;
            $clipack->package    = $request->package;
            $clipack->balance    = round($request->package - $request->advance);
            $clipack->created_by = $userid;
            $clipack->updated_by = $userid;
            $clipack->save();

            // 4. Add Payment with type (Cash/Cheque/Online)
            $clidocs1 = new ClientPayments();
            $clidocs1->client       = $client->id;
            $clidocs1->package_id   = $clipack->id;
            $clidocs1->paid_date    = Carbon::now();
            $clidocs1->amount       = $request->advance;
            $clidocs1->remains      = round($request->package - $request->advance);
            $clidocs1->payment_type = $request->payment_type;
            $clidocs1->created_by   = $userid;

            if ($request->payment_type == 'Cheque' && $request->hasFile('payment_cheque_receipt')) {
                $attachment = $request->file('payment_cheque_receipt');
                $name = 'payments/' . time() . '.' . $attachment->getClientOriginalExtension();
                $dbname1 = 'clients/' . $name;
                $request->file('payment_cheque_receipt')->storeAs('clients', $name, 'public');
                $clidocs1->file = $dbname1;
            } elseif ($request->payment_type == 'Online') {
                $clidocs1->transactioinid = $request->transactionid;
            } elseif ($request->payment_type == 'Cash' && $request->hasFile('payment_cash_receipt')) {
                $attachment = $request->file('payment_cash_receipt');
                $name = 'payments/' . time() . '.' . $attachment->getClientOriginalExtension();
                $dbname2 = 'clients/' . $name;
                $request->file('payment_cash_receipt')->storeAs('clients', $name, 'public');
                $clidocs1->file = $dbname2;
            }
            $clidocs1->save();

            // 5. Add proforma/scope document to ClientDocs
            if ($request->hasFile('proforma')) {
                $attachment = $request->file('proforma');
                $name = 'docs/' . time() . '.' . $attachment->getClientOriginalExtension();
                $dbname = 'clients/' . $name;
                $request->file('proforma')->storeAs('clients', $name, 'public');

                $clidocs = new ClientDocs();
                $clidocs->client   = $client->id;
                $clidocs->category = 'DSR';
                $clidocs->doc_type = 'Proforma';
                $clidocs->files    = $dbname;
                $clidocs->uploaded = Carbon::now();
                $clidocs->created  = $userid;
                $clidocs->save();
            }

            // 6. Record initial commercial engagement
            $this->engagementService->recordInitialFromMaturity(
                $client,
                $dept,
                $clipack,
                $userid
            );

            // 7. Notify Project Managers
            $productManagers = User::role('Project-Manager')->where('status', 'Active')->get();
            foreach ($productManagers as $pm) {
                $pm->notify((new ClientMatured($client, $dept, $request->project_name))->delay(now()->addSeconds(5)));
            }

            // 8. Update Client active state
            $client->is_active = true;
            $client->active_from = Carbon::now();
            $client->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Handoff Wizard completed successfully! Project has been operationalized.',
                'project_id' => $dept->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Handoff failed: ' . $e->getMessage() . " at " . $e->getLine()], 500);
        }
    }
}
