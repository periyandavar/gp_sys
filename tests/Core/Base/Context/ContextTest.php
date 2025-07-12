<?php

use System\Core\Base\Context\Context;
use System\Core\Test\TestCase;

class ContextTest extends TestCase
{
    public function testContextSetGetHasRemove()
    {
        $context = Context::getInstance(['foo' => 'bar']);
        $this->assertTrue($context->has('foo'));
        $this->assertEquals('bar', $context->get('foo'));
        $context->set('baz', 123);
        $this->assertEquals(123, $context->get('baz'));
        $context->remove('foo');
        $this->assertFalse($context->has('foo'));
    }

    public function testContextToStringAndLogConfig()
    {
        $context = Context::getInstance(['a' => 1, 'b' => 2, 'c' => 3]);
        $context->setLogConfig(['a', 'c']);
        $str = (string) $context;
        $this->assertStringContainsString('"a":1', $str);
        $this->assertStringContainsString('"c":3', $str);
        $this->assertStringNotContainsString('"b":2', $str);
    }

    public function testContextDebugInfo()
    {
        $context = Context::getInstance(['x' => 10, 'y' => 20]);
        $context->setLogConfig(['x']);
        $debug = $context->__debugInfo();
        $this->assertArrayHasKey('x', $debug);
        $this->assertArrayNotHasKey('y', $debug);
    }
}
