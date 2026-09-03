<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AiChatSession;
use App\Models\AiChatMessage;
use App\Models\Subject;
use App\Models\Task;
use App\Services\Ai\AiProviderFactory;
use App\Services\Ai\GroqProvider;
use App\Services\Ai\OpenCodeGoProvider;
use App\Repositories\AiChatRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiAssistantTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_resolves_default_provider_and_falls_back(): void
    {
        $factory = new AiProviderFactory();

        // 1. Force name to groq
        $provider = $factory->make('groq');
        $this->assertInstanceOf(GroqProvider::class, $provider);

        // 2. Force name to opencodego (fails if key is blank/empty, so it falls back to groq config)
        config(['ai.providers.opencodego.key' => '']);
        $fallbackProvider = $factory->make('opencodego');
        $this->assertInstanceOf(GroqProvider::class, $fallbackProvider);

        // 3. Set opencodego key to see if it correctly instantiates OpenCodeGoProvider
        config(['ai.providers.opencodego.key' => 'test-key']);
        $opencodegoProvider = $factory->make('opencodego');
        $this->assertInstanceOf(OpenCodeGoProvider::class, $opencodegoProvider);
    }

    public function test_ai_chat_repository_can_manage_sessions_and_fork(): void
    {
        $user = User::factory()->create();
        $repository = new AiChatRepository();

        // 1. Create session
        $session = $repository->createSession($user->id, 'Algebra Queries');
        $this->assertEquals('Algebra Queries', $session->title);

        // 2. Add message
        $userMsg = $repository->addMessage($session->id, 'user', 'What is 2x = 4?');
        $this->assertEquals('user', $userMsg->role);
        $this->assertEquals('What is 2x = 4?', $userMsg->content);

        $assistantMsg = $repository->addMessage($session->id, 'assistant', 'x = 2.');
        $this->assertEquals('assistant', $assistantMsg->role);

        // 3. Get messages
        $messages = $repository->getMessages($session->id, $user->id);
        $this->assertCount(2, $messages);

        // 4. Fork session
        $forkedSession = $repository->forkSession($session->id, $user->id);
        $this->assertNotNull($forkedSession);
        $this->assertEquals('Fork of Algebra Queries', $forkedSession->title);

        $forkedMessages = $repository->getMessages($forkedSession->id, $user->id);
        $this->assertCount(2, $forkedMessages);
        $this->assertEquals('What is 2x = 4?', $forkedMessages[0]->content);
        $this->assertEquals('x = 2.', $forkedMessages[1]->content);
    }

    public function test_chat_persistence_web_endpoints(): void
    {
        $user = User::factory()->create();
        $repository = new AiChatRepository();

        $session = $repository->createSession($user->id, 'Old Chat');

        // 1. Fetch sessions list
        $response = $this->actingAs($user)->getJson('/ai/sessions');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['title' => 'Old Chat']);

        // 2. Fork session endpoint
        $forkResponse = $this->actingAs($user)->postJson("/ai/sessions/{$session->id}/fork");
        $forkResponse->assertStatus(201);
        $forkResponse->assertJsonFragment(['title' => 'Fork of Old Chat']);

        // 3. Delete session
        $deleteResponse = $this->actingAs($user)->deleteJson("/ai/sessions/{$session->id}");
        $deleteResponse->assertStatus(200);

        $this->assertDatabaseMissing('ai_chat_sessions', ['id' => $session->id]);
    }
}
