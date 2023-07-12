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

        $originData = [
            'foo' => [
                'bar' => 1,
                'baz' => 2,
            ],
            'qux' => 3,
        ];

        $data = [
            'foo' => [
                'bar' => 4,
                'quux' => 5,
            ],
            'corge' => 6,
        ];

        $expectedResult = [
            'foo->bar' => 5,
            'foo->baz' => 2,
            'qux' => 3,
            'foo->quux' => 5,
            'corge' => 6,
        ];
        $executeUpdate = new ExecuteUpdate('test', $data);
        $executeUpdate->setAmount(true);
        $this->assertSame(
            $expectedResult,
            $reflectedMethod->invoke($executeUpdate, $originData, $data)
        );
    }

    public function testGetFormattedDataWithoutAmount()
    {
        // 反射
        $reflectedMethod = new ReflectionMethod(
            'Shanjing\LaravelStatistics\ExecuteUpdate',
            'getFormattedData'
        );
        $reflectedMethod->setAccessible(true);

        $originData = [
            'foo' => [
                'bar' => 1,
                'baz' => 2,
            ],
            'qux' => 3,
        ];

        $data = [
            'foo' => [
                'bar' => 4,
                'quux' => 5,
            ],
            'corge' => 6,
        ];

        $expectedResult = [
            'foo->bar' => 4,
            'foo->quux' => 5,
            'corge' => 6,
        ];
        $executeUpdate = new ExecuteUpdate('test', $data);
        $executeUpdate->setAmount(false);
        $this->assertSame(
            $expectedResult,
            $reflectedMethod->invoke($executeUpdate, $originData, $data)
        );
    }
}
