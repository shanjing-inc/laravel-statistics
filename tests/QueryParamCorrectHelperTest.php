<?php

namespace Shanjing\LaravelStatistics\Tests;

use PHPUnit\Framework\TestCase;
use Shanjing\LaravelStatistics\Helper\QueryParamCorrectHelper;

class QueryParamCorrectHelperTest extends TestCase
{
    public function testCorrectPeriod()
    {
        // 正常周期 ['year', 'quarter', 'month', 'week', 'day']
        $periods = ['year', 'quarter', 'month', 'week', 'day'];
        foreach ($periods as $period) {
            $this->assertSame($period, QueryParamCorrectHelper::correctPeriod($period));
        }

        // 异常测试 period param error!
        $this->expectExceptionMessage('period param type error!');
        QueryParamCorrectHelper::correctPeriod('exception');
    }


    public function testCorrectOrderBy()
    {
        // 正序
        $this->assertSame('asc', QueryParamCorrectHelper::correctOrderBy('asc'));

        // 其他情况（默认倒序）
        $this->assertSame('desc', QueryParamCorrectHelper::correctOrderBy('any'));
    }


    public function testCorrectOccurredAt()
    {
        // 正常日期
        $this->assertSame(
            ['20210905', '20210905'],
            QueryParamCorrectHelper::correctOccurredAt('20210905')
        );

        // 测试异常
        $this->expectExceptionMessage("occurredAt 必须是合法的时间, 例如 '20210901'");
        QueryParamCorrectHelper::correctOccurredAt('abd');
    }


    public function testCorrectOccurredBetween()
    {
        // 正常日期(日期大小正序)
        $this->assertSame(
            ['20210905', '20211005'],
            QueryParamCorrectHelper::correctOccurredBetween(['20210905', '20211005'])
        );

        // 正常日期(日期大小倒序)
        $this->assertSame(
            ['20210905', '20211005'],
            QueryParamCorrectHelper::correctOccurredBetween(['20211005', '20210905'])
        );

        // 测试异常([])
        $this->expectExceptionMessage("occurredBetween error！");
        QueryParamCorrectHelper::correctOccurredBetween([]);

        // 测试异常( size < 2)
        $this->expectExceptionMessage("occurredBetween error！");
        QueryParamCorrectHelper::correctOccurredBetween(['20211005']);

        // 测试异常( 日期不合法 )
        $this->expectExceptionMessage("invalid occurredBetween date!");
        QueryParamCorrectHelper::correctOccurredBetween(['sdgsag']);
    }
}
