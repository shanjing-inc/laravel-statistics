<?php

namespace Shanjing\LaravelStatistics\Helper;

use Carbon\Carbon;
use Exception;

/**
 *
 * 预处理请求参数，过滤不合规的参数
 *
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
            return static::makeUpTimeToSecond([$occurredAt, $occurredAt]);
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
        if (sizeof($occurredBetween) < (int) 2) {
            throw new Exception("occurredBetween error！");
        }

        // 是否是合法的时间
        $startTimeStamp = strtotime($occurredBetween[0]);
        $endTimeStamp   = strtotime($occurredBetween[1]);
        if (!ctype_digit($startTimeStamp) || !ctype_digit($endTimeStamp)) {
            throw new Exception("invalid occurredBetween date!");
        }

        // 调整时间大小顺序
        $ret = $occurredBetween;
        if ($startTimeStamp > $endTimeStamp) {
            $ret = [
                $occurredBetween[1],
                $occurredBetween[0]
            ];
        }

        return static::makeUpTimeToSecond($ret);
    }

    /**
     * 补足日期， 精确到秒
     *
     * @param array $occurredBetween
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public static function makeUpTimeToSecond(array $occurredBetween)
    {
        $ret = $occurredBetween;
        // 如果时间精确到 「秒」不处理，否则补足到秒[Ymd000000， Ymd235959]
        // 开始日期
        if (strlen($ret[0]) == (int) 4) {
            // 日期到年
            $ret[0] .= '0101000000';
        } elseif (strlen($ret[0]) == (int) 6) {
            // 日期到月
            $ret[0] .= '01000000';
        } elseif (strlen($ret[0]) == (int) 8) {
            // 日期到天
            $ret[0] .= '000000';
        }

        // 结束日期
        if (strlen($ret[1]) == (int) 4) {
            // 日期到年
            $ret[1] .= '1231235959';
        } elseif (strlen($ret[1]) == (int) 6) {
            // 日期到月
            $carbon = Carbon::createFromFormat('Ym', $ret[1]);
            $ret[1] = $ret[1] . $carbon->daysInMonth . '235959';
        } elseif (strlen($ret[1]) == (int) 8) {
            // 日期到天
            $ret[1] .= '235959';
        }

        return $ret;
    }
}
