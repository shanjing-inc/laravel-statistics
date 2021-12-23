<?php

namespace Shanjing\LaravelStatistics\Helper;

/**
 * 将查询参数格式化成对应的 sql 语句查询参数
 *
 * Class QueryParamToSqlHelper
 * @package Shanjing\LaravelStatistics\Helper
 *
 * @author lou <lou@shanjing-inc.com>
 */
class QueryParamToSqlHelper
{
    /**
     * 选择语句(选择字段)
     *
     * @param $period
     * @param $items
     * @return string
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public static function transformSelect($period, $items)
    {
        return static::periodToSql($period) . static::SelectedItemsToSql($items);
    }

    /**
     * Where 条件语句
     * @param $occurredBetween - 查询的时间段 【'20210920'， '20210929'】
     * @param $key - key 字段， 例如 'taobao'， 'jd'
     * @param $dateColumnKey -  default = 'occurred_at'
     * @return string
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public static function transformWhere($occurredBetween, $key, $dateColumnKey = 'occurred_at')
    {
        $where = '';
        // 时间范围
        if (is_array($occurredBetween) && sizeof($occurredBetween) >= intval(2)) {
            $where = "(`$dateColumnKey` >= '$occurredBetween[0]' AND `$dateColumnKey` <= '$occurredBetween[1]')";
        }

        // key
        if (! empty($key)) {
            if (!empty($where)) {
                $where = $where . " AND `key`='$key'";
            } else {
                $where = " AND `key`='$key'";
            }
        }

        return $where;
    }

    /**
     * 格式化周期字段
     *
     * year | quarter | month | week | day | default is day
     *
     * @param $period - 查询的时间段 【'20210920'， '20210929'】
     * @param $dateColumnKey - default = 'occurred_at'
     * @return string
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public static function periodToSql($period, $dateColumnKey = 'occurred_at', $dateColumnType = 'DateTime')
    {
        $format = 'DATE_FORMAT';
        if ($dateColumnType === strval('TimeStamp')) {
            $format = 'FROM_UNIXTIME';
        }

        switch ($period) {
            case strval('year'):
                return strval($format . '( ' . $dateColumnKey . ', "%Y" )  AS year');
            case strval('quarter'):
                return strval('concat(' . $format . '(' . $dateColumnKey . ', "%Y"),
                floor((' . $format . '(' . $dateColumnKey . ', "-%m")+2)/3)) AS quarter');
            case strval('month'):
                return strval($format . '( ' . $dateColumnKey . ', "%Y-%m" ) AS month');
            case strval('week'):
                return strval($format . '( ' . $dateColumnKey . ', "%Y-%u" ) AS week');
            default:
                return strval($format . '( ' . $dateColumnKey . ', "%Y-%m-%d" ) AS day');
        }
    }

    /**
     * 格式化需要统计的字段
     * 数据存储的形式为 json， 求和方法 SUM(data->"$.item")
     *
     * @param $item
     * @return string 'SUM(data->"$.item1"), SUM(data->"$.item2") ……'
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public static function selectedItemsToSql($items)
    {
        if (sizeof($items) == 0) {
            return '';
        }

        $formatItems = '';
        foreach ($items as $item) {
            $formatItems = $formatItems . ', SUM(data->"$.' . $item . '") AS ' . $item;
        }
        return $formatItems;
    }
}
