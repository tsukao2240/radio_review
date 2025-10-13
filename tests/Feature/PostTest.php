<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\Post;
use App\RadioProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_post_creation_page()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $program = RadioProgram::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('post.review', ['id' => $program->id]));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_view_post_creation_page()
    {
        $program = RadioProgram::factory()->create();

        $response = $this->get(route('post.review', ['id' => $program->id]));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_create_post()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $program = RadioProgram::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('post.store', ['id' => $program->id]), [
                'title' => 'Test Post Title',
                'body' => 'Test Post Body',
                'program_id' => $program->id,
                'program_title' => $program->title
            ]);

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'program_id' => $program->id,
            'title' => 'Test Post Title',
            'body' => 'Test Post Body'
        ]);
    }

    public function test_post_creation_requires_title()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $program = RadioProgram::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('post.store', ['id' => $program->id]), [
                'body' => 'Test Post Body',
                'program_id' => $program->id
            ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_post_creation_requires_body()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $program = RadioProgram::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('post.store', ['id' => $program->id]), [
                'title' => 'Test Post Title',
                'program_id' => $program->id
            ]);

        $response->assertSessionHasErrors('body');
    }

    public function test_user_can_view_all_posts()
    {
        Post::factory()->count(5)->create();

        $response = $this->get(route('review.view'));

        $response->assertStatus(200);
    }

    public function test_user_can_view_posts_for_specific_program()
    {
        $program = RadioProgram::factory()->create();
        Post::factory()->count(3)->create(['program_id' => $program->id]);

        $response = $this->get(route('review.list', [
            'station_id' => $program->station_id,
            'title' => $program->title
        ]));

        $response->assertStatus(200);
    }
}
