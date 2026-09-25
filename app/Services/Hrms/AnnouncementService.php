<?php

namespace App\Services\Hrms;

use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class AnnouncementService
{
    /**
     * Get all announcements for management with eager loading.
     */
    public function getAllAnnouncements(?User $user = null): Collection
    {
        $query = Announcement::with(['creator', 'department', 'branch'])
            ->withCount('reads')
            ->orderBy('id', 'desc');

        if ($user && $user->isBranchManager()) {
            $branchId = $user->branchId();
            if ($branchId) {
                $query->where(function ($q) use ($branchId) {
                    $q->where('target_branch', $branchId)
                      ->orWhereNull('target_branch');
                });
            }
        }

        return $query->get();
    }

    /**
     * Create a new announcement.
     */
    public function createAnnouncement(array $data, int $userId): Announcement
    {
        return Announcement::create([
            'title'         => $data['title'],
            'message'       => $data['message'],
            'type'          => $data['type'] ?? 'info',
            'target_dept'   => !empty($data['target_dept']) ? $data['target_dept'] : null,
            'target_branch' => !empty($data['target_branch']) ? $data['target_branch'] : null,
            'start_date'    => $data['start_date'],
            'end_date'      => $data['end_date'],
            'is_active'     => isset($data['is_active']) ? (bool) $data['is_active'] : true,
            'created_by'    => $userId,
        ]);
    }

    /**
     * Update an announcement.
     */
    public function updateAnnouncement(Announcement $announcement, array $data): bool
    {
        return $announcement->update([
            'title'         => $data['title'],
            'message'       => $data['message'],
            'type'          => $data['type'] ?? $announcement->type,
            'target_dept'   => !empty($data['target_dept']) ? $data['target_dept'] : null,
            'target_branch' => !empty($data['target_branch']) ? $data['target_branch'] : null,
            'start_date'    => $data['start_date'],
            'end_date'      => $data['end_date'],
            'is_active'     => isset($data['is_active']) ? (bool) $data['is_active'] : $announcement->is_active,
        ]);
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(Announcement $announcement): bool
    {
        $announcement->is_active = !$announcement->is_active;
        return $announcement->save();
    }

    /**
     * Soft delete an announcement.
     */
    public function deleteAnnouncement(Announcement $announcement): bool
    {
        return $announcement->delete();
    }

    /**
     * Get all active announcements targeted to a user, mapped with user's read status.
     * Used for both auto-popup (unread detection) and persistent floating trigger review.
     */
    public function getActiveAnnouncementsForUser(User $user): Collection
    {
        $today = Carbon::today()->toDateString();
        $userDeptId = $user->departments?->department;
        $userBranchId = $user->branchId();

        return Announcement::where('is_active', true)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->where(function ($q) use ($userDeptId) {
                $q->whereNull('target_dept');
                if ($userDeptId) {
                    $q->orWhere('target_dept', $userDeptId);
                }
            })
            ->where(function ($q) use ($userBranchId) {
                $q->whereNull('target_branch');
                if ($userBranchId) {
                    $q->orWhere('target_branch', $userBranchId);
                }
            })
            ->with([
                'reads' => function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                },
                'creator',
                'department',
                'branch'
            ])
            ->orderByRaw("CASE WHEN type = 'urgent' THEN 1 WHEN type = 'event' THEN 2 ELSE 3 END")
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($ann) {
                $ann->is_read = $ann->reads->isNotEmpty();
                return $ann;
            });
    }

    /**
     * Get unread, active announcements for a specific user.
     */
    public function getUnreadActiveAnnouncementsForUser(User $user): Collection
    {
        $today = Carbon::today()->toDateString();
        $userDeptId = $user->departments?->department;
        $userBranchId = $user->branchId();

        return Announcement::where('is_active', true)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->where(function ($q) use ($userDeptId) {
                $q->whereNull('target_dept');
                if ($userDeptId) {
                    $q->orWhere('target_dept', $userDeptId);
                }
            })
            ->where(function ($q) use ($userBranchId) {
                $q->whereNull('target_branch');
                if ($userBranchId) {
                    $q->orWhere('target_branch', $userBranchId);
                }
            })
            ->whereDoesntHave('reads', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderByRaw("CASE WHEN type = 'urgent' THEN 1 WHEN type = 'event' THEN 2 ELSE 3 END")
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Mark an announcement as read by a user so it never auto-pops for them again.
     */
    public function markAsRead(int $announcementId, int $userId): AnnouncementRead
    {
        return AnnouncementRead::firstOrCreate(
            [
                'announcement_id' => $announcementId,
                'user_id'         => $userId,
            ],
            [
                'read_at' => Carbon::now(),
            ]
        );
    }
}
