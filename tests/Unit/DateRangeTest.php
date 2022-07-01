<?php

namespace Tests\Unit;

use Actengage\Metrics\DateRange;
use Carbon\CarbonInterval;
use Exception;
use Tests\TestCase;

class DateRangeTest extends TestCase
{
    public function testWeekToDateRange()
    {
        $range = DateRange::from('WTD');
        
        $this->assertEquals(new CarbonInterval('P7D'), $range->interval);
        $this->assertEquals(now()->startOfWeek(), $range->start);
        $this->assertEquals(now()->subWeek()->startOfWeek(), $range->prev()->start);
        $this->assertEquals(now()->subWeek()->endOfWeek(), $range->prev()->end);
    }

    public function testMonthToDateRange()
    {
        $range = DateRange::from('MTD');
        
        $this->assertEquals(new CarbonInterval('P1M'), $range->interval);
        $this->assertEquals(now()->startOfMonth(), $range->start);
        $this->assertEquals(now()->subMonth()->startOfMonth(), $range->prev()->start);
        $this->assertEquals(now()->subMonth()->endOfMonth(), $range->prev()->end);
    }

    public function testYearToDateRange()
    {
        $range = DateRange::from('YTD');
        
        $this->assertEquals(new CarbonInterval('P1Y'), $range->interval);
        $this->assertEquals(now()->startOfYear(), $range->start);
        $this->assertEquals(now()->subYear()->startOfYear(), $range->prev()->start);
        $this->assertEquals(now()->subYear()->endOfYear(), $range->prev()->end);
    }

    public function testTodayDateRange()
    {
        $range = DateRange::from('TODAY');
        
        $this->assertEquals(new CarbonInterval('P1D'), $range->interval);
        $this->assertEquals(now()->startOfDay(), $range->start);
        $this->assertEquals(now()->subDay()->startOfDay(), $range->prev()->start);
        $this->assertEquals( now()->subDay()->endOfDay(), $range->prev()->end);
    }

    public function testYesterdayDateRange()
    {
        $range = DateRange::from('YESTERDAY');
        
        $this->assertEquals(new CarbonInterval('P1D'), $range->interval);
        $this->assertEquals(now()->subDay()->startOfDay(), $range->start);
        $this->assertEquals(now()->subDays(2)->startOfDay(), $range->prev()->start);
        $this->assertEquals(now()->subDays(2)->endOfDay(), $range->prev()->end);
    }

    public function testNumericalDayRange()
    {
        $range = DateRange::from(15);

        $this->assertEquals(now()->subDays(15)->startOfDay(), $range->start);
        $this->assertEquals(now()->subDays(30)->startOfDay(), $range->prev()->start);
        $this->assertEquals(now()->subDays(30)->startOfDay()->add($range->interval)->subMicrosecond(), $range->prev()->end);
    }

    public function testDateIntervalFormat()
    {
        $range = DateRange::from('P15D');

        $this->assertEquals(0, $range->start->diffInSeconds(now()->subDays(15)));
        $this->assertEquals(0, $range->end->diffInSeconds(now()));
        $this->assertEquals(0, $range->prev()->start->diffInSeconds(now()->subDays(30)));
        $this->assertEquals(0, $range->prev()->end->diffInSeconds(now()->subDays(15)));
    }

    public function testInvaliddateFormat()
    {
        $this->expectException(Exception::class);

        DateRange::from('P15');
    }
}