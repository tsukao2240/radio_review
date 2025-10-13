<?php

namespace Database\Factories;

use App\Post;
use App\User;
use App\RadioProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'program_id' => RadioProgram::factory(),
            'program_title' => $this->faker->sentence(3),
            'title' => $this->faker->sentence(5),
            'body' => $this->faker->paragraph(3),
        ];
    }
}
