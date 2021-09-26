<?php

namespace Shanjing\LaravelStatistics;

class Statistics
{
    public function __construct()
    {
    }

    /**
     * 保存统计数据
     *
     * @param string $key
     * @param array $dataToSave
     * @return ExecuteUpdate
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function save(string $key, array $dataToSave)
    {
        return new ExecuteUpdate($key, $dataToSave);
    }

    /**
     * 获取统计数据
     *
     * @param string $key
     * @param array $dataKeys
     * @return ExecuteGet
     *
     * @author lou <lou@shanjing-inc.com>
     */
    public function get(string $key, array $dataKeys)
    {
        return new ExecuteGet($key, $dataKeys);
    }
}
