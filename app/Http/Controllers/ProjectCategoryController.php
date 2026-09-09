<?php

namespace App\Http\Controllers;

use App\Models\ProjectCategory;
use App\Models\ProjectSubCategory;
use App\Services\Administration\ProjectCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectCategoryController extends Controller
{
    public function __construct(
        protected ProjectCategoryService $categoryService
    ) {}

    /**
     * Display Project Categories and Sub Categories Management view.
     */
    public function index(Request $request): View
    {
        $data = $this->categoryService->getListData($request);
        return view('components.administration.project-categories.index', $data);
    }

    /**
     * Store a newly created parent project category.
     */
    public function storeCategory(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'dept_id'  => 'required|exists:departments,id',
            'category' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('project_category', 'category')
                    ->where(fn($q) => $q->where('dept_id', $request->dept_id)->whereNull('deleted_at')),
            ],
            'status'   => 'nullable|boolean',
        ], [
            'category.unique' => 'A project category with this name already exists in the selected department.',
        ]);

        $this->categoryService->createCategory($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Project Category created successfully!',
            ]);
        }

        return redirect()->route('administration.project-categories.index', ['tab' => 'categories'])
            ->with('success', 'Project Category created successfully!');
    }

    /**
     * Update an existing parent project category.
     */
    public function updateCategory(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $category = ProjectCategory::findOrFail($id);

        $validated = $request->validate([
            'dept_id'  => 'required|exists:departments,id',
            'category' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('project_category', 'category')
                    ->ignore($id)
                    ->where(fn($q) => $q->where('dept_id', $request->dept_id)->whereNull('deleted_at')),
            ],
            'status'   => 'nullable|boolean',
        ], [
            'category.unique' => 'A project category with this name already exists in the selected department.',
        ]);

        $this->categoryService->updateCategory($category, $validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Project Category updated successfully!',
            ]);
        }

        return redirect()->route('administration.project-categories.index', ['tab' => 'categories'])
            ->with('success', 'Project Category updated successfully!');
    }

    /**
     * Delete a parent project category.
     */
    public function destroyCategory(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $category = ProjectCategory::findOrFail($id);
        $this->categoryService->deleteCategory($category);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Project Category and related sub-categories removed.',
            ]);
        }

        return redirect()->route('administration.project-categories.index', ['tab' => 'categories'])
            ->with('success', 'Project Category removed successfully.');
    }

    /**
     * Store a newly created project sub-category.
     */
    public function storeSubCategory(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'proj_id' => 'required|exists:project_category,id',
            'name'    => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('project_sub_categories', 'name')
                    ->where(fn($q) => $q->where('proj_id', $request->proj_id)->whereNull('deleted_at')),
            ],
            'status'  => 'nullable|boolean',
        ], [
            'name.unique' => 'A sub-category with this name already exists under the selected parent category.',
        ]);

        $this->categoryService->createSubCategory($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sub-Category created successfully!',
            ]);
        }

        return redirect()->route('administration.project-categories.index', ['tab' => 'sub_categories'])
            ->with('success', 'Sub-Category created successfully!');
    }

    /**
     * Update an existing project sub-category.
     */
    public function updateSubCategory(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $subCategory = ProjectSubCategory::findOrFail($id);

        $validated = $request->validate([
            'proj_id' => 'required|exists:project_category,id',
            'name'    => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('project_sub_categories', 'name')
                    ->ignore($id)
                    ->where(fn($q) => $q->where('proj_id', $request->proj_id)->whereNull('deleted_at')),
            ],
            'status'  => 'nullable|boolean',
        ], [
            'name.unique' => 'A sub-category with this name already exists under the selected parent category.',
        ]);

        $this->categoryService->updateSubCategory($subCategory, $validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sub-Category updated successfully!',
            ]);
        }

        return redirect()->route('administration.project-categories.index', ['tab' => 'sub_categories'])
            ->with('success', 'Sub-Category updated successfully!');
    }

    /**
     * Delete a project sub-category.
     */
    public function destroySubCategory(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $subCategory = ProjectSubCategory::findOrFail($id);
        $this->categoryService->deleteSubCategory($subCategory);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sub-Category removed successfully.',
            ]);
        }

        return redirect()->route('administration.project-categories.index', ['tab' => 'sub_categories'])
            ->with('success', 'Sub-Category removed successfully.');
    }

    /**
     * Toggle status for Category or Sub-Category via AJAX.
     */
    public function toggleStatus(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:category,sub_category',
            'id'   => 'required|integer',
        ]);

        $success = $this->categoryService->toggleStatus($request->input('type'), (int) $request->input('id'));

        return response()->json([
            'success' => $success,
            'message' => 'Status updated successfully.',
        ]);
    }
}
