<?php

namespace Tests\Unit;

use Actengage\Metrics\Contracts\Result;
use Actengage\Metrics\Value;
use Faker\Factory;
use Illuminate\Http\Request;
use Tests\TestCase;
use Tests\User;

class ValueMetricTest extends TestCase
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
            'created_at' => now(),
        ]))->map(function ($value) {
            return $value();
        })->all());
    }

    public function testCount()
    {
        $metric = new class extends Value
        {
            public function ranges(): array
            {
                return [
                    '5' => '5 Days',
                    '10' => '10 Days',
                    '15' => '15 Days',
                    'P21D' => '21 Days',
                    'TODAY' => 'Today',
                    'YESTERDAY' => 'Yesterday',
                    'MTD' => 'Month To Date',
                    'QTD' => 'Quarter To Date',
                    'YTD' => 'Year To Date',
                    'ALL' => 'All Time',
                ];
            }

            public function calculate(Request $request): Result
            {
                return $this
                    ->title('Total Users')
                    ->description('The total number of users.')
                    ->timezone('EST')
                    ->range('P21D')
                    ->count(User::class)
                    ->prefix('#')
                    ->suffix('users');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'MTD',
        ]));

        $this->assertNotNull($result->metric->title);
        $this->assertNotNull($result->metric->description);
        $this->assertEquals(100, $result->value);
        $this->assertEquals('#', $result->prefix);
        $this->assertEquals('users', $result->suffix);
    }

    public function testAverage()
    {
        $metric = new class extends Value
        {
            public function calculate(Request $request): Result
            {
                return $this->average(User::class, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'daily',
        ]));

        $this->assertGreaterThan(3, $result->value);
    }

    public function testMax()
    {
        $metric = new class extends Value
        {
            public function calculate(Request $request): Result
            {
                return $this->max(User::class, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'daily',
        ]));

        $this->assertGreaterThan(0, $result->value);
    }

    public function testMin()
    {
        $metric = new class extends Value
        {
            public function calculate(Request $request): Result
            {
                return $this->min(User::class, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'daily',
        ]));

        $this->assertGreaterThan(0, $result->value);
    }

    public function testSum()
    {
        $metric = new class extends Value
        {
            public function calculate(Request $request): Result
            {
                return $this->sum(User::class, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'daily',
        ]));

        $this->assertGreaterThan(3000, $result->value);
    }
}
