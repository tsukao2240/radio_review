<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Post;
use App\User;
use App\RadioProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_has_fillable_attributes()
    {
        $fillable = ['id', 'user_id', 'program_id', 'program_title', 'title', 'body'];
        $post = new Post();

        $this->assertEquals($fillable, $post->getFillable());
    }

    public function test_post_belongs_to_user()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $post->user);
        $this->assertEquals($user->id, $post->user->id);
    }

    public function test_post_belongs_to_radio_program()
    {
        $program = RadioProgram::factory()->create();
        $post = Post::factory()->create(['program_id' => $program->id]);

        $this->assertInstanceOf(RadioProgram::class, $post->radioProgram);
        $this->assertEquals($program->id, $post->radioProgram->id);
    }

    public function test_post_can_be_created()
    {
        $user = User::factory()->create();
        $program = RadioProgram::factory()->create();

        $post = Post::create([
            'user_id' => $user->id,
            'program_id' => $program->id,
            'program_title' => $program->title,
            'title' => 'Test Post',
            'body' => 'Test Body'
        ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'body' => 'Test Body'
        ]);
    }

    public function test_post_can_be_updated()
    {
        $post = Post::factory()->create(['title' => 'Original Title']);

        $post->update(['title' => 'Updated Title']);

        $this->assertEquals('Updated Title', $post->fresh()->title);
    }

    public function test_post_can_be_deleted()
    {
        $post = Post::factory()->create();
        $postId = $post->id;

        $post->delete();

        $this->assertDatabaseMissing('posts', ['id' => $postId]);
    }
}
