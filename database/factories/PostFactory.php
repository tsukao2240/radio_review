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
            'rating' => $this->faker->randomFloat(1, 1, 5),
            'station_id' => $this->faker->randomElement(['TBS', 'QRR', 'LFR', 'INT', 'FMT', 'FMJ', 'JORF']),
            'likes_count' => 0,
            'comments_count' => 0,
        ];
    }
}
