<?php

namespace Database\Factories;

use App\RadioProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

class RadioProgramFactory extends Factory
{
    protected $model = RadioProgram::class;

    public function definition()
    {
        return [
            'station_id' => $this->faker->randomElement(['TBS', 'QRR', 'LFR', 'JORF', 'FMT']),
            'title' => $this->faker->sentence(4),
            'cast' => $this->faker->name(),
            'start' => $this->faker->time('H:i'),
            'end' => $this->faker->time('H:i'),
            'info' => $this->faker->paragraph(2),
            'url' => $this->faker->url(),
            'image' => $this->faker->imageUrl(640, 480, 'radio', true),
        ];
    }
}
