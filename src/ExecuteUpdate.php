<?php

namespace Shanjing\LaravelStatistics;

use Exception;
use Illuminate\Support\Facades\DB;
use Shanjing\LaravelStatistics\Models\StatisticsModel;

class ExecuteUpdate
{
    protected $key;
    protected $data;
    protected $occurredAt;

    public function __construct(string $key, array $data)
    {
        $this->key = $key;
        $this->data = $data;
    }

    public function occurredAt(string $occurredAt)
    {
        $this->occurredAt = $occurredAt;
        return $this;
    }

    public function exec()
    {
        $model = StatisticsModel::whereRaw($this->getWhereRaw())
            ->first();

        if (null != $model) {
            return StatisticsModel::orderBy('id', 'desc')
                ->where('id', $model->id)
                ->update(array_merge(
                    $this->getFormatedData($this->data),
                    ['updated_at' => now()]
                ));
        } else {
            return StatisticsModel::orderBy('id', 'desc')
                ->insert([
                   'data'  => json_encode($this->data),
                    'key'  => $this->key,
                    'occurred_at' => $this->occurredAt,
                    'created_at' => now(),
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
        return $this->getFormatedWhereOccurredAt($this->occurredAt) . " AND `key`='$this->key'";
    }


    /**
     * 获取格式化的时间条件
     *
     * @param $occurredAt
     * @return string occurredAt , default is today
     *
     * @author lou <lou@shanjing-inc.com>
     */
    private function getFormatedWhereOccurredAt($occurredAt)
    {
        if (null == $occurredAt) {
            return"'" . date('Ymd') . "'";
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
    private function getFormatedData($data)
    {
        if ($data == null || sizeof($data) == 0) {
            throw new Exception('There is no data to save!');
        }

        return $this->arrow(['data' => $data]);
    }

    /**
     * Flatten a multi-dimensional associative array with dots.
     *
     * @param  iterable  $array
     * @param  string  $prepend
     * @return array
     */
    public static function arrow($array, $prepend = '')
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && ! empty($value)) {
                $results = array_merge($results, static::arrow($value, $prepend . $key . '->'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }
}
