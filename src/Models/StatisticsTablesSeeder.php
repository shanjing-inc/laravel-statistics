<?php

namespace Shanjing\LaravelStatistics\Models;

use Illuminate\Database\Seeder;

class StatisticsTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // create a StatisticsModel.
        StatisticsModel::truncate();
        StatisticsModel::create([
            'key'  => 'taobao',
            'data' => json_encode([
                'gmv'       => '1254',
                'order_num' => '1420',
                'money'     => '4842.21'
            ]),
            'occurred_at' => '20210921',
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);
    }
}
