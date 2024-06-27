<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $scheduled_data = $this->faker->dateTimeBetween('+1day', '+1year'); // 日付をランダム生成
        return [
            'user_name' => $this->faker->unique()->name(),
            'level' => $this->faker->numberBetween(1, 99),
            'exp' => $this->faker->randomNumber(5),
            'life' => $this->faker->randomNumber(1),
            'created_at' => $scheduled_data->format('Y-m-d H:i:s'),
            'updated_at' => $scheduled_data->modify('+1 hour')->format('Y-m-d H:i:s')
        ];
    }
}
