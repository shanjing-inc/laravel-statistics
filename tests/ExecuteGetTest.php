<?php

namespace Shanjing\LaravelStatistics\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Shanjing\LaravelStatistics\ExecuteGet;

class ExecuteGetTest extends TestCase
{
    public function testPeriod()
    {
        // 反射
        $reflectedClass = new ReflectionClass('Shanjing\LaravelStatistics\ExecuteGet');
        $reflectedProperty = $reflectedClass->getProperty('period');
        $reflectedProperty->setAccessible(true);

        // 正常周期
        $periods = ['year', 'quarter', 'month', 'week', 'day'];
        foreach ($periods as $period) {
            $executeGet = new ExecuteGet('test', ['item']);
            $executeGet->period($period);
            // 反射获取隐私参数
            $this->assertSame($period, $reflectedProperty->getValue($executeGet));
        }

        // 未设定周期
        $executeGet = new ExecuteGet('test', ['item']);
        // 反射获取隐私参数
        $this->assertSame('day', $reflectedProperty->getValue($executeGet));

        // 异常测试
        $this->expectExceptionMessage('period param error!');
        $executeGet = new ExecuteGet('test', ['item']);
        $executeGet->period('exception');
    }

    public function testOrderBy()
    {
        // 反射
        $reflectedClass = new ReflectionClass('Shanjing\LaravelStatistics\ExecuteGet');
        $reflectedProperty = $reflectedClass->getProperty('orderBy');
        $reflectedProperty->setAccessible(true);

        // 正序
        $executeGet = new ExecuteGet('test', ['item']);
        $executeGet->orderBy('asc');
        // 反射获取隐私参数
        $this->assertSame('asc', $reflectedProperty->getValue($executeGet));

        // 其他情况（默认倒序）
        $executeGet = new ExecuteGet('test', ['item']);
        // 反射获取隐私参数
        $this->assertSame('desc', $reflectedProperty->getValue($executeGet));
    }

    public function testOccurredAt()
    {
        // 反射
        $reflectedClass = new ReflectionClass('Shanjing\LaravelStatistics\ExecuteGet');
        $reflectedProperty = $reflectedClass->getProperty('occurredBetween');
        $reflectedProperty->setAccessible(true);

        // 正常日期
        $executeGet = new ExecuteGet('test', ['item']);
        $executeGet->occurredAt('20210905');
        // 反射获取隐私参数
        $this->assertSame(['20210905', '20210905'], $reflectedProperty->getValue($executeGet));

        // 测试异常
        $this->expectExceptionMessage("occurredAt 必须是合法的时间, 例如 '20210901'");
        $executeGet = new ExecuteGet('test', ['item']);
        $executeGet->occurredAt('abd');
    }

    public function testOccurredBetween()
    {
        // 反射
        $reflectedClass = new ReflectionClass('Shanjing\LaravelStatistics\ExecuteGet');
        $reflectedProperty = $reflectedClass->getProperty('occurredBetween');
        $reflectedProperty->setAccessible(true);

        // 正常日期(日期大小正序)
        $executeGet = new ExecuteGet('test', ['item']);
        $executeGet->occurredBetween(['20210905', '20211005']);
        // 反射获取隐私参数
        $this->assertSame(['20210905', '20211005'], $reflectedProperty->getValue($executeGet));

        // 正常日期(日期大小倒序)
        $executeGet = new ExecuteGet('test', ['item']);
        $executeGet->occurredBetween(['20211005', '20210905']);
        // 反射获取隐私参数
        $this->assertSame(['20210905', '20211005'], $reflectedProperty->getValue($executeGet));

        // 测试异常([])
        $this->expectExceptionMessage("occurredBetween error！");
        $executeGet = new ExecuteGet('test', ['item']);
        $executeGet->occurredBetween([]);

        // 测试异常( size < 2)
        $this->expectExceptionMessage("occurredBetween error！");
        $executeGet = new ExecuteGet('test', ['item']);
        $executeGet->occurredBetween(['20211005']);

        // 测试异常( 日期不合法 )
        $this->expectExceptionMessage("invalid occurredBetween date!");
        $executeGet = new ExecuteGet('test', ['item']);
        $executeGet->occurredBetween(['20211005']);
    }

    public function testGetFormattedSelectedPeriod()
    {
        // 反射
        $reflectedMethod = new ReflectionMethod(
            'Shanjing\LaravelStatistics\ExecuteGet',
            'getFormattedSelectedPeriod'
        );
        $reflectedMethod->setAccessible(true);

        // period and return
        $arr = [
            'year'     => 'DATE_FORMAT( occurred_at, "%Y" )  AS year',
            'quarter'  => 'concat(DATE_FORMAT(occurred_at, "%Y"), 
                floor((DATE_FORMAT(occurred_at, "-%m")+2)/3)) AS quarter',
            'month'    => 'DATE_FORMAT( occurred_at, "%Y-%m" ) AS month',
            'week'     => 'DATE_FORMAT( occurred_at, "%Y-%u" ) AS week',
            'day'      => 'DATE_FORMAT( occurred_at, "%Y-%m-%d" ) AS day'
        ];
        foreach ($arr as $key => $value) {
            $executeGet = new ExecuteGet('test', ['item']);
            $executeGet->period($key);
            // 反射获取隐私参数
            $this->assertSame($value, $reflectedMethod->invoke($executeGet, $key));
        }
    }

    public function testGetFormattedSelectedItems()
    {
        // 反射
        $reflectedMethod = new ReflectionMethod(
            'Shanjing\LaravelStatistics\ExecuteGet',
            'getFormattedSelectedItems'
        );
        $reflectedMethod->setAccessible(true);

        // items
        $items = ['gmv', 'order', 'volume' ];
        $expected = ', SUM(data->"$.gmv") AS gmv, SUM(data->"$.order") AS order, SUM(data->"$.volume") AS volume';
        $executeGet = new ExecuteGet('test', $items);
        // 反射获取隐私参数
        $this->assertSame($expected, $reflectedMethod->invoke($executeGet, $items));

        // items(空)
        $items = [];
        $expected = '';
        $executeGet = new ExecuteGet('test', $items);
        // 反射获取隐私参数
        $this->assertSame($expected, $reflectedMethod->invoke($executeGet, $items));
    }
}
