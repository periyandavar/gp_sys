<?php

use Loader\Container;
use System\Core\Base\Context\WebContext;
use System\Core\Test\TestCase;

class WebContextTest extends TestCase
{
    public function setUp(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/test/uri';
        $_GET = ['foo' => 'bar'];
        $_POST = ['baz' => 'qux'];
        $_SESSION = ['user' => 'alice'];
        $_COOKIE = ['token' => '123'];
    }

    public function testWebContextGetters()
    {
        $webContext = WebContext::getInstance('dev', [
            'uri' => '/test/uri',
        ]);
        $webContext->setModule('user');
        $webContext->setRouter('main');
        $this->assertEquals('main', $webContext->getRouter());
        $this->assertEquals('user', $webContext->getModule());
        $this->assertEquals('POST', $webContext->getRequestMethod());
        $this->assertEquals('/test/uri', $webContext->getRequestUri());
        $this->assertEquals(['foo' => 'bar'], $webContext->getQueryParams());
        $this->assertEquals(['baz' => 'qux'], $webContext->getPostParams());
        $this->assertEquals(['user' => 'alice'], $webContext->getSession());
        $this->assertEquals(['token' => '123'], $webContext->getCookies());
    }

    public function testWebContextToString()
    {
        $webContext = WebContext::getInstance('dev', [
            'uri' => '/test/uri',
        ]);
        $webContext->setModule('user');
        $webContext->setRouter('main');
        Container::set('route', new \Router\Route('main', 'user', 'index'));
        $webContext->setLogConfig(['router', 'module', 'request_method']);
        $str = (string) $webContext;
        $this->assertStringContainsString('main', $str);
        $this->assertStringContainsString('user', $str);
    }
}
