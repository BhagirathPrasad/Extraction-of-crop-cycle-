<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use DatabaseMigrations;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/settings/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put('/settings/profile', [
                'name' => 'Test User',
                'phone' => '1234567890',
                'organization' => 'Test Org',
                'region' => 'Test Region',
            ]);

        $response->assertRedirect();
        
        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('1234567890', $user->phone);
        $this->assertSame('Test Org', $user->organization);
        $this->assertSame('Test Region', $user->region);
    }

    public function test_profile_page_requires_authentication(): void
    {
        $response = $this->get('/settings/profile');

        $response->assertRedirect('/login');
    }

    public function test_locale_can_be_switched(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $response = $this
            ->actingAs($user)
            ->post('/settings/locale', [
                'locale' => 'hi',
            ]);

        $response->assertRedirect();
        
        $user->refresh();
        $this->assertSame('hi', $user->locale);
    }

    public function test_theme_can_be_toggled(): void
    {
        $user = User::factory()->create(['theme' => 'light']);

        $response = $this
            ->actingAs($user)
            ->post('/settings/theme');

        $response->assertRedirect();
        
        $user->refresh();
        $this->assertSame('dark', $user->theme);
    }
}
