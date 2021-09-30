<?php

namespace Shanjing\LaravelStatistics\Tests;

use Shanjing\LaravelStatistics\Helper\ArrayHelper;
use PHPUnit\Framework\TestCase;

class ArrayHelperTest extends TestCase
{
    public function testArrow()
    {
        //
        $this->assertSame(
            ['data->item1' => 'value1', 'data->item2' => 'value2'],
            ArrayHelper::arrow(['data' => ['item1' => 'value1', 'item2' => 'value2']])
        );
    }
}
