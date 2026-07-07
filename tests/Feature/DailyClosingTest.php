<?php

namespace Tests\Feature;

use App\Models\ClientHistory;
use App\Models\DailyTarget;
use App\Models\DayClosing;
use App\Models\User;
use App\Services\DailyClosingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DailyClosingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_metrics_calculation_for_nsd()
    {
        $user = User::factory()->create();
        $user->assignRole('Sales-Executive');

        // Create some histories for today
        ClientHistory::create([
            'client' => 1,
            'category' => 'STS',
            'remarks' => 'Call done',
            'created' => $user->id,
        ]);

        ClientHistory::create([
            'client' => 1,
            'category' => 'DSR',
            'remarks' => 'DSR update',
            'created' => $user->id,
        ]);

        $service = new DailyClosingService();
        $metrics = $service->getTodayMetrics($user, Carbon::today()->format('Y-m-d'));

        $this->assertEquals(1, $metrics['sts'] ?? 0);
        $this->assertEquals(1, $metrics['dsr'] ?? 0);
    }

    public function test_resolving_target_status_for_nsd()
    {
        $user = User::factory()->create();
        $user->assignRole('Sales-Executive');

        $service = new DailyClosingService();

        // 1. Target Met (at least 2 DSR)
        $metrics = ['sts' => 10, 'dsr' => 2];
        $status = $service->resolveTargetStatus($user, $metrics);
        $this->assertEquals('Met', $status);

        // 2. Target Met (at least 45 STS)
        $metrics2 = ['sts' => 45, 'dsr' => 1];
        $status2 = $service->resolveTargetStatus($user, $metrics2);
        $this->assertEquals('Met', $status2);

        // 3. Target Not Met
        $metrics3 = ['sts' => 40, 'dsr' => 1];
        $status3 = $service->resolveTargetStatus($user, $metrics3);
        $this->assertEquals('Not Met', $status3);
    }

    public function test_daily_closing_submission_flow()
    {
        $user = User::factory()->create();
        $user->assignRole('Sales-Executive');

        $service = new DailyClosingService();
        $remarks = "Did calling but clients were busy.";

        $submission = $service->submitClosing($user, $remarks);

        $this->assertDatabaseHas('day_closings', [
            'user_id' => $user->id,
            'executive_remarks' => $remarks,
            'status' => 'Pending',
        ]);

        // Expect exception when trying to submit twice in a day
        $this->expectException(\Exception::class);
        $service->submitClosing($user, "Duplicate submission");
    }

    public function test_daily_closing_on_leave_submission()
    {
        $user = User::factory()->create();
        $user->assignRole('Sales-Executive');

        $service = new DailyClosingService();
        $remarks = "Vacation leave";

        $submission = $service->submitClosing($user, $remarks, true);

        $this->assertEquals('On Leave', $submission->target_status);
        $this->assertDatabaseHas('day_closings', [
            'user_id' => $user->id,
            'target_status' => 'On Leave',
            'executive_remarks' => $remarks,
        ]);
    }

    public function test_submit_leave_on_behalf()
    {
        $manager = User::factory()->create();
        $manager->assignRole('Branch-Manager');

        $employee = User::factory()->create();
        $employee->assignRole('Sales-Executive');

        $this->actingAs($manager);

        $response = $this->post(route('day-closing.submit-leave-on-behalf'), [
            'user_id' => $employee->id,
            'leave_date' => Carbon::today()->format('Y-m-d'),
            'remarks' => 'Employee is sick today.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('day_closings', [
            'user_id' => $employee->id,
            'target_status' => 'On Leave',
            'status' => 'Approved',
            'approved_by' => $manager->id,
            'tl_remarks' => 'Employee is sick today.',
        ]);
    }
}
