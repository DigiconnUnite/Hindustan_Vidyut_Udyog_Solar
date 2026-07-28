<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\BlogPost;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminModulesSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_staff_can_reach_every_admin_module(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);
        $product = Product::create(['name' => 'Test Panel', 'capacity_kw' => 3, 'is_active' => true]);
        $post = BlogPost::create(['title' => 'Test', 'slug' => 'test', 'body' => 'body', 'author_id' => $admin->id, 'status' => 'draft']);

        $routes = [
            'admin.dashboard', 'admin.leads.index', 'admin.leads.create',
            'admin.jobs.index', 'admin.team.index', 'admin.blog.index',
            'admin.blog.create', 'admin.products.index', 'admin.products.create',
            'admin.users.index', 'admin.settings.edit',
        ];

        foreach ($routes as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }

        $this->actingAs($admin)->get(route('admin.products.edit', $product))->assertOk();
        $this->actingAs($admin)->get(route('admin.blog.edit', $post))->assertOk();
    }

    public function test_staff_cannot_reach_admin_only_modules(): void
    {
        $staff = User::factory()->create(['role' => Role::Staff]);

        $this->actingAs($staff)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.settings.edit'))->assertForbidden();
    }

    public function test_technician_can_reach_my_jobs_but_not_admin_modules(): void
    {
        $technician = User::factory()->create(['role' => Role::Technician]);

        $this->actingAs($technician)->get(route('technician.jobs.index'))->assertOk();
        $this->actingAs($technician)->get(route('admin.leads.index'))->assertForbidden();
    }

    public function test_customer_can_reach_portal_but_not_admin(): void
    {
        $customer = User::factory()->create(['role' => Role::Customer]);

        $this->actingAs($customer)->get(route('portal.installation'))->assertOk();
        $this->actingAs($customer)->get(route('portal.documents'))->assertOk();
        $this->actingAs($customer)->get(route('admin.dashboard'))->assertForbidden();
    }
}
