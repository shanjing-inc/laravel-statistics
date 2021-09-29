<?php

namespace Shanjing\LaravelStatistics\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Shanjing\LaravelStatistics\ExecuteGet;
use Shanjing\LaravelStatistics\Helper\DateUtils;
use Shanjing\LaravelStatistics\Models\StatisticsModel;

class ExecuteGetTest extends TestCase
{
    public function testPeriod()
    {
        // 反射（获取私有属性）
        $reflectedClass = new ReflectionClass('Shanjing\LaravelStatistics\ExecuteGet');
        $reflectedProperty = $reflectedClass->getProperty('period');
        $reflectedProperty->setAccessible(true);

        // 未设定周期（默认为 day）
        $executeGet = new ExecuteGet('test', ['item']);
        $this->assertSame('day', $reflectedProperty->getValue($executeGet));

        // 正常周期 ['year', 'quarter', 'month', 'week', 'day']
        $periods = ['year', 'quarter', 'month', 'week', 'day'];
        foreach ($periods as $period) {
            $executeGet = new ExecuteGet('test', ['item']);
            $executeGet->period($period);
            $this->assertSame($period, $reflectedProperty->getValue($executeGet));
        }

        // 异常测试 period param error!
        $this->expectExceptionMessage('period param error!');
        $executeGet = new ExecuteGet('test', ['item']);
        $executeGet->period('exception');
    }

    public function testOrderBy()
    {
        // 反射（获取私有属性）
        $reflectedClass = new ReflectionClass('Shanjing\LaravelStatistics\ExecuteGet');
        $reflectedProperty = $reflectedClass->getProperty('orderBy');
        $reflectedProperty->setAccessible(true);

        // 正序
        $executeGet = new ExecuteGet('test', ['item']);
        $executeGet->orderBy('asc');
        $this->assertSame('asc', $reflectedProperty->getValue($executeGet));

        // 其他情况（默认倒序）
        $executeGet = new ExecuteGet('test', ['item']);
        $this->assertSame('desc', $reflectedProperty->getValue($executeGet));
    }

    public function testOccurredAt()
    {
        // 反射（获取私有属性）
        $reflectedClass = new ReflectionClass('Shanjing\LaravelStatistics\ExecuteGet');
        $reflectedProperty = $reflectedClass->getProperty('occurredBetween');
        $reflectedProperty->setAccessible(true);

        // 正常日期
        $executeGet = new ExecuteGet('test', ['item']);
        $executeGet->occurredAt('20210905');
        $this->assertSame(['20210905', '20210905'], $reflectedProperty->getValue($executeGet));

        // 测试异常
        $this->expectExceptionMessage("occurredAt 必须是合法的时间, 例如 '20210901'");
        $executeGet = new ExecuteGet('test', ['item']);
        $executeGet->occurredAt('abd');
    }

    public function testOccurredBetween()
    {
        // 反射（获取私有属性）
        $reflectedClass = new ReflectionClass('Shanjing\LaravelStatistics\ExecuteGet');
        $reflectedProperty = $reflectedClass->getProperty('occurredBetween');
        $reflectedProperty->setAccessible(true);

        // 正常日期(日期大小正序)
        $executeGet = new ExecuteGet('test', ['item']);
        $executeGet->occurredBetween(['20210905', '20211005']);
        $this->assertSame(['20210905', '20211005'], $reflectedProperty->getValue($executeGet));

        // 正常日期(日期大小倒序)
        $executeGet = new ExecuteGet('test', ['item']);
        $executeGet->occurredBetween(['20211005', '20210905']);
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
        // 反射（调用私有方法）
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
        // 反射（调用私有方法）
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

    /**
     *
     * DateUtils 已经测试了，这里只需要测试补足数据就可以了
     *
     * @throws \ReflectionException
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function testFillMissedDateWithZeroValue()
    {
        // 反射（调用私有方法）
        $reflectedMethod = new ReflectionMethod(
            'Shanjing\LaravelStatistics\ExecuteGet',
            'fillMissedDateWithZeroValue'
        );
        $reflectedMethod->setAccessible(true);

        // mock
        $mock = $this->createMock(StatisticsModel::class);
        $mock->method('__get')
            ->with('day')
            ->willReturn("2021-09-06");
        $mock->method('getAttributes')
            ->willReturn([
                'day' => '2021-09-06',
                'key1' => 'value1',
                'key2' => 'value2'
            ]);


        $period = 'day';
        $occurredBetween = ['20210905', '20210907'];
        $orderBy = 'desc';
        $items = ['key1', 'key2'];
        $data = [
            $mock
        ];

        $expected = [
            [
                'day' => '2021-09-07',
                'key1' => 0,
                'key2' => 0
            ],
            [
                'day' => '2021-09-06',
                'key1' => 'value1',
                'key2' => 'value2'
            ],
            [
                'day' => '2021-09-05',
                'key1' => 0,
                'key2' => 0
            ]
        ];

        $executeGet = new ExecuteGet('test', $items);
        $this->assertSame(
            $expected,
            $reflectedMethod->invoke($executeGet, $data, $period, $occurredBetween, $orderBy, $items)
        );
    }
}
