<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $rating = $this->faker->numberBetween(2, 10) * 0.5;

        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'content' => $this->faker->realTextBetween(30, 150),
            'rating'  => $rating,
            'status'  => $this->faker->randomElement(['approved', 'approved', 'approved', 'pending']),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function negative(): static
    {
        $rating = $this->faker->numberBetween(1, 4) * 0.5;

        return $this->state(fn (array $attributes) => [
            'rating' => $rating,
            'content' => 'Доставка задержалась, а упаковка была помята. Расстроен.',
            'status' => 'pending',
        ]);
    }
}
