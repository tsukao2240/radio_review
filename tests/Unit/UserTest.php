<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\User;
use App\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_fillable_attributes()
    {
        $fillable = ['name', 'email', 'password'];
        $user = new User();

        $this->assertEquals($fillable, $user->getFillable());
    }

    public function test_user_has_hidden_attributes()
    {
        $hidden = ['password', 'remember_token'];
        $user = new User();

        $this->assertEquals($hidden, $user->getHidden());
    }

    public function test_user_can_be_created()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);
    }

    public function test_user_password_is_hashed()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123')
        ]);

        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_user_has_many_posts()
    {
        $user = User::factory()->create();
        $posts = Post::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->posts);
        $this->assertInstanceOf(Post::class, $user->posts->first());
    }

    public function test_user_email_is_cast_to_datetime()
    {
        $user = new User();
        $casts = $user->getCasts();

        $this->assertArrayHasKey('email_verified_at', $casts);
        $this->assertEquals('datetime', $casts['email_verified_at']);
    }

    public function test_user_can_be_updated()
    {
        $user = User::factory()->create(['name' => 'Original Name']);

        $user->update(['name' => 'Updated Name']);

        $this->assertEquals('Updated Name', $user->fresh()->name);
    }

    public function test_user_can_be_deleted()
    {
        $user = User::factory()->create();
        $userId = $user->id;

        $user->delete();

        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }
}
