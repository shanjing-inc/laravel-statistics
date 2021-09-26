<?php

namespace Shanjing\LaravelStatistics;

use Illuminate\Support\Facades\DB;
use Shanjing\LaravelStatistics\Helper\DateUtils;

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
    }

    public function period($period)
    {
        $this->period = $period;
        return $this;
    }

    public function orderBy($orderBy)
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    public function occurredAt(string $occurredAt)
    {
        $this->occurredBetween = [$occurredAt, $occurredAt];
        return $this;
    }

    public function occurredBetween(array $occurredBetween)
    {
        $this->occurredBetween = $occurredBetween;
        return $this;
    }

    public function exec()
    {
        $data = DB::table(config('statistics.database.statistics_table'))
            ->selectRaw($this->getRawSelect())
            ->whereRaw($this->getRawWhere())
            ->groupBy($this->getPeriod())
            ->orderBy($this->getPeriod(), $this->getOrderBy())
            ->get();


        return $this->fillMissedDateWithZeroValue($data);
    }

    /**
     * 格式化选择语句
     *
     * @return string
     *
     * @author lou <lou@shanjing-inc.com>
     */
    private function getRawSelect()
    {
        return $this->getFormatedSelectedPeriod($this->period)
            . $this->getFormatedSelectedItems($this->items);
    }

    /**
     * 格式化条件语句
     *
     * occurredBetween： 时间范围
     * key: 主维度参数
     *
     * @return string
     *
     * @author lou <lou@shanjing-inc.com>
     */
    private function getRawWhere()
    {
        $occurredBetween = $this->getFormatedWhereOccurredBetween($this->occurredBetween);
        if (!empty($occurredBetween)) {
            return $this->getFormatedWhereOccurredBetween($this->occurredBetween) . " AND `key`='$this->key'";
        } else {
            return "`key`='$this->key'";
        }
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
    private function getFormatedSelectedPeriod($period)
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
    private function getFormatedSelectedItems($items)
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
     * 获取格式化的时间条件
     *
     * @param $occurredBetween
     * @return string occurredBetween | '', default is '', means no limit
     *
     * @author lou <lou@shanjing-inc.com>
     */
    private function getFormatedWhereOccurredBetween($occurredBetween)
    {
        if (null == $occurredBetween || sizeof($occurredBetween) == 0) {
            return '';
        }

        return "(`occurred_at` >= '$occurredBetween[0]' AND `occurred_at` <= '$occurredBetween[1]')";
    }

    /**
     * 返回周期
     *
     *
     * @return string  year | quarter | month | week | day, default is day
     *
     * @author lou <lou@shanjing-inc.com>
     */
    private function getPeriod()
    {
        if (empty($this->period)) {
            return strval('day');
        }
        return $this->period;
    }

    /**
     * 排序方式
     *
     * @return string asc | desc, default is desc
     *
     * @author lou <lou@shanjing-inc.com>
     */
    private function getOrderBy()
    {
        if ($this->orderBy === strval('asc')) {
            return strval('asc');
        } else {
            return strval('desc');
        }
    }

    /**
     * 如果日期是空白的，则补充 0 为数据
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function fillMissedDateWithZeroValue($data)
    {
        $period = $this->period;
        switch ($period) {
            case strval('year'):
                $unabridgedDates = DateUtils::getUnabridgedYears($this->occurredBetween, $this->orderBy);
                break;
            case strval('quarter'):
                $unabridgedDates = DateUtils::getUnabridgedQuarter($this->occurredBetween, $this->orderBy);
                break;
            case strval('month'):
                $unabridgedDates = DateUtils::getUnabridgedMonths($this->occurredBetween, $this->orderBy);
                break;
            case strval('week'):
                $unabridgedDates = DateUtils::getUnabridgedWeeks($this->occurredBetween, $this->orderBy);
                break;
            default:
                $unabridgedDates = DateUtils::getUnabridgedDays($this->occurredBetween, $this->orderBy);
                break;
        }

        // 缺失日期补充 0 数据
        $dataFilledMissedData = [];
        // 填充数据
        $fillValue = [];
        foreach ($this->items as $item) {
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
