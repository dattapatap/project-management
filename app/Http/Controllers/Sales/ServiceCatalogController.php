<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Services\Sales\ServiceCatalogService;
use Auth;
use Illuminate\Http\Request;
use Validator;

class ServiceCatalogController extends Controller
{
    public function __construct(
        private ServiceCatalogService $catalogService
    ) {}

    public function index(Request $request)
    {
        $catalogs = $this->catalogService->getAllCatalogs();
        return view('sales.catalog.index', compact('catalogs'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name'          => 'required|string|max:255',
            'category'      => 'required|string|max:100',
            'billing_cycle' => 'required|string|in:one-time,monthly,quarterly,yearly',
            'description'   => 'nullable|string',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->all()], 400);
        }

        try {
            $item = $this->catalogService->createCatalogItem($request->all(), Auth::user());
            return response()->json([
                'success' => true,
                'message' => 'Service catalog item created successfully!',
                'item'    => $item
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'name'          => 'required|string|max:255',
            'category'      => 'required|string|max:100',
            'billing_cycle' => 'required|string|in:one-time,monthly,quarterly,yearly',
            'description'   => 'nullable|string',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->all()], 400);
        }

        try {
            $this->catalogService->updateCatalogItem($id, $request->all());
            return response()->json([
                'success' => true,
                'message' => 'Service catalog item updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $this->catalogService->toggleStatus($id);
            return response()->json([
                'success' => true,
                'message' => 'Service status toggled successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
