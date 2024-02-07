<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Habit;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HabitsTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_habits_view_can_be_render(): void
    {
        // Arrange
        $habits = Habit::factory(3)->create();

        // Act
        $response = $this->withoutExceptionHandling()->get('/habits');

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('habits', $habits);
    }

    public function test_that_habits_can_be_created(): void
    {
        # Arrange
        $habit = Habit::factory()->make();

        # Act
        $response = $this->withoutExceptionHandling()->post('/habits', $habit->toArray());

        # Assert
        $response->assertRedirect('/habits');
        $this->assertDatabaseHas('habits', $habit->toArray());
    }

    public function test_habits_can_be_updated(): void
    {
        $habit = Habit::factory()->create();

        $updateHabit = [
            'name' => 'updated',
            'times_per_day' => 4
        ];

        $response = $this->withoutExceptionHandling()->put("/habits/{$habit->id}", $updateHabit);
        
        $response->assertRedirect('/habits');
        $this->assertDatabaseHas('habits', ['id' => $habit->id, ...$updateHabit]);
    }

    /**
     * @dataProvider provideBadHabitData
     */
    public function test_create_habit_validation(string $missing, Array $habit): void
    {
        $response = $this->post('/habits', $habit);
        $response->assertSessionHasErrors([$missing]);
    }

    /**
     * @dataProvider provideBadHabitData
     */
    public function test_update_habit_validation(string $missing, Array $habit): void
    {
        $habitId = Habit::factory()->create()->id;

        $response = $this->put("/habits/{$habitId}", $habit);
        $response->assertSessionHasErrors([$missing]);
    }    

    public function test_habits_can_be_deleted(): void
    {
        $habitId = Habit::factory()->create()->id;

        $response = $this->withExceptionHandling()->delete("/habits/{$habitId}");

        $response->assertRedirect('/habits');
        $this->assertDatabaseMissing('habits', [
            'id' => $habitId
        ]);
    }

    public static function provideBadHabitData(): Array
    {
        $habit = Habit::factory()->make();

        return [
            'missing name' => [
                'name',
                [
                    ...$habit->toArray(),
                    'name' => null
                ]
            ], 
            'missing times_per_day' => [
                'times_per_day',
                [
                    ...$habit->toArray(),
                    'times_per_day' => null
                ]
            ]
        ];
    }
}
