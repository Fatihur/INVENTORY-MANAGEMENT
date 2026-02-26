<?php

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => 'SKU-' . fake()->unique()->numerify('#####'),
            'code' => fn (array $attributes) => $attributes['sku'],
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'unit' => fake()->randomElement(['pcs', 'kg', 'box', 'meter', 'liter']),
            'category' => fake()->optional()->word(),
            'cost_price' => fake()->randomFloat(2, 10, 1000),
            'selling_price' => fake()->randomFloat(2, 20, 2000),
            'min_stock' => fake()->numberBetween(0, 50),
            'max_stock' => fake()->optional()->numberBetween(100, 1000),
            'safety_stock' => fake()->numberBetween(0, 20),
            'target_stock' => fake()->optional()->numberBetween(50, 500),
            'lead_time_days' => fake()->numberBetween(1, 30),
            'track_batch' => fake()->boolean(20),
            'track_serial' => fake()->boolean(10),
            'is_active' => true,
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function ($product) {
            // Create stock records for all warehouses
            $warehouses = Warehouse::where('is_active', true)->get();
            if ($warehouses->isEmpty()) {
                $warehouses = Warehouse::factory()->count(1)->create(['is_active' => true]);
            }

            foreach ($warehouses as $warehouse) {
                $product->stocks()->create([
                    'warehouse_id' => $warehouse->id,
                    'qty_on_hand' => 0,
                ]);
            }
        });
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
