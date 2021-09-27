<?php

namespace Shanjing\LaravelStatistics\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
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
        // 反射获取隐私参数
        $this->assertSame('20210905', $reflectedProperty->getValue($executeUpdate));
    }
}
