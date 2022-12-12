<?php

namespace Shanjing\LaravelStatistics;

use Exception;
use Shanjing\LaravelStatistics\Helper\ArrayHelper;
use Shanjing\LaravelStatistics\Models\StatisticsModel;

class ExecuteUpdate
{
    protected $key;
    protected $data;
    protected $occurredAt;

    public function __construct(string $key, array $data)
    {
        $this->key  = $key;
        $this->data = $data;
    }

    public function occurredAt(string $occurredAt)
    {
        if (!empty($occurredAt)) {
            // 是否是合法的时间
            $occurredAtTimeStamp = strtotime($occurredAt);
            if (!ctype_digit($occurredAtTimeStamp)) {
                throw new Exception("occurredAt 必须是合法的时间, 例如 '20210901'");
            }
            $this->occurredAt = $occurredAt;
        }

        return $this;
    }

    public function exec()
    {
        $model = StatisticsModel::whereRaw($this->getWhereRaw())
            ->first();

        if (null != $model) {
            return StatisticsModel::where('id', $model->id)
                ->update(array_merge(
                    $this->getFormattedData($this->data),
                    ['updated_at' => now()]
                ));
        } else {
            return StatisticsModel::insert([
                   'data'         => json_encode($this->data),
                    'key'         => $this->key,
                    'occurred_at' => $this->occurredAt,
                    'created_at'  => now(),
                    'updated_at'  => now()
                ]);
        }
    }

    /**
     * 格式化条件语句
     *
     * occurredAt： 时间
     * key: 主维度参数
     *
     * @return String
     *
     * @author lou <lou@shanjing-inc.com>
     */
    private function getWhereRaw()
    {
        return $this->getFormattedWhereOccurredAt($this->occurredAt) . " AND `key`='$this->key'";
    }

    /**
     * 获取格式化的时间条件
     *
     * @param $occurredAt
     * @return string occurredAt , default is today
     *
     * @author lou <lou@shanjing-inc.com>
     */
    private function getFormattedWhereOccurredAt($occurredAt)
    {
        if (null == $occurredAt) {
            return"`occurred_at` = '" . date('Ymd') . "'";
        }

        return "`occurred_at` = '$occurredAt'";
    }

    /**
     * 将要更新的字段格式化
     *
     * @param $data
     * @return array occurredAt , default is today
     *
     * @author lou <lou@shanjing-inc.com>
     */
    private function getFormattedData($data)
    {
        if ($data == null || sizeof($data) == 0) {
            throw new Exception('There is no data to save!');
        }

        return ArrayHelper::arrow(['data' => $data]);
    }
}
