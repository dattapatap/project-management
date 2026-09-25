<?php

namespace App\Http\Controllers\Hrms;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Branches;
use App\Models\Department;
use App\Services\Hrms\AnnouncementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function __construct(
        protected AnnouncementService $announcementService
    ) {
        $this->middleware('auth');
    }

    /**
     * Display announcements list for Admin and Branch Manager.
     */
    public function index(): View
    {
        $user = Auth::user();
        $announcements = $this->announcementService->getAllAnnouncements($user);
        $departments = Department::orderBy('name')->get();
        $branches = Branches::orderBy('name')->get();

        return view('components.hrms.announcements.index', compact('announcements', 'departments', 'branches'));
    }

    /**
     * Store a newly created announcement.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'message'       => 'required|string',
            'type'          => 'required|in:info,urgent,event',
            'target_dept'   => 'nullable|exists:departments,id',
            'target_branch' => 'nullable|exists:branches,id',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'is_active'     => 'nullable|boolean',
        ]);

        $this->announcementService->createAnnouncement($validated, Auth::id());

        return redirect()->route('hrms.announcements.index')
            ->with('success', 'Announcement published successfully.');
    }

    /**
     * Update the specified announcement.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $announcement = Announcement::findOrFail($id);

        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'message'       => 'required|string',
            'type'          => 'required|in:info,urgent,event',
            'target_dept'   => 'nullable|exists:departments,id',
            'target_branch' => 'nullable|exists:branches,id',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'is_active'     => 'nullable|boolean',
        ]);

        $this->announcementService->updateAnnouncement($announcement, $validated);

        return redirect()->route('hrms.announcements.index')
            ->with('success', 'Announcement updated successfully.');
    }

    /**
     * Toggle the active status of an announcement.
     */
    public function toggleStatus(int $id): RedirectResponse
    {
        $announcement = Announcement::findOrFail($id);
        $this->announcementService->toggleStatus($announcement);

        $statusMsg = $announcement->is_active ? 'activated' : 'deactivated';

        return redirect()->route('hrms.announcements.index')
            ->with('success', "Announcement {$statusMsg} successfully.");
    }

    /**
     * Remove the specified announcement from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $announcement = Announcement::findOrFail($id);
        $this->announcementService->deleteAnnouncement($announcement);

        return redirect()->route('hrms.announcements.index')
            ->with('success', 'Announcement deleted successfully.');
    }

    /**
     * AJAX action for employees / TLs to mark an announcement as read.
     */
    public function markAsRead(int $id): JsonResponse
    {
        $userId = Auth::id();
        $this->announcementService->markAsRead($id, $userId);

        return response()->json([
            'success' => true,
            'message' => 'Announcement marked as read.',
        ]);
    }
}
