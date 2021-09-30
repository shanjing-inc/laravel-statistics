<?php

namespace Shanjing\LaravelStatistics\Helper;

use Exception;

/**
 *
 * 预处理请求参数，过滤不合规的参数
 *
 * Class QueryParamCorrectHelper
 * @package Shanjing\LaravelStatistics\Helper
 *
 * @author lou <lou@shanjing-inc.com>
 */
class QueryParamCorrectHelper
{

    /**
     * 格式化周期
     *
     * @param string $period - year | quarter | month | week | day
     * @return string
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public static function correctPeriod(string $period)
    {
        switch ($period) {
            case strval('year'):
            case strval('quarter'):
            case strval('month'):
            case strval('week'):
            case strval('day'):
                return $period;
            default:
                throw new Exception("period param type error!");
        }
    }

    /**
     * 排序方式
     *
     * @param $orderBy - asc | desc
     * @return string
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public static function correctOrderBy(string $orderBy)
    {
        if ($orderBy === strval('asc')) {
            return $orderBy;
        } else {
            return strval('desc');
        }
    }

    /**
     * 时间条件
     *
     * @param string $occurredAt
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public static function correctOccurredAt(string $occurredAt)
    {
        if (!empty($occurredAt)) {
            // 是否是合法的时间
            $occurredAtTimeStamp = strtotime($occurredAt);
            if (!ctype_digit($occurredAtTimeStamp)) {
                throw new Exception("occurredAt 必须是合法的时间, 例如 '20210901'");
            }
            return [$occurredAt, $occurredAt];
        }

        return [];
    }

    /**
     * @param array $occurredBetween - 例如： ['20210901' '20210925']
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public static function correctOccurredBetween(array $occurredBetween)
    {
        // 参数判断
        if (sizeof($occurredBetween) < intval(2)) {
            throw new Exception("occurredBetween error！");
        }

        // 是否是合法的时间
        $startTimeStamp = strtotime($occurredBetween[0]);
        $endTimeStamp = strtotime($occurredBetween[1]);
        if (!ctype_digit($startTimeStamp) || !ctype_digit($endTimeStamp)) {
            throw new Exception("invalid occurredBetween date!");
        }

        // 调整时间大小顺序
        if ($startTimeStamp > $endTimeStamp) {
            return [
                $occurredBetween[1],
                $occurredBetween[0]
            ];
        } else {
            return $occurredBetween;
        }
    }
}
