# Metrics

This package provides a standard alone metrics API similar to Laravel Nova.

## Requirements

- Laravel 9.x+
- PHP 8.1+

## Getting Started

*Install via Composer*
 
```
composer require actengage/metrics
```

## Value Metric

```php
use Actengage\Metrics\Contracts\Result;
use Actengage\Metrics\Value;
use App\Models\User;
use Illuminate\Http\Request;

$metric = new class extends Value {
    public function ranges(): array {
        return [
            '5' => '5 Days',
            '10' => '10 Days',
            '15' => '15 Days',
            'P21D' => '21 Days',
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'MTD' => 'Month To Date',
            'QTD' => 'Quarter To Date',
            'YTD' => 'Year To Date',
            'ALL' => 'All Time'
        ];
    }
    public function calculate(Request $request): Result {
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
    'range' => 'MTD'
]));
```


```php
use Actengage\Metrics\Contracts\Result;
use Actengage\Metrics\Trend;
use App\Models\User;
use Illuminate\Http\Request;

$metric = new class extends Trend {
    public function calculate(Request $request): Result {
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
    'range' => 30
]));
```