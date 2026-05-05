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
        $category = $request->category;
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
                            .  '<a class="dropdown-item createNewDomain" client="' . $client->id . '" clientnm="' . $client->name . '" href="javascript:void(0)">Add Domain</a>'
                            .  '<a class="dropdown-item" href="' . route('client.detail', [$clientEncodedId, 'docs']) . '">Add Documents</a>';
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

                return '<span class="badge ' . $class . ' font-size-12">' . $data->status . '</span>';
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
            return redirect()->route('clients.category', 'Fresh')->with('success', 'Client Added successfully');
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
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->getMessageBag()->toArray()], 400);
        }

        try {
            DB::beginTransaction();
            $userid = Auth::id();

            $client = new Clients();
            $this->assignClientData($client, $request);

            $client->category   = 'Direct';
            $client->status     = 'Matured';
            $client->is_active  = true;
            $client->created_by = $userid;
            $client->tele_ref_user = $userid;
            $client->updated_by = $userid;
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
}
