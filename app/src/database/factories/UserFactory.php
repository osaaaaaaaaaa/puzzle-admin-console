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
            'name' => $this->faker->unique()->name(),
            'achievement_id' => $this->faker->numberBetween(0, 5),
            'icon_id' => $this->faker->numberBetween(1, 7),
            'stage_id' => $this->faker->numberBetween(1, 23),
            'created_at' => $scheduled_data->format('Y-m-d H:i:s'),
            'updated_at' => $scheduled_data->modify('+1 hour')->format('Y-m-d H:i:s')
        ];
    }
}
