<?php

namespace Shanjing\LaravelStatistics\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Shanjing\LaravelStatistics\ExecuteGet;
use Shanjing\LaravelStatistics\ExecuteUpdate;

class ExecuteUpdateTest extends TestCase
{

    public function testOccurredAt()
    {
        // 反射
        $reflectedClass = new ReflectionClass('Shanjing\LaravelStatistics\ExecuteUpdate');
        $reflectedProperty = $reflectedClass->getProperty('occurredAt');
        $reflectedProperty->setAccessible(true);

        // 正常日期
        $executeUpdate = new ExecuteUpdate('test', ['item' => 'value']);
        $executeUpdate->occurredAt('20210905');
        $this->assertSame('20210905', $reflectedProperty->getValue($executeUpdate));
    }

    public function testGetFormattedWhereOccurredAt()
    {
        // 反射
        $reflectedMethod = new ReflectionMethod(
            'Shanjing\LaravelStatistics\ExecuteUpdate',
            'getFormattedWhereOccurredAt'
        );
        $reflectedMethod->setAccessible(true);

        //
        $executeUpdate = new ExecuteUpdate('test', ['item' => 'value']);
        $this->assertSame("`occurred_at` = '20210905'", $reflectedMethod->invoke($executeUpdate, '20210905'));
    }

    public function testGetFormattedData()
    {
        // 反射
        $reflectedMethod = new ReflectionMethod(
            'Shanjing\LaravelStatistics\ExecuteUpdate',
            'getFormattedData'
        );
        $reflectedMethod->setAccessible(true);

        //
        $expected = [
            "data->key1" => "value1",
            "data->key2" => "value2"
        ];
        $executeUpdate = new ExecuteUpdate('test', ['item' => 'value']);
        $this->assertSame(
            $expected,
            $reflectedMethod->invoke($executeUpdate, ['key1' => 'value1', 'key2' => 'value2'])
        );
    }
}
