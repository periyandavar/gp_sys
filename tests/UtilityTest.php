<?php

use System\Core\Test\TestCase;
use System\Core\Utility;

class UtilityTest extends TestCase
{
    public function testEndsWith()
    {
        $this->assertTrue(Utility::endsWith('foobar', 'bar'));
        $this->assertFalse(Utility::endsWith('foobar', 'baz'));
        $this->assertTrue(Utility::endsWith('foobar', ''));
    }

    public function testStartsWith()
    {
        $this->assertTrue(Utility::startsWith('foobar', 'foo'));
        $this->assertFalse(Utility::startsWith('foobar', 'bar'));
        $this->assertTrue(Utility::startsWith('foobar', ''));
    }

    public function testSetSessionData()
    {
        Utility::setSessionData('test', 'value');
        $this->assertEquals('value', $_SESSION['test']);
        Utility::setSessionData('test', null);
        $this->assertArrayNotHasKey('test', $_SESSION);
    }

    public function testIsAssociative()
    {
        $this->assertFalse(Utility::isAssociative([1, 2, 3]));
        $this->assertTrue(Utility::isAssociative(['a' => 1, 'b' => 2]));
        $this->assertFalse(Utility::isAssociative([]));
    }

    public function testIsStaticFile()
    {
        $this->assertTrue(Utility::isStaticFile('file.css'));
        $this->assertTrue(Utility::isStaticFile('image.png'));
        $this->assertFalse(Utility::isStaticFile('script.php'));
    }

    public function testArrayToXml()
    {
        $data = ['foo' => 'bar', 'baz' => ['qux' => 'quux']];
        $xml = Utility::arrayToXml($data, '<root/>');
        $this->assertStringContainsString('<foo>bar</foo>', $xml);
        $this->assertStringContainsString('<baz>', $xml);
        $this->assertStringContainsString('<qux>quux</qux>', $xml);
    }

    public function testArrayToCsv()
    {
        $data = [
            ['name' => 'Alice', 'age' => 30],
            ['name' => 'Bob', 'age' => 25]
        ];
        $csv = Utility::arrayToCsv($data);
        $this->assertStringContainsString('name,age', $csv);
        $this->assertStringContainsString('Alice,30', $csv);
        $this->assertStringContainsString('Bob,25', $csv);
    }

    public function testArrayToYaml()
    {
        $data = ['foo' => 'bar', 'baz' => 'qux'];
        $yaml = Utility::arrayToYaml($data);
        $this->assertStringContainsString('foo: bar', $yaml);
        $this->assertStringContainsString('baz: qux', $yaml);
    }

    public function testCoalesceArray()
    {
        $arr = ['a' => 1];
        $this->assertEquals(1, Utility::coalesceArray($arr, 'a', 2));
        $this->assertEquals(2, Utility::coalesceArray($arr, 'b', 2));
    }

    public function testCurrentUrl()
    {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REQUEST_URI'] = '/test/path';
        $this->assertEquals('https://localhost/test/path', Utility::currentUrl());

        $_SERVER['HTTPS'] = 'off';
        $this->assertEquals('http://localhost/test/path', Utility::currentUrl());
    }
}
