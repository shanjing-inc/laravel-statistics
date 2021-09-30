<?php

namespace Shanjing\LaravelStatistics;

use Shanjing\LaravelStatistics\Helper\QueryParamToSqlHelper;
use Shanjing\LaravelStatistics\Helper\QueryParamCorrectHelper;
use Shanjing\LaravelStatistics\Helper\QueryResultProcessHelper;
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
        $this->period = QueryParamCorrectHelper::correctPeriod($period);
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
        $this->orderBy = QueryParamCorrectHelper::correctOrderBy($orderBy);
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
        $this->occurredBetween = QueryParamCorrectHelper::correctOccurredAt($occurredAt);
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
        $this->occurredBetween = QueryParamCorrectHelper::correctOccurredBetween($occurredBetween);
        return $this;
    }

    /**
     * @return array
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function exec()
    {
        $data = StatisticsModel::selectRaw(QueryParamToSqlHelper::transformSelect($this->period, $this->items))
            ->whereRaw(QueryParamToSqlHelper::transformWhere($this->occurredBetween, $this->key))
            ->groupBy($this->period)
            ->orderBy($this->period, $this->orderBy)
            ->get();

        return QueryResultProcessHelper::fillMissedDateAndChangeNullValueWithZero(
            $data,
            $this->period,
            $this->occurredBetween,
            $this->orderBy
        );
    }
}
