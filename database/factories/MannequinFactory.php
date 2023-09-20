<?php

namespace Database\Factories;

use App\Models\Mannequin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mannequin>
 */
class MannequinFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Mannequin::class;

    public function definition()
    {
        return [
            'po' => $this->faker->randomNumber(),
            'itemref' => $this->faker->word,
            'company' => $this->faker->company,
            'category' => $this->faker->word,
            'type' => $this->faker->word,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'description' => $this->faker->sentence,
            'images' => 'image_path.jpg',
            'file' => 'file_path.doc',
            'pdf' => 'pdf_path.pdf',
            'addedBy' => 'admin@example.com',
            'activeStatus' => 1, // Or you can use a specific value like 1
        ];
    }
}
