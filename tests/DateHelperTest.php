<?php

namespace Shanjing\LaravelStatistics\Tests;

use Shanjing\LaravelStatistics\Helper\DateHelper;
use PHPUnit\Framework\TestCase;

class DateHelperTest extends TestCase
{

    public function testGetUnabridgedYears()
    {
        //
        $this->assertSame(
            ['2021', '2020', '2019', '2018'],
            DateHelper::getUnabridgedYears(['20180105', '20211030'], 'desc')
        );

        //
        $this->assertSame(
            ['2018', '2019', '2020', '2021'],
            DateHelper::getUnabridgedYears(['20180105', '20211030'], 'asc')
        );
    }

    public function testGetUnabridgedQuarter()
    {
        //
        $this->assertSame(
            ['2021-4', '2021-3', '2021-2', '2021-1', '2020-4', '2020-3'],
            DateHelper::getUnabridgedQuarter(['20200905', '20211015'], 'desc')
        );

        //
        $this->assertSame(
            ['2020-3', '2020-4', '2021-1', '2021-2', '2021-3', '2021-4'],
            DateHelper::getUnabridgedQuarter(['20200905', '20211015'], 'asc')
        );
    }

    public function testGetUnabridgedMonths()
    {
        //
        $this->assertSame(
            ['2021-05', '2021-04', '2021-03', '2021-02', '2021-01', '2020-12', '2020-11'],
            DateHelper::getUnabridgedMonths(['20201105', '20210515'], 'desc')
        );

        //
        $this->assertSame(
            ['2020-11', '2020-12', '2021-01', '2021-02', '2021-03', '2021-04', '2021-05'],
            DateHelper::getUnabridgedMonths(['20201105', '20210515'], 'asc')
        );
    }

    public function testGetUnabridgedWeeks()
    {
        //
        $this->assertSame(
            ['2021-28', '2021-27', '2021-26', '2021-25', '2021-24', '2021-23', '2021-22'],
            DateHelper::getUnabridgedWeeks(['20210605', '20210715'], 'desc')
        );

        //
        $this->assertSame(
            ['2021-22', '2021-23', '2021-24', '2021-25', '2021-26', '2021-27', '2021-28'],
            DateHelper::getUnabridgedWeeks(['20210605', '20210715'], 'asc')
        );
    }

    public function testGetUnabridgedDays()
    {
        //
        $this->assertSame(
            ['2021-06-11', '2021-06-10', '2021-06-09', '2021-06-08', '2021-06-07', '2021-06-06', '2021-06-05'],
            DateHelper::getUnabridgedDays(['20210605', '20210611'], 'desc')
        );

        //
        $this->assertSame(
            ['2021-06-05', '2021-06-06', '2021-06-07', '2021-06-08', '2021-06-09', '2021-06-10', '2021-06-11'],
            DateHelper::getUnabridgedDays(['20210605', '20210611'], 'asc')
        );
    }
}
