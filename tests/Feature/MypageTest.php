<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\Post;
use App\RadioProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MypageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_mypage()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('myreview.view'));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_mypage()
    {
        $response = $this->get(route('myreview.view'));

        $response->assertRedirect(route('login'));
    }

    public function test_mypage_shows_only_user_posts()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Post::factory()->create([
            'user_id' => $user->id,
            'title' => 'My Post'
        ]);

        Post::factory()->create([
            'user_id' => $otherUser->id,
            'title' => 'Other User Post'
        ]);

        $response = $this->actingAs($user)
            ->get(route('myreview.view'));

        $response->assertSee('My Post');
        $response->assertDontSee('Other User Post');
    }

    public function test_user_can_view_edit_page()
    {
        $user = User::factory()->create();
        $program = RadioProgram::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'program_id' => $program->id
        ]);

        $response = $this->actingAs($user)
            ->get(route('myreview.edit', ['program_id' => $program->id]));

        $response->assertStatus(200);
    }

    public function test_user_can_update_own_post()
    {
        $user = User::factory()->create();
        $program = RadioProgram::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'program_id' => $program->id,
            'title' => 'Original Title'
        ]);

        $response = $this->actingAs($user)
            ->post(route('myreview.update', ['program_id' => $program->id]), [
                'title' => 'Updated Title',
                'body' => 'Updated Body'
            ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
            'body' => 'Updated Body'
        ]);
    }

    public function test_user_can_delete_own_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->post(route('myreview.delete'), [
                'program_id' => $post->program_id
            ]);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_user_cannot_edit_other_user_post()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $program = RadioProgram::factory()->create();

        $post = Post::factory()->create([
            'user_id' => $otherUser->id,
            'program_id' => $program->id
        ]);

        $response = $this->actingAs($user)
            ->get(route('myreview.edit', ['program_id' => $program->id]));

        $response->assertStatus(403);
    }

    public function test_post_update_rate_limiting_works()
    {
        $user = User::factory()->create();
        $program = RadioProgram::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'program_id' => $program->id
        ]);

        for ($i = 0; $i < 11; $i++) {
            $response = $this->actingAs($user)
                ->post(route('myreview.update', ['program_id' => $program->id]), [
                    'title' => 'Title ' . $i,
                    'body' => 'Body ' . $i
                ]);
        }

        $response->assertStatus(429);
    }
}
