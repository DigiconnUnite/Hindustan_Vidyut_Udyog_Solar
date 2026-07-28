<?php

namespace Tests\Feature;

use App\Enums\JobStage;
use App\Enums\LeadStatus;
use App\Enums\Role;
use App\Models\InstallationJob;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class LeadToJobFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_leads_index(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);
        $this->actingAs($admin)->get(route('admin.leads.index'))->assertOk();
    }

    public function test_customer_cannot_view_leads_index(): void
    {
        $customer = User::factory()->create(['role' => Role::Customer]);
        $this->actingAs($customer)->get(route('admin.leads.index'))->assertForbidden();
    }

    public function test_staff_can_create_lead_convert_to_job_and_advance_stage(): void
    {
        $staff = User::factory()->create(['role' => Role::Staff]);
        $this->actingAs($staff);

        // Create lead via the Livewire form
        Volt::test('admin.leads.create')
            ->set('name', 'Rakesh Sharma')
            ->set('phone', '9812345678')
            ->set('city', 'Pune')
            ->set('source', 'phone')
            ->call('save')
            ->assertRedirect();

        $lead = Lead::where('phone', '9812345678')->firstOrFail();
        $this->assertEquals(LeadStatus::New, $lead->status);

        // Convert to job
        Volt::test('admin.leads.show', ['lead' => $lead])
            ->set('convertAddress', '123 MG Road')
            ->set('convertCity', 'Pune')
            ->set('convertCapacity', 5)
            ->call('convertToJob')
            ->assertRedirect();

        $lead->refresh();
        $this->assertEquals(LeadStatus::Converted, $lead->status);
        $this->assertNotNull($lead->converted_job_id);

        $job = InstallationJob::findOrFail($lead->converted_job_id);
        $this->assertEquals(JobStage::Lead, $job->current_stage);
        $this->assertEquals(1, $job->stageHistory()->count());

        // A customer user should have been created and linked
        $this->assertNotNull($job->customer);
        $this->assertEquals(Role::Customer, $job->customer->role);

        // Advance stage as staff
        Volt::test('admin.jobs.show', ['job' => $job])
            ->call('advanceStage');

        $job->refresh();
        $this->assertEquals(JobStage::SiteSurvey, $job->current_stage);
        $this->assertEquals(2, $job->stageHistory()->count());
    }

    public function test_technician_can_only_advance_sequentially_on_assigned_job(): void
    {
        $staff = User::factory()->create(['role' => Role::Staff]);
        $technician = User::factory()->create(['role' => Role::Technician]);
        $customer = User::factory()->create(['role' => Role::Customer]);

        $job = InstallationJob::create([
            'customer_id' => $customer->id,
            'address' => '1 Test St',
            'current_stage' => JobStage::SiteSurvey,
        ]);
        $job->stageHistory()->create(['stage' => JobStage::SiteSurvey, 'changed_by' => $staff->id, 'changed_at' => now()]);

        // Technician not assigned -> cannot view
        $this->actingAs($technician)
            ->get(route('admin.jobs.show', $job))
            ->assertForbidden();

        // Assign technician
        $job->teamAssignments()->create([
            'user_id' => $technician->id,
            'role_on_job' => 'technician',
            'assigned_at' => now(),
        ]);

        // Now technician can view and advance sequentially
        Volt::test('admin.jobs.show', ['job' => $job])
            ->call('advanceStage');

        $job->refresh();
        $this->assertEquals(JobStage::Quotation, $job->current_stage);
    }
}
