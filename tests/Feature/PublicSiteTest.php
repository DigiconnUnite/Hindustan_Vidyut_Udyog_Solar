<?php

namespace Tests\Feature;

use App\Enums\BlogStatus;
use App\Enums\Role;
use App\Models\BlogPost;
use App\Models\Lead;
use App\Models\Product;
use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewLeadNotification;
use Livewire\Volt\Volt;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_render(): void
    {
        foreach (['home', 'about', 'services', 'products', 'gallery', 'contact', 'quote.create', 'blog.index'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_published_post_is_visible_draft_is_not(): void
    {
        $author = User::factory()->create(['role' => Role::Admin]);
        $published = BlogPost::create(['title' => 'Live', 'slug' => 'live', 'body' => 'x', 'author_id' => $author->id, 'status' => BlogStatus::Published, 'published_at' => now()]);
        $draft = BlogPost::create(['title' => 'Draft', 'slug' => 'draft', 'body' => 'x', 'author_id' => $author->id, 'status' => BlogStatus::Draft]);

        $this->get(route('blog.show', $published))->assertOk();
        $this->get(route('blog.show', $draft))->assertNotFound();
    }

    public function test_quote_form_creates_lead_and_notifies_staff(): void
    {
        Notification::fake();

        $staff = User::factory()->create(['role' => Role::Staff]);

        Volt::test('public.quote.create')
            ->set('name', 'Suresh Patil')
            ->set('phone', '9876543210')
            ->set('city', 'Nashik')
            ->call('submit')
            ->assertHasNoErrors();

        $lead = Lead::where('phone', '9876543210')->first();
        $this->assertNotNull($lead);
        $this->assertEquals('website', $lead->source->value);

        $quoteRequest = QuoteRequest::where('phone', '9876543210')->first();
        $this->assertNotNull($quoteRequest);
        $this->assertEquals($lead->id, $quoteRequest->converted_lead_id);

        Notification::assertSentTo($staff, NewLeadNotification::class);
    }

    public function test_only_active_products_show_publicly(): void
    {
        Product::create(['name' => 'Active One', 'capacity_kw' => 3, 'is_active' => true]);
        Product::create(['name' => 'Hidden One', 'capacity_kw' => 5, 'is_active' => false]);

        $response = $this->get(route('products'));

        $response->assertOk();
        $response->assertSee('Active One');
        $response->assertDontSee('Hidden One');
    }
}
