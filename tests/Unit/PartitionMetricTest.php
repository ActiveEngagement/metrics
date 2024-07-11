<?php

namespace Tests\Unit;

use Actengage\Metrics\Contracts\Result;
use Actengage\Metrics\Partition;
use Actengage\Metrics\Results\PartitionResult;
use Faker\Factory;
use Illuminate\Http\Request;
use Tests\TestCase;
use Tests\User;

class PartitionMetricTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $faker = app(Factory::class)->create();

        User::insert(collect(array_fill(0, 100, fn () => [
            'name' => $faker->name(),
            'email' => $faker->email(),
            'password' => $faker->password(),
            'age' => $faker->numberBetween(3, 99),
            'created_at' => $faker->dateTimeBetween('-60 days', 'now'),
        ]))->map(function ($value) {
            return $value();
        })->all());
    }

    public function testCount()
    {
        $metric = new class extends Partition
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->title('User Registrations')
                    ->description('User registrations trending by day.')
                    ->count(User::class, 'age');
            }
        };

        $result = $metric->resolve(request());

        $this->assertInstanceOf(PartitionResult::class, $result);
    }

    public function testSum()
    {
        $metric = new class extends Partition
        {
            public function calculate(Request $request): Result
            {
                return $this->sum(User::class, 'id', 'age');
            }
        };

        $result = $metric->resolve(request());

        $this->assertInstanceOf(PartitionResult::class, $result);
    }
}
