<?php

namespace Shanjing\LaravelStatistics\Tests;

use PHPUnit\Framework\TestCase;
use Shanjing\LaravelStatistics\Helper\QueryResultProcessHelper;
use Shanjing\LaravelStatistics\Models\StatisticsModel;

class QueryResultProcessHelperTest extends TestCase
{

    /**
     *
     * DateHelper 已经测试了，这里只需要测试补足数据就可以了
     *
     * @throws \ReflectionException
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function testFillMissedDateAndChangeNullValueWithZero()
    {
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

        // 补足缺失日期时，使用的填充数据
        $fillValue = [];
        foreach ($data[0]->getAttributes() as $key => $value) {
            $fillValue[$key] = 0;
        }
        $this->assertSame(
            $expected,
            QueryResultProcessHelper::fillMissedDateAndChangeNullValueWithZero(
                $data,
                $period,
                $occurredBetween,
                $orderBy,
                $fillValue
            )
        );
    }
}
