<?php

namespace  Shanjing\LaravelStatistics\Helper;

/**
 * @package Shanjing\LaravelStatistics\Helper
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 *
 * @author lou <lou@shanjing-inc.com>
 */
class DateHelper
{
    /**
     * 处理周期为年的数据填充

     * @param array $timeRange
     * @param $orderBy "asc | desc"
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public static function getUnabridgedYears(array $timeRange = null, $orderBy = 'desc')
    {
        $years = [];
        // 获取时间范围
        if (null != $timeRange && sizeof($timeRange) > 0) {
            // 设定了时间范围
            $startTimeStamp = strtotime($timeRange[0]);
            $endTimeStamp   = strtotime($timeRange[1]);
            // 相差的年数数
            $yearGap = (date('Y', $endTimeStamp) - date('Y', $startTimeStamp)) + 1;
            if ($orderBy === strval('asc')) {
                for ($i = 0; $i < $yearGap; $i++) {
                    $years[] = date('Y', strtotime("+" . $i . " year", $startTimeStamp));
                }
                // 处理结束节点
                $endYear = date('Y', $endTimeStamp);
                if (sizeof($years) > 0 && $endYear != $years[$yearGap - 1]) {
                    $years[] = $endYear;
                }
            } else {
                for ($i = 0; $i < $yearGap; $i++) {
                    $years[] = date('Y', strtotime("-" . $i . " year", $endTimeStamp));
                }
                // 处理结束节点
                $startYear = date('Y', $startTimeStamp);
                if (sizeof($years) > 0 && $startYear != $years[$yearGap - 1]) {
                    $years[] = $startYear;
                }
            }
        } else {
            // 未设定日期范围，获取最近 4 个季度的日期
            $maxYears = 10;
            if ($orderBy === strval('asc')) {
                for ($i = 1; $i <= $maxYears; $i++) {
                    $years[] = date("Y", strtotime("-" . ($maxYears - $i) . "year"));
                }
            } else {
                for ($i = 0; $i < $maxYears; $i++) {
                    $years[] = date("Y", strtotime("-" . $i . "year"));
                }
            }
        }
        return $years;
    }

    /**
     * 处理周期为季度的数据填充
     *
     * @param array $timeRange
     * @param $orderBy
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */

    public static function getUnabridgedQuarter(array $timeRange = null, $orderBy = 'desc')
    {
        $quarters = [];
        // 获取时间范围
        if (null != $timeRange && sizeof($timeRange) > 0) {
            // 设定了时间范围
            $startTimeStamp = strtotime($timeRange[0]);
            $endTimeStamp   = strtotime($timeRange[1]);
            // 相差的月数
            $monthGap = (date('Y', $endTimeStamp) - date('Y', $startTimeStamp)) * 12
                + date('m', $endTimeStamp) - date('m', $startTimeStamp) + 1;
            $quarterGap = floor(($monthGap + 2) / 3);
            if ($orderBy === strval('asc')) {
                for ($i = 0; $i < $quarterGap; $i++) {
                    $year       = date('Y', strtotime("+" . 3 * $i . " month", $startTimeStamp));
                    $month      = date('m', strtotime("+" . 3 * $i . " month", $startTimeStamp));
                    $quarters[] = $year . '-' . floor(($month + 2) / 3);
                }
                // 处理结束节点
                $endQuarter = date('Y', $endTimeStamp) . '-' . floor((date('m', $endTimeStamp) + 2) / 3);
                if (sizeof($quarters) > 0 && $endQuarter != $quarters[$quarterGap - 1]) {
                    $quarters[] = $endQuarter;
                }
            } else {
                for ($i = 0; $i < $quarterGap; $i++) {
                    $year       = date('Y', strtotime("-" . 3 * $i . " month", $endTimeStamp));
                    $month      = date('m', strtotime("-" . 3 * $i . " month", $endTimeStamp));
                    $quarters[] = $year . '-' . floor(($month + 2) / 3);
                }
                // 处理结束节点
                $startQuarter = date('Y', $startTimeStamp) . '-' . floor((date('m', $startTimeStamp) + 2) / 3);
                if (sizeof($quarters) > 0 && $startQuarter != $quarters[$quarterGap - 1]) {
                    $quarters[] = $startQuarter;
                }
            }
        } else {
            // 未设定日期范围，获取最近 4 个季度的日期
            $maxQuarters = 4;
            if ($orderBy === strval('asc')) {
                // 正序返回，为了包含当前日期的季度，需要 $i = 1 开始
                for ($i = 1; $i <= $maxQuarters; $i++) {
                    $year       = date("Y", strtotime("-" . ($maxQuarters - $i) * 3 . "month"));
                    $month      = date("m", strtotime("-" . ($maxQuarters - $i) * 3 . "month"));
                    $quarters[] = $year . '-' . floor(($month + 2) / 3);
                }
            } else {
                for ($i = 0; $i < $maxQuarters; $i++) {
                    $year       = date("Y", strtotime("-" . $i * 3 . "month"));
                    $month      = date("m", strtotime("-" . $i * 3 . "month"));
                    $quarters[] = $year . '-' . floor(($month + 2) / 3);
                }
            }
        }

        return $quarters;
    }

