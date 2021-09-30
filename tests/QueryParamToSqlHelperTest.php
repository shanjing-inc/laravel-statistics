<?php

namespace Shanjing\LaravelStatistics\Tests;

use PHPUnit\Framework\TestCase;
use Shanjing\LaravelStatistics\ExecuteGet;
use Shanjing\LaravelStatistics\Helper\QueryParamToSqlHelper;

class QueryParamToSqlHelperTest extends TestCase
{

    public function testPeriodToSql()
    {
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
            $dateColumnKey = 'occurred_at';
            // 反射获取隐私参数
            $this->assertSame($value, QueryParamToSqlHelper::periodToSql($key, $dateColumnKey));
        }
    }

    public function testGetFormattedSelectedItems()
    {
        // items
        $items = ['gmv', 'order', 'volume' ];
        $expected = ', SUM(data->"$.gmv") AS gmv, SUM(data->"$.order") AS order, SUM(data->"$.volume") AS volume';
        $executeGet = new ExecuteGet('test', $items);
        // 反射获取隐私参数
        $this->assertSame($expected, QueryParamToSqlHelper::selectedItemsToSql($items));

        // items(空)
        $items = [];
        $expected = '';
        // 反射获取隐私参数
        $this->assertSame($expected, QueryParamToSqlHelper::selectedItemsToSql($items));
    }
}
