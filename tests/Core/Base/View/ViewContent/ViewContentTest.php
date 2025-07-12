<?php

use PHPUnit\Framework\TestCase;
use System\Core\Base\View\ViewContent;

class ViewContentTest extends TestCase
{
    public function testAddStyle()
    {
        $vc = new ViewContent();
        $vc->addStyle('style.css');
        $styles = $this->getProperty($vc, 'style');
        $this->assertNotEmpty($styles);
        $this->assertEquals('style.css', $styles[0]->getContent());
    }

    public function testAddScript()
    {
        $vc = new ViewContent();
        $vc->addScript('script.js');
        $scripts = $this->getProperty($vc, 'script');
        $this->assertNotEmpty($scripts);
        $this->assertEquals('script.js', $scripts[0]->getContent());
    }

    public function testAddCss()
    {
        $vc = new ViewContent();
        $vc->addCss('main.css');
        $css = $this->getProperty($vc, 'css');
        $this->assertNotEmpty($css);
        $this->assertEquals('main.css', $css[0]->getContent());
    }

    public function testAddJs()
    {
        $vc = new ViewContent();
        $vc->addJs('main.js');
        $js = $this->getProperty($vc, 'js');
        $this->assertNotEmpty($js);
        $this->assertEquals('main.js', $js[0]->getContent());
    }

    public function testAddLayout()
    {
        $vc = new ViewContent();
        $vc->addLayout('layout.php');
        $layouts = $this->getProperty($vc, 'layout');
        $this->assertNotEmpty($layouts);
        $this->assertEquals('layout.php', $layouts[0]->getContent());
    }

    public function testAddTemplate()
    {
        $vc = new ViewContent();
        $vc->addTemplate('template.php', ['foo' => 'bar']);
        $templates = $this->getProperty($vc, 'template');
        $this->assertNotEmpty($templates);
        $this->assertEquals('template.php', $templates[0]->getContent());
        $this->assertEquals(['foo' => 'bar'], $templates[0]->getData());
    }

    /**
     * Helper to access protected/private properties.
     */
    protected function getProperty($object, $property)
    {
        $ref = new ReflectionClass($object);
        $prop = $ref->getProperty($property);
        $prop->setAccessible(true);
        return $prop->getValue($object);
    }
}