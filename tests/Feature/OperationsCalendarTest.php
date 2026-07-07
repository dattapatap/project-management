<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use App\Models\DepartmentProjects;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OperationsCalendarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic roles
        Role::firstOrCreate(['name' => 'Admin']);
        Role::firstOrCreate(['name' => 'Developer']);
        Role::firstOrCreate(['name' => 'Sales-Executive']);
    }

    public function test_unauthenticated_users_are_redirected()
    {
        $response = $this->get(route('operations.calendar.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_non_wms_users_are_aborted()
    {
        $nonWmsUser = User::factory()->create();
        $nonWmsUser->assignRole('Sales-Executive');

        $response = $this->actingAs($nonWmsUser)->get(route('operations.calendar.index'));
        $response->assertStatus(403);
    }

    public function test_wms_users_can_access_calendar()
    {
        $wmsUser = User::factory()->create();
        $wmsUser->assignRole('Developer');

        $response = $this->actingAs($wmsUser)->get(route('operations.calendar.index'));
        $response->assertStatus(200);
        $response->assertViewIs('operations.task_calendar');
    }

    public function test_events_endpoint_returns_json_events()
    {
        $wmsUser = User::factory()->create();
        $wmsUser->assignRole('Developer');

        $project = DepartmentProjects::create([
            'project_name' => 'Test Project',
            'status' => 'InProgress',
            'created_by' => $wmsUser->id,
        ]);

        $task = Task::create([
            'title' => 'Test Task 1',
            'projectid' => $project->id,
            'assigned_to' => $wmsUser->id,
            'status' => 'InProgress',
            'priority' => 'Medium',
            'startdate' => '2026-07-10',
            'enddate' => '2026-07-15',
            'created_by' => $wmsUser->id,
        ]);

        $response = $this->actingAs($wmsUser)->get(route('operations.calendar.events', [
            'start' => '2026-07-01',
            'end' => '2026-07-31'
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'title',
                'start',
                'end',
                'color',
                'textColor',
                'extendedProps' => [
                    'project',
                    'assignee',
                    'status',
                    'priority',
                    'start_date',
                    'end_date',
                    'project_id'
                ]
            ]
        ]);

        $response->assertJsonFragment([
            'title' => 'Test Task 1',
            'start' => '2026-07-10',
            'end' => '2026-07-16' // 15 + 1 day = 16
        ]);
    }
}
