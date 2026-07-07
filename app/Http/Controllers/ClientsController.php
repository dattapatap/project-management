<?php

namespace App\Http\Controllers;

use App\Services\ClientServices;

use App\Http\Requests\ClientStoreRequest;
use App\Http\Requests\ClientUpdate;
use App\Models\ClientHistory;
use App\Models\Clients;
use App\Models\User;
use App\Notifications\AssignToExecutive;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Log;
use Yajra\DataTables\Facades\DataTables;

class ClientsController extends Controller
{
    private $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            return $next($request);
        });
    }


    public function index(Request $request, ClientServices $clientService)
    {
        $category = normalize_client_category($request->category);
        $data = $clientService->clients($category, $this->user);

        if (!$request->ajax()) {
            return view('components.clients.index');
        }

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($client) {
                $btns = '';
                $clientEncodedId = base64_encode($client->id);

                // Assign To Button
                if ($this->user->hasRole(["Admin", "Team-Leader"]) && $client->status != 'Matured') {
                    $btns .= '<a class="dropdown-item assignToUser" client="' . $client->id . '" href="javascript:void(0);">Assign To</a>';
                }

                // Update STS/DSR Buttons
                if ($client->status != 'Not Interested') {
                    $btns .= '<a class="dropdown-item" href="' . route('client.detail', [$clientEncodedId, 'sts']) . '">Update STS</a>'
                        .  '<a class="dropdown-item" href="' . route('client.detail', [$clientEncodedId, 'dsr']) . '">Update DSR</a>';
                }

                // Admin/TL Specific Actions
                if ($this->user->hasRole(["Admin", "Team-Leader", 'Branch-Manager'])) {
                    if ($client->status == 'Matured') {
                        $btns .= '<a class="dropdown-item createNewProject" client="' . $client->id . '" clientnm="' . $client->name . '" href="javascript:void(0)">Add Projects</a>'
                            .  '<a class="dropdown-item" href="' . route('client.detail', [$clientEncodedId, 'payment']) . '" >Add Payment</a>'
                            .  '<a class="dropdown-item" href="' . route('csd.renewals.index', ['client' => $client->id]) . '">Add Renewal</a>'
                            .  '<a class="dropdown-item" href="' . route('client.detail', [$clientEncodedId, 'docs']) . '">Add Documents</a>';
                    }
                }

                if ($this->user->hasRole(["Admin", "Branch-Manager"])) {
                    if ($client->status != 'Matured') {
                        $btns .= '<a class="dropdown-item directMatureClient" client="' . $client->id . '" href="javascript:void(0);">Mark as Matured</a>';
                    }
                }

                $action = '<div class="d-flex align-items-center justify-content-center">';

                // View/STS View Button (Always visible)
                $action .= '<a type="button" class="btn btn-outline-success btn-sm m-1" target="_blank" href="' . route('client.detail', [$clientEncodedId, 'sts']) . '" data-toggle="tooltip" title="View Details">
                                <i class="mdi mdi-eye-outline"></i>
                            </a>';

                // Edit and Settings (Hidden for Project-Manager)
                if (!$this->user->hasRole('Project-Manager')) {
                    $action .= '<a type="button" class="btn btn-outline-warning btn-sm m-1" href="' . route('clients.edit', $client->id) . '" data-toggle="tooltip" title="Edit Client">
                                    <i class="mdi mdi-square-edit-outline"></i>
                                </a>
                                <div class="btn-group client-action-btn m-1">
                                    <a type="button" class="btn btn-outline-danger btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="mdi mdi-settings-transfer-outline"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right">' . $btns . '</div>
                                </div>';
                }

                $action .= '</div>';

                return $action;
            })
            ->editColumn('contactinfo', function ($data) {
                return $data->cont_person . '<br><small class="text-muted">' . $data->designation . '</small>';
            })
            ->editColumn('active_from', function ($data) {
                return $data->active_from ? Carbon::parse($data->active_from)->format('d M Y') : '-';
            })
            ->editColumn('telereferral', function ($data) {
                return $data->telereferral ? $data->telereferral->name : '-';
            })
            ->addColumn('created_by_name', function ($data) {
                return $data->creator ? $data->creator->name : 'System';
            })
            ->addColumn('following_by_name', function ($data) {
                return $data->referral ? $data->referral->name : '-';
            })
            ->addColumn('created_at', function ($data) {
                return Carbon::parse($data->created_at)->format('d M Y');
            })
            ->filterColumn('created_at', function ($query, $keyword) {
                $query->whereRaw("DATE_FORMAT(created_at,'%d/%m/%Y') LIKE ?", ["%$keyword%"]);
            })
            ->editColumn('status', function ($data) {
                $class = 'badge-soft-primary';
                if ($data->status == 'Matured') $class = 'badge-soft-success';
                if ($data->status == 'Fresh') $class = 'badge-soft-info';
                if ($data->status == 'Not Interested') $class = 'badge-soft-danger';

                $html = '<div class="status-trigger-wrapper text-center" style="cursor: pointer;" data-client-id="' . $data->id . '" title="Click to view full touchpoint history">';
                $html .= '<span class="badge ' . $class . ' font-size-12 px-2 py-1">' . $data->status . '</span>';
                if ($data->status != 'Fresh' && $data->status != 'Matured' && $data->status != 'Not Interested') {
                    if ($data->history && $data->history->tbro) {
                        $html .= '<br><small class="text-danger font-weight-bold d-block mt-1"><i class="mdi mdi-calendar mr-1"></i>' . Carbon::parse($data->history->tbro)->format('d M Y') . '</small>';
                    }
                }
                $html .= '<span class="text-muted font-size-10 d-block mt-1 toggle-history-text" style="opacity: 0.8;"><i class="mdi mdi-chevron-down mr-1 text-primary"></i>History</span>';
                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['action', 'status', 'contactinfo'])
            ->make(true);
    }

    public function clientsbycategory(Request $request)
    {
        return view('components.clients.index');
    }

    public function create()
    {
        $users = User::where('deleted_at', null)->where('status', 'Active')
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['Sales-Executive', 'Team-Leader']);
            })->get();
        return view('components.clients.create', compact('users'));
    }

    public function store(ClientStoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $userid = Auth::id();

            $client = new Clients();
            $this->assignClientData($client, $request);

            $client->category      = ucfirst($request->category);
            $client->status        = $request->status;
            $client->is_active     = false;
            $client->ref_user      = $request->referral;
            $client->created_by    = $userid;
            $client->tele_ref_user = $userid;
            $client->updated_by    = $userid;
            $client->save();

            $client->histories()->create([
                'category'  => 'STS',
                'status'    => $request->status,
                'tbro_type' => $request->type,
                'time'      => Carbon::parse($request->time)->format('H:i:s'),
                'tbro'      => Carbon::parse($request->tbro_date)->format('Y-m-d'),
                'remarks'   => $request->remarks,
                'created'   => $userid,
            ]);

            DB::commit();
            return redirect()->route('clients.category', client_category_slug('Fresh'))->with('success', 'Client Added successfully');
        } catch (Exception $ex) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Something went wrong: ' . $ex->getMessage())->withInput();
        }
    }

    public function ajaxStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'mobile' => 'required|string|max:20',
            'city' => 'required|string|max:255',
            'address' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->getMessageBag()->toArray()], 400);
        }

        try {
            DB::beginTransaction();
            $userid = Auth::id();

            // Check if a client with the same name, email, or mobile already exists (including soft-deleted check)
            $existingClient = Clients::where(function ($query) use ($request) {
                $query->where('name', $request->name)
                    ->orWhere('email', $request->email);
            })->whereNull('deleted_at')->first();

            if ($existingClient) {
                if ($existingClient->status === 'Matured') {
                    // Client already exists and is Matured - block duplicate creation
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'Client "' . $existingClient->name . '" added.'
                    ], 409);
                }

                // Client exists but is NOT Matured - update to Matured
                $existingClient->status = 'Matured';
                $existingClient->updated_by = $userid;

                // Set ref_user to Admin
                $admin = User::role('Admin')->where('status', 'Active')->first();
                $existingClient->ref_user = $admin ? $admin->id : $userid;

                $existingClient->save();

                // Add history for the status change
                $existingClient->histories()->create([
                    'category' => 'STS',
                    'status'   => 'Matured',
                    'remarks'  => 'Client matured through Project Management section',
                    'created'  => $userid,
                ]);

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Client "' . $existingClient->name . '" already existed and has been updated to Matured.',
                    'client' => ['id' => $existingClient->id, 'name' => $existingClient->name]
                ], 200);
            }

            // No existing client found - create new
            $client = new Clients();
            $this->assignClientData($client, $request);

            $client->category   = 'Direct';
            $client->status     = 'Matured';
            $client->is_active  = true;
            $client->created_by = $userid;
            $client->tele_ref_user = $userid;
            $client->updated_by = $userid;

            // Set ref_user to Admin
            $admin = User::role('Admin')->where('status', 'Active')->first();
            $client->ref_user = $admin ? $admin->id : $userid;

            $client->save();

            // Add Initial Client History
            $client->histories()->create([
                'category' => 'STS',
                'status'   => 'Matured',
                'remarks'  => 'Client created through Project Management section',
                'created'  => $userid,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Client created successfully and set to Matured.',
                'client' => ['id' => $client->id, 'name' => $client->name]
            ], 200);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Something went wrong: ' . $ex->getMessage()], 500);
        }
    }

    private function assignClientData(Clients $client, Request $request)
    {
        $client->name          = ucfirst($request->name);
        $client->cont_person   = ucfirst($request->contact_person);
        $client->designation   = ucfirst($request->designation);
        $client->email         = $request->email;
        $client->mobile        = $request->mobile;
        $client->city          = ucfirst($request->city);
        $client->website_link  = $request->website_link;
        $client->address       = $request->address ?? $request->address1;
        $client->description   = $request->remarks ?? $client->description;
    }

    public function bulkUploadForm()
    {
        $user = Auth::user();
        $requiresAssign = $user->hasRole(['Admin', 'Branch-Manager', 'Manager']);

        $users = [];
        if ($requiresAssign) {
            $users = User::where('deleted_at', null)->where('status', 'Active')
                ->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['Sales-Executive', 'Team-Leader', 'Branch-Manager']);
                })->get();
        }

        return view('components.clients.bulkupload', compact('users', 'requiresAssign'));
    }

    public function bulkUploadSample(Request $request)
    {
        $type = $request->get('type', 'fresh'); // 'fresh' or 'matured'

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="companies_bulk_upload_template_' . $type . '.csv"',
        ];

        $callback = function () use ($type) {
            $file = fopen('php://output', 'w');

            if ($type === 'matured') {
                fputcsv($file, [
                    'Company Name',
                    'Contact Person',
                    'Email ID',
                    'Mobile',
                    'City',
                    'Address',
                    'Website Link',
                ]);
                fputcsv($file, [
                    'Alpha Corp',
                    'Jane Smith',
                    'jane.smith@alphacorp.com',
                    '9123456780',
                    'New York',
                    '789 Maple Avenue',
                    'https://alphacorp.com',
                ]);
            } else {
                fputcsv($file, [
                    'Company Name',
                    'Contact Person',
                    'Designation',
                    'Email ID',
                    'Mobile',
                    'City',
                    'Website Link',
                    'Address',
                    'Remarks',
                    'TBRO Touchpoint Type',
                    'Schedule Time',
                    'Schedule Date (TBRO)',
                    'STS Routing Status',
                ]);
                fputcsv($file, [
                    'Acme Corporation Ltd',
                    'John Doe',
                    'Business Head',
                    'john.doe@acme.com',
                    '9876543210',
                    'Chicago',
                    'https://acmecorp.com',
                    '456 Enterprise Boulevard',
                    'Interested in custom SEO and portal design services',
                    'Call',
                    '03:30 PM',
                    '15-05-2026',
                    'Fresh',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function bulkUploadStore(Request $request)
    {
        $user = Auth::user();
        $requiresAssign = $user->hasRole(['Admin', 'Branch-Manager', 'Manager']);

        $rules = [
            'file'       => 'required|file|mimes:csv,txt|max:5120',
            'client_type' => 'required|in:Fresh,Matured',
        ];

        if ($requiresAssign) {
            $rules['referral'] = 'required|exists:users,id';
        }

        $request->validate($rules);

        $file       = $request->file('file');
        $clientType = $request->input('client_type');
        $referralId = $requiresAssign ? $request->referral : $user->id;
        $userId     = $user->id;

        $successCount = 0;
        $failedCount  = 0;
        $errorsList   = [];

        // Enforce maximum record upload limit of 200 records
        if (($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
            $totalRowsInFile = 0;
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (empty(array_filter($row))) {
                    continue;
                }
                $totalRowsInFile++;
            }
            fclose($handle);

            $dataRowsCount = $totalRowsInFile - 1;
            if ($dataRowsCount > 200) {
                return redirect()->back()
                    ->with('error', "The uploaded file contains $dataRowsCount records, which exceeds the maximum allowed limit of 200 records per upload. Please split your file and try again.")
                    ->withInput();
            }
        }

        if (($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
            $rowNumber = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $rowNumber++;

                // Skip header row
                if ($rowNumber === 1) {
                    continue;
                }

                // Skip totally empty rows
                if (empty(array_filter($data))) {
                    continue;
                }

                try {
                    DB::beginTransaction();

                    if ($clientType === 'Matured') {
                        // Matured client format: Client Name, Contact Person, Email, Mobile, City, Address, Website Link
                        $name         = trim($data[0] ?? '');
                        $cont_person  = trim($data[1] ?? '');
                        $email        = trim($data[2] ?? '');
                        $mobile       = trim($data[3] ?? '');
                        $city         = trim($data[4] ?? '');
                        $address      = trim($data[5] ?? '');
                        $website_link = trim($data[6] ?? '');

                        // Sanitize mobile number: remove non-numeric characters and handle prefixes like +91 or 0
                        $mobile = preg_replace('/[^0-9]/', '', $mobile);
                        if (strlen($mobile) === 12 && str_starts_with($mobile, '91')) {
                            $mobile = substr($mobile, 2);
                        } elseif (strlen($mobile) === 11 && str_starts_with($mobile, '0')) {
                            $mobile = substr($mobile, 1);
                        }

                        // Validation for Matured clients
                        if (empty($name) || empty($cont_person) || empty($mobile) || empty($city) || empty($address)) {
                            throw new Exception("Missing required fields. Company Name, Contact Person, Mobile, City, and Address are mandatory.");
                        }

                        if (!preg_match('/^[6-9][0-9]{9}$/', $mobile)) {
                            throw new Exception("Mobile '$mobile' is invalid. Must be exactly 10 digits starting with 6, 7, 8, or 9.");
                        }

                        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            throw new Exception("Email ID '$email' is invalid.");
                        }

                        // Duplicate check for Matured clients: name AND mobile
                        $duplicateExists = Clients::where('name', $name)
                            ->where('mobile', $mobile)
                            ->whereNull('deleted_at')
                            ->first();

                        if ($duplicateExists) {
                            $errorsList[] = "Row $rowNumber skipped: Client '$name' with mobile '$mobile' already exists in the system (Duplicate detected).";
                            $failedCount++;
                            DB::rollBack();
                            continue;
                        }

                        $client = new Clients();
                        $client->name          = ucwords(strtolower($name));
                        $client->cont_person   = ucwords(strtolower($cont_person));
                        $client->designation   = null; // Not provided in Matured template
                        $client->email         = $email ?: null;
                        $client->mobile        = $mobile;
                        $client->city          = ucwords(strtolower($city));
                        $client->website_link  = $website_link ?: null;
                        $client->address       = $address ?: null;
                        $client->description   = 'Matured client added via bulk CSV upload';
                        $client->category      = 'Direct'; // Or a suitable category for matured leads
                        $client->status        = 'Matured';
                        $client->is_active     = true;
                        $client->ref_user      = $referralId;
                        $client->created_by    = $userId;
                        $client->tele_ref_user = $userId;
                        $client->updated_by    = $userId;
                        $client->save();

                        $client->histories()->create([
                            'category'  => 'STS',
                            'status'    => 'Matured',
                            'remarks'   => 'Matured client added via bulk CSV upload',
                            'created'   => $userId,
                        ]);
                    } else {
                        // Existing Fresh client format handling
                        $name         = trim($data[0] ?? '');
                        $cont_person  = trim($data[1] ?? '');
                        $designation  = trim($data[2] ?? '');
                        $email        = trim($data[3] ?? '');
                        $mobile       = trim($data[4] ?? '');
                        $city         = trim($data[5] ?? '');
                        $website_link = trim($data[6] ?? '');
                        $address      = trim($data[7] ?? '');
                        $remarks      = trim($data[8] ?? '');
                        $tbro_type    = trim($data[9] ?? '');
                        $tbro_time    = trim($data[10] ?? '');
                        $tbro_date    = trim($data[11] ?? '');
                        $sts_status   = trim($data[12] ?? '') ?: 'Fresh';

                        // Sanitize mobile number: remove non-numeric characters and handle prefixes like +91 or 0
                        $mobile = preg_replace('/[^0-9]/', '', $mobile);
                        if (strlen($mobile) === 12 && str_starts_with($mobile, '91')) {
                            $mobile = substr($mobile, 2);
                        } elseif (strlen($mobile) === 11 && str_starts_with($mobile, '0')) {
                            $mobile = substr($mobile, 1);
                        }

                        // Validation rules for Fresh clients
                        if (empty($name) || empty($cont_person) || empty($designation) || empty($mobile) || empty($city) || empty($address) || empty($remarks)) {
                            throw new Exception("Missing required fields. Company Name, Contact Person, Designation, Mobile, City, Address, and Remarks are all mandatory.");
                        }

                        // Duplicate check for Fresh clients: only by name
                        $duplicateExists = Clients::where('name', $name)->whereNull('deleted_at')->exists();
                        if ($duplicateExists) {
                            $errorsList[] = "Row $rowNumber skipped: Company Name '$name' already exists in the system (Duplicate detected).";
                            $failedCount++;
                            DB::rollBack();
                            continue;
                        }

                        if (!preg_match('/^[6-9][0-9]{9}$/', $mobile)) {
                            throw new Exception("Mobile '$mobile' is invalid. Must be exactly 10 digits starting with 6, 7, 8, or 9.");
                        }

                        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            throw new Exception("Email ID '$email' is invalid.");
                        }

                        $parsedTime = null;
                        if (!empty($tbro_time)) {
                            try {
                                $parsedTime = Carbon::parse($tbro_time)->format('H:i:s');
                            } catch (\Exception $e) {
                                // ignore or fail silently
                            }
                        }

                        $parsedDate = null;
                        if (!empty($tbro_date)) {
                            try {
                                $parsedDate = Carbon::parse($tbro_date)->format('Y-m-d');
                            } catch (\Exception $e) {
                                // ignore or fail silently
                            }
                        }

                        $client = new Clients();
                        $client->name          = ucwords(strtolower($name));
                        $client->cont_person   = ucwords(strtolower($cont_person));
                        $client->designation   = ucwords(strtolower($designation));
                        $client->email         = $email ?: null;
                        $client->mobile        = $mobile;
                        $client->city          = ucwords(strtolower($city));
                        $client->website_link  = $website_link ?: null;
                        $client->address       = $address ?: null;
                        $client->description   = $remarks ?: null;

                        $category = 'Fresh';
                        if (strtolower($sts_status) !== 'fresh') {
                            $category = 'Folloup';
                        }
                        if (strtolower($sts_status) === 'not interested') {
                            $category = 'Not Interested';
                        }

                        $client->category      = $category;
                        $client->status        = $sts_status;
                        $client->is_active     = false;
                        $client->ref_user      = $referralId;
                        $client->created_by    = $userId;
                        $client->tele_ref_user = $userId;
                        $client->updated_by    = $userId;
                        $client->save();

                        $client->histories()->create([
                            'category'  => 'STS',
                            'status'    => $sts_status,
                            'tbro_type' => $tbro_type ?: null,
                            'time'      => $parsedTime,
                            'tbro'      => $parsedDate,
                            'remarks'   => $remarks ?: 'Company added via bulk CSV upload',
                            'created'   => $userId,
                        ]);
                    }

                    DB::commit();
                    $successCount++;
                } catch (Exception $ex) {
                    DB::rollBack();
                    $failedCount++;
                    $errorsList[] = "Row $rowNumber failed: " . $ex->getMessage();
                }
            }
            fclose($handle);
        }

        $message = "Successfully uploaded $successCount companies!";
        if ($failedCount > 0) {
            $message .= " However, $failedCount rows failed/skipped.";
        }

        if ($failedCount > 0) {
            return redirect()->back()
                ->with('success', $message)
                ->with('bulk_errors', $errorsList)
                ->withInput();
        }

        $targetCategory = ($clientType === 'Matured') ? 'Matured' : 'Fresh';
        return redirect()->route('clients.category', client_category_slug($targetCategory))->with('success', $message);
    }

    public function show(Clients $client)
    {
        if ($client) {
            $client  = Clients::with('referral')->where('id', $client->id)->where('deleted_at', null)->first();
            return view('components.clients.history.show', compact('client'));
        }
        abort(404);
    }


    public function showClient(Request $request)
    {
        $viewType = $request->urlname;
        $clientId = base64_decode($request->id);

        if (!$clientId) abort(404);

        $relations = ['referral', 'telereferral'];
        if ($viewType === 'payment') $relations[] = 'package';

        $client = Clients::with($relations)->findOrFail($clientId);

        $viewMap = [
            'contacts'    => 'contacts',
            'sts'         => 'sts',
            'dsr'         => 'dsr',
            'development' => 'development',
            'designing'   => 'designing',
            'seo'         => 'seo',
            'history'     => 'history',
            'docs'        => 'docs',
            'payment'     => 'payments',
        ];

        if (isset($viewMap[$viewType])) {
            return view("components.clients.history.{$viewMap[$viewType]}", compact('client'));
        }

        abort(404);
    }

    public function edit(Request $request)
    {
        $client  = Clients::with('referral')->where('id', $request->id)->where('deleted_at', null)->first();
        if ($client)
            return view('components.clients.edit', compact('client'));
        else
            abort(404);
    }

    public function update(ClientUpdate $request, Clients $client)
    {
        try {
            DB::beginTransaction();
            $client = Clients::findOrFail($client->id);

            $this->assignClientData($client, $request);

            $client->category   = ucfirst($request->category);
            $client->alt_email  = $request->alternate_email;
            $client->alt_mobile = $request->alternate_mobile;
            $client->telephone  = $request->telephone;
            $client->alt_telephone = $request->alternate_telephone;
            $client->alt_address   = $request->address2;
            $client->updated_by = Auth::id();

            $client->save();
            DB::commit();

            return redirect()->route('client.detail', [base64_encode($client->id), 'contacts'])->with('success', 'Client updated successfully');
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error("Client Update Error: " . $ex->getMessage());
            return redirect()->back()->with('error', 'Something went wrong: ' . $ex->getMessage())->withInput();
        }
    }

    public function destroy(Clients $client)
    {
        if ($client) {
            $client->updated_by = Auth::user()->id;
            $client->save();
            $client->delete();
            return redirect()->back()->with('message', 'Client Deleted');
        }
    }


    public function assignToExecutive(Request $request)
    {
        $user = Auth::user();

        $clientid  = $request->clientid;
        $executive = $request->executive;

        if ($clientid && $executive) {
            $client = Clients::where('id', $clientid)->first();
            $assignUser = User::find($executive);
            $clientUser = User::find($client->ref_user);
            try {
                DB::beginTransaction();

                $history = new ClientHistory();
                $history->category    = 'STS';
                $history->client      = $client->id;
                $history->remarks     = " Client has been assigned from $clientUser->name To $assignUser->name ";
                $history->status      = 'Fresh';
                $history->time        = Carbon::now()->format('H:i');
                $history->created     = $user->id;
                $history->save();

                $client->status = 'Fresh';

                if ($assignUser->hasRole('Team-Leader')) {
                    $client->ref_user = $assignUser->id;
                    $client->tele_ref_user = $assignUser->id;
                } else {
                    $client->ref_user = $assignUser->id;
                }

                $client->save();

                DB::commit();

                $assignUser->notify((new AssignToExecutive($client,  $category = "Client"))->delay(now()->addSeconds(5)));

                return response()->json(['status' => true, 'message' => 'Client Assigned successfully']);
            } catch (Exception $ex) {
                DB::rollBack();
                Log::error($ex->getMessage());
                return response()->json(['status' => false, 'message' => 'Opps! somthing went wrong, please try again']);
            }
        }
    }

    /**
     * Nudge Sales Executive for quick callback/follow-up status updates
     */
    public function nudgeExecutive(Request $request, $client_id)
    {
        $user = Auth::user();
        try {
            $client = Clients::findOrFail($client_id);
            if (!$client->ref_user) {
                return response()->json([
                    'status' => false,
                    'message' => 'This client has no assigned sales executive to nudge.'
                ], 400);
            }

            $executive = User::findOrFail($client->ref_user);

            // Notify the assigned sales executive
            $executive->notify(new \App\Notifications\SalesLeadNudgeNotification($client, $user));

            // Log this nudge inside ClientHistory for tracking
            $history = new ClientHistory();
            $history->category = 'STS';
            $history->client = $client->id;
            $history->remarks = "⚠️ Team Leader {$user->name} nudged {$executive->name} for an immediate follow-up update.";
            $history->status = $client->status;
            $history->time = Carbon::now()->format('H:i');
            $history->created = $user->id;
            $history->save();

            return response()->json([
                'status' => true,
                'message' => "Executive {$executive->name} has been nudged successfully!"
            ]);
        } catch (\Exception $ex) {
            \Log::error("Sales Lead Nudge Error: " . $ex->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to nudge executive. Please try again.'
            ], 500);
        }
    }

    /**
     * Nudge Sales Executive by User ID (finds their oldest overdue follow-up and notifies them)
     */
    public function nudgeExecutiveByUserId(Request $request)
    {
        $user = Auth::user();
        $execId = $request->get('executive_id');
        if (!$execId) {
            return response()->json([
                'status' => false,
                'message' => 'Executive ID is required.'
            ], 400);
        }

        try {
            $executive = User::findOrFail($execId);

            // Find the executive's oldest overdue client callback
            $client = Clients::where('ref_user', $executive->id)
                ->whereNotIn('status', ['Fresh', 'Matured', 'Not Interested'])
                ->whereHas('histories', function ($q) {
                    $q->where('tbro', '<', Carbon::today()->toDateString());
                })
                ->orderBy('created_at', 'asc')
                ->first();

            // Fallback: any active client if no overdue found
            if (!$client) {
                $client = Clients::where('ref_user', $executive->id)
                    ->whereNotIn('status', ['Fresh', 'Matured', 'Not Interested'])
                    ->first();
            }

            if (!$client) {
                return response()->json([
                    'status' => false,
                    'message' => 'This executive currently has no active clients to nudge.'
                ], 400);
            }

            // Notify the assigned sales executive
            $executive->notify(new \App\Notifications\SalesLeadNudgeNotification($client, $user));

            // Log this nudge inside ClientHistory for tracking
            $history = new ClientHistory();
            $history->category = 'STS';
            $history->client = $client->id;
            $history->remarks = "⚠️ Team Leader {$user->name} nudged {$executive->name} for an immediate follow-up on '{$client->name}'.";
            $history->status = $client->status;
            $history->time = Carbon::now()->format('H:i');
            $history->created = $user->id;
            $history->save();

            return response()->json([
                'status' => true,
                'message' => "Executive {$executive->name} has been nudged successfully!"
            ]);
        } catch (\Exception $ex) {
            \Log::error("Sales Executive Nudge Error: " . $ex->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to nudge executive. Please try again.'
            ], 500);
        }
    }

    public function directMature(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->hasRole(['Admin', 'Branch-Manager'])) {
            return response()->json(['status' => false, 'message' => 'Unauthorized! Only Admins and Branch Managers can mature leads directly.'], 403);
        }

        try {
            DB::beginTransaction();
            $client = Clients::findOrFail($id);
            if ($client->status === 'Matured') {
                return response()->json(['status' => false, 'message' => 'Client is already matured.'], 400);
            }

            $client->status = 'Matured';
            $client->updated_by = $user->id;
            $client->save();

            // Log history
            $client->histories()->create([
                'category' => 'STS',
                'status'   => 'Matured',
                'remarks'  => 'Lead matured directly by ' . $user->name,
                'created'  => $user->id,
            ]);

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Client matured successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }
}
