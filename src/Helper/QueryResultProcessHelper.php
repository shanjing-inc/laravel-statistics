<?php

namespace Shanjing\LaravelStatistics\Helper;

/**
 * 处理查询的结果
 *
 * 1. 补足缺失的日期
 * 2. 查询的 null 结果，修改为 0， 方便展示统计数据
 *
 * Class QueryResultProcessHelper
 * @package Shanjing\LaravelStatistics\Helper
 *
 * @author lou <lou@shanjing-inc.com>
 */
class QueryResultProcessHelper
{
    /**
     * 如果日期是空白的，则补充 0 为数据
     *
     * @param $data - 待处理的数据
     * @param $period - 周期 day | week | month | quarter | year
     * @param $occurredBetween - 时间范围 如 【20210901， 20210930】
     * @param $orderBy - 排序方式 desc | asc
     * @param $items - 请求数据的 key，如 gmv | order_num, 用来说明哪些 item 需要补充 0
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @author lou <lou@shanjing-inc.com>
     */
    public static function fillMissedDateAndChangeNullValueWithZero($data, $period, $occurredBetween, $orderBy)
    {
        switch ($period) {
            case strval('year'):
                $unabridgedDates = DateHelper::getUnabridgedYears($occurredBetween, $orderBy);
                break;
            case strval('quarter'):
                $unabridgedDates = DateHelper::getUnabridgedQuarter($occurredBetween, $orderBy);
                break;
            case strval('month'):
                $unabridgedDates = DateHelper::getUnabridgedMonths($occurredBetween, $orderBy);
                break;
            case strval('week'):
                $unabridgedDates = DateHelper::getUnabridgedWeeks($occurredBetween, $orderBy);
                break;
            default:
                $unabridgedDates = DateHelper::getUnabridgedDays($occurredBetween, $orderBy);
                break;
        }

        // 缺失日期补充 0 数据
        $dataFilledMissedData = [];
        if (null != $data && sizeof($data) > 0) {
            // 填充数据
            $fillValue = [];
            foreach ($data[0]->getAttributes() as $key => $value) {
                $fillValue[$key] = 0;
            }
            //正在使用的 data 数据索引， 用来获取具体的日期对比参数， 来确定当天是否有数据
            $dataCursor = 0;
            // 遍历日期，补足不存在的日期
            foreach ($unabridgedDates as $unabridgedDate) {
                if ($dataCursor < sizeof($data) && $unabridgedDate === $data[$dataCursor]->$period) {
                    $dataFilledMissedData[] = $data[$dataCursor]->getAttributes();
                    $dataCursor++;
                } else {
                    $dataFilledMissedData[] = array_merge($fillValue, [
                        "$period" => $unabridgedDate,
                    ]);
                }
            }
        } else {
            // 只有日期的数据
            foreach ($unabridgedDates as $unabridgedDate) {
                $dataFilledMissedData[] = ["$period" => $unabridgedDate];
            }
        }



        return $dataFilledMissedData;
    }
}
