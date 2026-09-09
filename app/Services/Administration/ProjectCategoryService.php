<?php

namespace App\Services\Administration;

use App\Models\Department;
use App\Models\DepartmentProjects;
use App\Models\ProjectCategory;
use App\Models\ProjectSubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectCategoryService
{
    /**
     * Get listing data, summary stats, and filter lookups for the Project Categories view.
     */
    public function getListData(Request $request): array
    {
        $search = trim($request->query('search', ''));
        $categoryId = $request->query('category_id');
        $deptId = $request->query('dept_id');
        $status = $request->query('status');
        $activeTab = $request->query('tab', 'sub_categories'); // 'sub_categories' or 'categories'
        $perPage = (int) $request->query('per_page', 25);
        if ($perPage <= 0 || $perPage > 200) {
            $perPage = 25;
        }

        // 1. Sub-Categories Query
        $subCategoriesQuery = ProjectSubCategory::with(['projectCategory.department'])
            ->withCount('projects');

        if (!empty($search)) {
            $subCategoriesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('projectCategory', function ($sq) use ($search) {
                      $sq->where('category', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($categoryId)) {
            $subCategoriesQuery->where('proj_id', $categoryId);
        }

        if (!empty($deptId)) {
            $subCategoriesQuery->whereHas('projectCategory', function ($q) use ($deptId) {
                $q->where('dept_id', $deptId);
            });
        }

        if ($status !== null && $status !== '') {
            $subCategoriesQuery->where('status', (bool) $status);
        }

        $subCategories = $subCategoriesQuery->orderBy('proj_id', 'asc')
            ->orderBy('name', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        // 2. Parent Categories Query
        $categoriesQuery = ProjectCategory::with('department')
            ->withCount(['subCategories', 'projects']);

        if (!empty($search) && $activeTab === 'categories') {
            $categoriesQuery->where('category', 'like', "%{$search}%");
        }

        if (!empty($deptId)) {
            $categoriesQuery->where('dept_id', $deptId);
        }

        if ($status !== null && $status !== '') {
            $categoriesQuery->where('status', (bool) $status);
        }

        $categories = $categoriesQuery->orderBy('dept_id', 'asc')
            ->orderBy('category', 'asc')
            ->get();

        // 3. Dropdown Lookups
        $allCategories = ProjectCategory::with('department')->where('status', true)->orderBy('category')->get();
        $departments = Department::where('status', true)->orderBy('name')->get();

        // 4. KPI Summary Statistics
        $totalCategories = ProjectCategory::count();
        $totalSubCategories = ProjectSubCategory::count();
        $activeSubCategories = ProjectSubCategory::where('status', true)->count();
        $totalProjectsWithCategory = DepartmentProjects::whereNotNull('category')->count();

        $stats = [
            'total_categories'     => $totalCategories,
            'total_sub_categories' => $totalSubCategories,
            'active_sub_categories'=> $activeSubCategories,
            'total_projects'       => $totalProjectsWithCategory,
        ];

        return [
            'subCategories'  => $subCategories,
            'categories'     => $categories,
            'allCategories'  => $allCategories,
            'departments'    => $departments,
            'stats'          => $stats,
            'activeTab'      => $activeTab,
            'perPage'        => $perPage,
            'filters'        => [
                'search'      => $search,
                'category_id' => $categoryId,
                'dept_id'     => $deptId,
                'status'      => $status,
            ],
        ];
    }

    /**
     * Create a new parent project category.
     */
    public function createCategory(array $data): ProjectCategory
    {
        return ProjectCategory::create([
            'dept_id'  => $data['dept_id'],
            'category' => trim($data['category']),
            'status'   => isset($data['status']) ? (bool) $data['status'] : true,
        ]);
    }

    /**
     * Update an existing parent project category.
     */
    public function updateCategory(ProjectCategory $category, array $data): ProjectCategory
    {
        $category->update([
            'dept_id'  => $data['dept_id'] ?? $category->dept_id,
            'category' => isset($data['category']) ? trim($data['category']) : $category->category,
            'status'   => isset($data['status']) ? (bool) $data['status'] : $category->status,
        ]);

        return $category;
    }

    /**
     * Delete a parent project category.
     */
    public function deleteCategory(ProjectCategory $category): bool
    {
        return DB::transaction(function () use ($category) {
            // Soft delete associated sub-categories as well
            $category->subCategories()->delete();
            return (bool) $category->delete();
        });
    }

    /**
     * Create a new project sub-category with parent category ID.
     */
    public function createSubCategory(array $data): ProjectSubCategory
    {
        return ProjectSubCategory::create([
            'proj_id' => $data['proj_id'],
            'name'    => trim($data['name']),
            'status'  => isset($data['status']) ? (bool) $data['status'] : true,
        ]);
    }

    /**
     * Update an existing project sub-category.
     */
    public function updateSubCategory(ProjectSubCategory $subCategory, array $data): ProjectSubCategory
    {
        $subCategory->update([
            'proj_id' => $data['proj_id'] ?? $subCategory->proj_id,
            'name'    => isset($data['name']) ? trim($data['name']) : $subCategory->name,
            'status'  => isset($data['status']) ? (bool) $data['status'] : $subCategory->status,
        ]);

        return $subCategory;
    }

    /**
     * Delete a project sub-category.
     */
    public function deleteSubCategory(ProjectSubCategory $subCategory): bool
    {
        return (bool) $subCategory->delete();
    }

    /**
     * Toggle active/inactive status for Category or Sub-Category.
     */
    public function toggleStatus(string $type, int $id): bool
    {
        if ($type === 'category') {
            $category = ProjectCategory::findOrFail($id);
            $category->status = !$category->status;
            return $category->save();
        }

        if ($type === 'sub_category') {
            $subCategory = ProjectSubCategory::findOrFail($id);
            $subCategory->status = !$subCategory->status;
            return $subCategory->save();
        }

        return false;
    }
}