    /**
     * 处理周期为月的数据填充
     *
     * @param array $timeRange
     * @param $orderBy
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public static function getUnabridgedMonths(array $timeRange = null, $orderBy = 'desc')
    {
        $months = [];
        // 获取时间范围
        if (null != $timeRange && sizeof($timeRange) > 0) {
            $startTimeStamp = strtotime($timeRange[0]);
            $endTimeStamp   = strtotime($timeRange[1]);
            // 相差的月数
            $monthGap = (date('Y', $endTimeStamp) - date('Y', $startTimeStamp)) * 12
                + date('m', $endTimeStamp) - date('m', $startTimeStamp) + 1;
            if ($orderBy === strval('asc')) {
                for ($i = 0; $i < $monthGap; $i++) {
                    $months[] = date('Y-m', strtotime("last day of +" . $i . " month", $startTimeStamp));
                }
                // 处理结束节点
                $endMonth = date('Y-m', $endTimeStamp);
                if (sizeof($months) > 0 && $endMonth != $months[$monthGap - 1]) {
                    $months[] = $endMonth;
                }
            } else {
                for ($i = 0; $i < $monthGap; $i++) {
                    $months[] = date('Y-m', strtotime("last day of -" . $i . " month", $endTimeStamp));
                }
                // 处理结束节点
                $startMonth = date('Y-m', $startTimeStamp);
                if (sizeof($months) > 0 && $startMonth != $months[$monthGap - 1]) {
                    $months[] = $startMonth;
                }
            }
        } else {
            // 未设定日期范围，获取最近 6 个月的日期
            $maxMonths = 6;
            if ($orderBy === strval('asc')) {
                for ($i = 1; $i <= $maxMonths; $i++) {
                    $months[] = date("Y-m", strtotime("last day of -" . ($maxMonths - $i) . "month"));
                }
            } else {
                for ($i = 0; $i < $maxMonths; $i++) {
                    $months[] = date("Y-m", strtotime("last day of -" . $i . "month"));
                }
            }
        }
        return $months;
    }


    /**
     * 处理周期为周的数据填充
     *
     * @param array $timeRange
     * @param $orderBy
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public static function getUnabridgedWeeks(array $timeRange = null, $orderBy = 'desc')
    {
        $weeks = [];
        // 获取时间范围
        if (null != $timeRange && sizeof($timeRange) > 0) {
            // 设定了时间范围
            $secondOfOneWeek = 604800;
            $startTimeStamp  = strtotime($timeRange[0]);
            $endTimeStamp    = strtotime($timeRange[1]);
            $weekGap         = floor(($endTimeStamp - $startTimeStamp) / $secondOfOneWeek) + 1;
            if ($orderBy === strval('asc')) {
                for ($i = 0; $i < $weekGap; $i++) {
                    $weeks[] = date('o-W', strtotime("+" . $i * 7 . " day", $startTimeStamp));
                }
                // 处理结束节点
                $endWeek = date('o-W', $endTimeStamp);
                if (sizeof($weeks) > 0 && $endWeek != $weeks[$weekGap - 1]) {
                    $weeks[] = $endWeek;
                }
            } else {
                for ($i = 0; $i < $weekGap; $i++) {
                    $weeks[] = date('o-W', strtotime("-" . $i * 7 . " day", $endTimeStamp));
                }
                // 处理结束节点
                $startWeek = date('o-W', $startTimeStamp);
                if (sizeof($weeks) > 0 && $startWeek != $weeks[$weekGap - 1]) {
                    $weeks[] = $startWeek;
                }
            }
        } else {
            // 未设定日期范围，获取最近 10 周的日期
            $maxWeeks = 10;
            if ($orderBy === strval('asc')) {
                for ($i = 1; $i <= $maxWeeks; $i++) {
                    $weeks[] = date("o-W", strtotime("-" . ($maxWeeks - $i) * 7 . " day"));
                }
            } else {
                for ($i = 0; $i < $maxWeeks; $i++) {
                    $weeks[] = date("o-W", strtotime("-" . $i * 7 . " day"));
                }
            }
        }

        return $weeks;
    }

    /**
     * 处理周期为天的数据填充
     *
     * @param array $timeRange
     * @param $orderBy
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public static function getUnabridgedDays(array $timeRange = null, $orderBy = 'desc')
    {
        $days = [];
        // 获取时间范围
        if (null != $timeRange && sizeof($timeRange) > 0) {
            // 设定了时间范围
            $secondOfOneDay = 86400;
            $startTimeStamp = strtotime($timeRange[0]);
            $endTimeStamp   = strtotime($timeRange[1]);
            $dayGap         = floor(($endTimeStamp - $startTimeStamp) / $secondOfOneDay) + 1;
            if ($orderBy === strval('asc')) {
                for ($i = 0; $i < $dayGap; $i++) {
                    $days[] = date('Y-m-d', strtotime("+" . $i . " day", $startTimeStamp));
                }
            } else {
                for ($i = 0; $i < $dayGap; $i++) {
                    $days[] = date('Y-m-d', strtotime("-" . $i . " day", $endTimeStamp));
                }
            }
        } else {
            // 从今日回数 60 天
            $maxDays = 60;
            if ($orderBy === strval('asc')) {
                for ($i = 1; $i <= $maxDays; $i++) {
                    $days[] = date("Y-m-d", strtotime("-" . ($maxDays - $i) . "day"));
                }
            } else {
                for ($i = 0; $i < $maxDays; $i++) {
                    $days[] = date("Y-m-d", strtotime("-$i day"));
                }
            }
        }

        return $days;
    }
}
