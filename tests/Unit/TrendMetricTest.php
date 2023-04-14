<?php

namespace Tests\Unit;

use Actengage\Metrics\Contracts\Result;
use Actengage\Metrics\DateRange;
use Actengage\Metrics\Results\TrendResult;
use Actengage\Metrics\Trend;
use Faker\Factory;
use Illuminate\Http\Request;
use Tests\TestCase;
use Tests\User;

class TrendMetricTest extends TestCase
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
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->title('User Registrations')
                    ->description('User registrations trending by day.')
                    ->twelveHourTime()
                    ->range($request->range)
                    ->count(User::class, Trend::BY_DAYS)
                    ->prefix('#')
                    ->suffix('users');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 30,
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
        $this->assertNotNull($result->metric->title);
        $this->assertNotNull($result->metric->description);
        $this->assertEquals('#', $result->prefix);
        $this->assertEquals('users', $result->suffix);
        $this->assertCount(31, $result->trend);
    }

    public function testCountByMonths()
    {
        $this->range = DateRange::from(365);

        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range(365)
                    ->countByMonths(User::class);
            }
        };

        $result = $metric->resolve(request());

        $this->assertInstanceOf(TrendResult::class, $result);
    }

    public function testCountByWeeks()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->countByWeeks(User::class);
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'P5W',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
        $this->assertCount(6, $result->trend);
    }

    public function testCountByDays()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range(30)
                    ->countByDays(User::class);
            }
        };

        $result = $metric->resolve(request());

        $this->assertInstanceOf(TrendResult::class, $result);
        $this->assertCount(31, $result->trend);
    }

    public function testCountByHours()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->countByHours(User::class);
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'day',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
    }

    public function testCountByMinutes()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->countByMinutes(User::class);
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'day',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
    }

    public function testAverage()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->average(User::class, Trend::BY_MONTHS, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'month',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
        $this->assertCount(2, $result->trend);
    }

    public function testAverageByMonths()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->averageByMonths(User::class, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'month',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
        $this->assertCount(2, $result->trend);
    }

    public function testAverageByWeeks()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->averageByWeeks(User::class, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'year',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
        $this->assertCount(53, $result->trend);
    }

    public function testAverageByDays()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->averageByDays(User::class, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'month',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
    }

    public function testAverageByHours()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->averageByHours(User::class, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'day',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
    }

    public function testAverageByMinutes()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->averageByMinutes(User::class, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'hour',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
    }

    public function testSum()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->sum(User::class, Trend::BY_MONTHS, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'month',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
        $this->assertCount(2, $result->trend);
    }

    public function testSumByMonths()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->sumByMonths(User::class, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'year',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
    }

    public function testSumByWeeks()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->sumByWeeks(User::class, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'year',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
    }

    public function testSumByDays()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->sumByDays(User::class, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'month',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
    }

    public function testSumByHours()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->sumByHours(User::class, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'day',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
    }

    public function testSumByMinutes()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->sumByMinutes(User::class, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'hour',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
    }

    public function testMax()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->max(User::class, Trend::BY_MONTHS, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'month',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
    }

    public function testMaxByMonths()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->maxByMonths(User::class, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'month',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
    }

    public function testMaxByWeeks()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->maxByWeeks(User::class, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'month',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
    }

    public function testMaxByDays()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->maxByDays(User::class, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'month',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
    }

    public function testMaxByHours()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->maxByHours(User::class, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'month',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
    }

    public function testMaxByMinutes()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->maxByMinutes(User::class, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'month',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
    }

    public function testMin()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->min(User::class, Trend::BY_MONTHS, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'month',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
    }

    public function testMinByMonths()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->minByMonths(User::class, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'month',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
    }

    public function testMinByWeeks()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->minByWeeks(User::class, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'month',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
    }

    public function testMinByDays()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->minByDays(User::class, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'month',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
    }

    public function testMinByHours()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->minByHours(User::class, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'month',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
    }

    public function testMinByMinutes()
    {
        $metric = new class extends Trend
        {
            public function calculate(Request $request): Result
            {
                return $this
                    ->twelveHourTime()
                    ->range($request->range)
                    ->minByMinutes(User::class, 'age');
            }
        };

        $result = $metric->resolve(request()->merge([
            'range' => 'month',
        ]));

        $this->assertInstanceOf(TrendResult::class, $result);
    }
}
