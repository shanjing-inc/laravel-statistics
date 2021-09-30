<?php

namespace Shanjing\LaravelStatistics\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Shanjing\LaravelStatistics\ExecuteGet;

class ExecuteGetTest extends TestCase
{
    public function testPeriod()
    {
        // 反射（获取私有属性）
        $reflectedClass = new ReflectionClass('Shanjing\LaravelStatistics\ExecuteGet');
        $reflectedProperty = $reflectedClass->getProperty('period');
        $reflectedProperty->setAccessible(true);

        // 未设置参数
        $executeGet = new ExecuteGet('test', ['item']);
        $this->assertSame('day', $reflectedProperty->getValue($executeGet));

        // 设置参数
        $executeGet = new ExecuteGet('test', ['item']);
        $executeGet->period('year');
        $this->assertSame('year', $reflectedProperty->getValue($executeGet));
    }

    public function testOrderBy()
    {
        // 反射（获取私有属性）
        $reflectedClass = new ReflectionClass('Shanjing\LaravelStatistics\ExecuteGet');
        $reflectedProperty = $reflectedClass->getProperty('orderBy');
        $reflectedProperty->setAccessible(true);

        // 未设置参数
        $executeGet = new ExecuteGet('test', ['item']);
        $this->assertSame('desc', $reflectedProperty->getValue($executeGet));

        // 设置参数
        $executeGet = new ExecuteGet('test', ['item']);
        $executeGet->orderBy('asc');
        $this->assertSame('asc', $reflectedProperty->getValue($executeGet));
    }

    public function testOccurredAt()
    {
        // 反射（获取私有属性）
        $reflectedClass = new ReflectionClass('Shanjing\LaravelStatistics\ExecuteGet');
        $reflectedProperty = $reflectedClass->getProperty('occurredBetween');
        $reflectedProperty->setAccessible(true);

        // 未设置参数
        $executeGet = new ExecuteGet('test', ['item']);
        $this->assertSame(null, $reflectedProperty->getValue($executeGet));

        // 设置参数
        $executeGet = new ExecuteGet('test', ['item']);
        $executeGet->occurredAt('20210905');
        $this->assertSame(['20210905', '20210905'], $reflectedProperty->getValue($executeGet));
    }

    public function testOccurredBetween()
    {
        // 反射（获取私有属性）
        $reflectedClass = new ReflectionClass('Shanjing\LaravelStatistics\ExecuteGet');
        $reflectedProperty = $reflectedClass->getProperty('occurredBetween');
        $reflectedProperty->setAccessible(true);

        // 未设置参数
        $executeGet = new ExecuteGet('test', ['item']);
        $this->assertSame(null, $reflectedProperty->getValue($executeGet));

        // 设置参数
        $executeGet = new ExecuteGet('test', ['item']);
        $executeGet->occurredBetween(['20210905', '20211015']);
        $this->assertSame(['20210905', '20211015'], $reflectedProperty->getValue($executeGet));
    }
}
