<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ChatbotTest extends TestCase
{
    public function test_guest_cannot_access_chatbot_endpoint(): void
    {
        $response = $this->postJson(route('chatbot.message'), [
            'message' => 'Hello',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_send_message_and_receive_response(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('chatbot.message'), [
            'message' => 'what is ndvi',
            'lang' => 'en',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'text',
                'command',
                'success',
            ])
            ->assertJson([
                'success' => true,
                'command' => null,
            ]);

        $this->assertStringContainsString('NDVI', $response->json('text'));
    }

    public function test_chatbot_returns_navigation_command_for_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('chatbot.message'), [
            'message' => 'please open the dashboard page',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'command' => 'open_dashboard',
                'success' => true,
            ]);
    }

    public function test_chatbot_supports_hindi_language(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('chatbot.message'), [
            'message' => 'रबी सीजन के लिए फसलों के सुझाव दें',
            'lang' => 'hi',
        ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('खरीफ', $response->json('text'));
    }
}
