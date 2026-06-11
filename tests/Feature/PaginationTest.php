<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use App\Models\Note;
use App\Models\Subject;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tasks_api_endpoint_returns_paginated_data(): void
    {
        $user = User::factory()->create();
        $subject = Subject::factory()->create(['user_id' => $user->id]);
        Task::factory()->count(15)->create(['subject_id' => $subject->id]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/tasks?per_page=5');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'current_page',
                'data',
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total',
            ],
        ]);
        
        $this->assertCount(5, $response->json('data.data'));
        $this->assertEquals(15, $response->json('data.total'));
    }

    public function test_notes_api_endpoint_returns_paginated_data(): void
    {
        $user = User::factory()->create();
        Note::factory()->count(8)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/notes?per_page=3');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'current_page',
                'data',
                'total',
            ],
        ]);
        
        $this->assertCount(3, $response->json('data.data'));
        $this->assertEquals(8, $response->json('data.total'));
    }

    public function test_subjects_api_endpoint_returns_paginated_data(): void
    {
        $user = User::factory()->create();
        Subject::factory()->count(7)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/subjects?per_page=2');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'current_page',
                'data',
                'total',
            ],
        ]);
        
        $this->assertCount(2, $response->json('data.data'));
        $this->assertEquals(7, $response->json('data.total'));
    }

    public function test_categories_api_endpoint_returns_paginated_data(): void
    {
        $user = User::factory()->create();
        Category::factory()->count(12)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/categories?per_page=5');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'current_page',
                'data',
                'total',
            ],
        ]);
        
        $this->assertCount(5, $response->json('data.data'));
        $this->assertEquals(12, $response->json('data.total'));
    }
}
