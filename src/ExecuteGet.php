<?php

namespace Shanjing\LaravelStatistics;

use Exception;
use Illuminate\Support\Facades\DB;
use Shanjing\LaravelStatistics\Helper\DateUtils;
use Shanjing\LaravelStatistics\Models\StatisticsModel;

class ExecuteGet
{
    protected $key;
    protected $items;
    protected $period;
    protected $orderBy;
    protected $occurredBetween;

    public function __construct(string $key, array $items)
    {
        $this->key = $key;
        $this->items = $items;
        $this->period = 'day';
        $this->orderBy = 'desc';
    }

    /**
     * 格式化周期
     *
     * @param string $period - year | quarter | month | week | day
     * @return $this
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function period(string $period)
    {
        switch ($period) {
            case strval('year'):
            case strval('quarter'):
            case strval('month'):
            case strval('week'):
            case strval('day'):
                $this->period = $period;
                break;
            default:
                throw new Exception("period param error!");
        }
        return $this;
    }

    /**
     * 排序方式
     *
     * @param $orderBy - asc | desc
     * @return $this
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function orderBy(string $orderBy)
    {
        if ($orderBy === strval('asc')) {
            $this->orderBy = $orderBy;
        } else {
            $this->orderBy = 'desc';
        }
        return $this;
    }

    /**
     * 时间条件
     *
     * @param string $occurredAt
     * @return $this
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function occurredAt(string $occurredAt)
    {
        if (!empty($occurredAt)) {
            // 是否是合法的时间
            $occurredAtTimeStamp = strtotime($occurredAt);
            if (!ctype_digit($occurredAtTimeStamp)) {
                throw new Exception("occurredAt 必须是合法的时间, 例如 '20210901'");
            }

            $this->occurredBetween = [$occurredAt, $occurredAt];
        }

        return $this;
    }

    /**
     * @param array $occurredBetween - 例如： ['20210901' '20210925']
     * @return $this
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function occurredBetween(array $occurredBetween)
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
            $this->occurredBetween = [
                $occurredBetween[1],
                $occurredBetween[0]
            ];
        } else {
            $this->occurredBetween = $occurredBetween;
        }

        return $this;
    }

    /**
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function exec()
    {
        $data = StatisticsModel::selectRaw($this->getRawSelect($this->period, $this->items))
            ->whereRaw($this->getRawWhere($this->occurredBetween, $this->key))
            ->groupBy($this->period)
            ->orderBy($this->period, $this->orderBy)
            ->get();

        return $this->fillMissedDateWithZeroValue(
            $data,
            $this->period,
            $this->occurredBetween,
            $this->orderBy,
            $this->items
        );
    }

    /**
     * 选择语句(选择字段)
     *
     * @param $period
     * @param $items
     * @return string
     *
     * @author lou <lou@shanjing-inc.com>
     */
    private function getRawSelect($period, $items)
    {
        return $this->getFormattedSelectedPeriod($period)
            . $this->getFormattedSelectedItems($items);
    }

    /**
     * Where 条件语句
     * @param $occurredBetween
     * @param $key
     * @return string
     *
     * @author lou <lou@shanjing-inc.com>
     */
    private function getRawWhere($occurredBetween, $key)
    {
        if (null == $occurredBetween || sizeof($occurredBetween) < intval(2)) {
            return "`key`='$key'";
        }

        return "(`occurred_at` >= '$occurredBetween[0]' AND `occurred_at` <= '$occurredBetween[1]')"
            . " AND `key`='$key'";
    }

    /**
     * 格式化周期字段
     *
     * year | quarter | month | week | day | default is day
     *
     * @param $period
     * @return string
     *
     * @author lou <lou@shanjing-inc.com>
     */
    private function getFormattedSelectedPeriod($period)
    {
        switch ($period) {
            case strval('year'):
                return strval('DATE_FORMAT( occurred_at, "%Y" )  AS year');
            case strval('quarter'):
                return strval('concat(DATE_FORMAT(occurred_at, "%Y"), 
                floor((DATE_FORMAT(occurred_at, "-%m")+2)/3)) AS quarter');
            case strval('month'):
                return strval('DATE_FORMAT( occurred_at, "%Y-%m" ) AS month');
            case strval('week'):
                return strval('DATE_FORMAT( occurred_at, "%Y-%u" ) AS week');
            default:
                return strval('DATE_FORMAT( occurred_at, "%Y-%m-%d" ) AS day');
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
    private function getFormattedSelectedItems($items)
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

    /**
     * 如果日期是空白的，则补充 0 为数据
     *
     * @param $data - 待处理的数据
     * @param $period - 周期 day | week | month | quarter | year
     * @param array $occurredBetween - 时间范围 如 【20210901， 20210930】
     * @param $orderBy - 排序方式 desc | asc
     * @param $items - 请求数据的 key，如 gmv | order_num, 用来说明哪些 item 需要补充 0
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     */
    private function fillMissedDateWithZeroValue($data, $period, array $occurredBetween, $orderBy, $items)
    {
        switch ($period) {
            case strval('year'):
                $unabridgedDates = DateUtils::getUnabridgedYears($occurredBetween, $orderBy);
                break;
            case strval('quarter'):
                $unabridgedDates = DateUtils::getUnabridgedQuarter($occurredBetween, $orderBy);
                break;
            case strval('month'):
                $unabridgedDates = DateUtils::getUnabridgedMonths($occurredBetween, $orderBy);
                break;
            case strval('week'):
                $unabridgedDates = DateUtils::getUnabridgedWeeks($occurredBetween, $orderBy);
                break;
            default:
                $unabridgedDates = DateUtils::getUnabridgedDays($occurredBetween, $orderBy);
                break;
        }

        // 缺失日期补充 0 数据
        $dataFilledMissedData = [];
        // 填充数据
        $fillValue = [];
        foreach ($items as $item) {
            $fillValue[$item] = 0;
        }
        //正在使用的 data 数据索引， 用来获取具体的日期对比参数， 来确定当天是否有数据
        $dataCursor = 0;
        // 遍历日期，补足不存在的日期
        foreach ($unabridgedDates as $unabridgedDate) {
            if ($dataCursor < sizeof($data) && $unabridgedDate === $data[$dataCursor]->$period) {
                $dataFilledMissedData[] = $data[$dataCursor];
                $dataCursor++;
            } else {
                $dataFilledMissedData[] = array_merge([
                    "$period" => $unabridgedDate,
                ], $fillValue);
            }
        }

        return $dataFilledMissedData;
    }
}
