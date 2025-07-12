<?php

use Loader\Config\ConfigLoader;
use Loader\Container;
use Router\Response\Response;
use System\Core\Base\View\Content\DynamicContent;
use System\Core\Base\View\Content\StaticContent;
use System\Core\Base\View\View;
use System\Core\Base\View\ViewContent;
use System\Core\Test\TestCase;

class ViewTest extends TestCase
{
    protected $view;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock ConfigLoader
        $configMock = Mockery::mock(ConfigLoader::class);
        $configMock->shouldReceive('get')->with('config', '')->andReturn([]);
        $configMock->shouldReceive('get')->with('template', '')->andReturn(__DIR__);
        $configMock->shouldReceive('get')->with('view', '')->andReturn(__DIR__);
        $configMock->shouldReceive('get')->with('static', '')->andReturn(__DIR__);
        $configMock->shouldReceive('get')->with('layout', '')->andReturn(__DIR__);

        // Overload ConfigLoader::getConfig to return our mock
        // shouldReceive('getConfig')->andReturn($configMock);

        // Mock Container for Response
        $responseMock = Mockery::mock(Response::class);
        $responseMock->shouldReceive('setBody')->andReturnSelf();
        $responseMock->shouldReceive('setType')->andReturnSelf();
        Container::set(Response::class, $responseMock);
        $this->context->shouldReceive('getConfig')
            ->andReturn($configMock);
        Container::set('context', $this->context);

        $this->view = new View();
    }

    public function testAddAndGetContent()
    {
        $vc = new ViewContent();
        $this->view->add($vc);
        $content = $this->view->getContent();
        $this->assertContains($vc, $content);
    }

    public function testAddContentWithArray()
    {
        $data = ['style' => 'style.css'];
        $this->view->addContent($data);
        $content = $this->view->getContent();
        $this->assertNotEmpty($content);
        $this->assertInstanceOf(ViewContent::class, $content[0]);
    }

    public function testAddContents()
    {
        $vc1 = new ViewContent();
        $vc2 = new ViewContent();
        $this->view->addContents([$vc1, $vc2]);
        $content = $this->view->getContent();
        $this->assertCount(2, $content);
    }

    public function testAddStyleScriptCssJs()
    {
        $this->view->addStyle('style.css');
        $this->view->addScript('script.js');
        $this->view->addCss('body { color: red; }');
        $this->view->addJs('console.log("hi");');
        $content = $this->view->getContent();
        $this->assertNotEmpty($content);
    }

    public function testAddTemplateLayoutView()
    {
        $this->view->addTemplate('tpl.php', ['foo' => 'bar']);
        $this->view->addLayout('layout.html');
        $this->view->addView('view.php', ['bar' => 'baz']);
        $content = $this->view->getContent();
        $this->assertNotEmpty($content);
    }

    public function testClear()
    {
        $this->view->addStyle('style.css');
        $this->view->clear();
        $this->assertEmpty($this->view->getContent());
    }

    public function testFrameViewContents()
    {
        $vc = new ViewContent();
        $result = $this->view->frameViewContents([$vc]);
        $this->assertIsArray($result);
        $this->assertInstanceOf(ViewContent::class, $result[0]);
    }

    public function testCreateViewContent()
    {
        $data = [
            'style' => ['style.css'],
            'script' => ['script.js'],
            'template' => ['file' => 'tpl.php', 'data' => ['foo' => 'bar']],
            'view' => ['file' => 'view.php', 'data' => ['bar' => 'baz']],
            'css' => 'body { color: red; }',
            'js' => 'console.log("hi");',
            'layout' => 'layout.html'
        ];
        $vc = $this->view->createViewContent($data);
        $this->assertInstanceOf(ViewContent::class, $vc);
    }

    public function testRenderSheet()
    {
        $result = $this->view->renderSheet('style.css');
        $this->assertStringContainsString('stylesheet', $result);
    }

    public function testRenderScript()
    {
        $result = $this->view->renderScript('script.js');
        $this->assertStringContainsString('script', $result);
    }

    public function testToString()
    {
        $this->view->addStyle('style.css');
        $output = (string) $this->view;
        $this->assertIsString($output);
    }

    public function testGet()
    {
        $response = $this->view->get();
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testGetAll()
    {
        $vc = new ViewContent();
        $vc->addStyle('style.css');
        $vc->addScript('script.js');
        $vc->addCss('body { color: red; }');
        $vc->addJs('console.log("hi");');
        $vc->addLayout('layout.php');
        $vc->addTemplate('template.php', ['foo' => 'bar']);
        $vc->addView('view.php', ['bar' => 'baz']);

        $all = $vc->getAll();

        $this->assertArrayHasKey('style', $all);
        $this->assertArrayHasKey('script', $all);
        $this->assertArrayHasKey('css', $all);
        $this->assertArrayHasKey('js', $all);
        $this->assertArrayHasKey('template', $all);
        $this->assertArrayHasKey('layout', $all);
        $this->assertArrayHasKey('view', $all);

        $this->assertInstanceOf(StaticContent::class, $all['style'][0]);
        $this->assertInstanceOf(StaticContent::class, $all['script'][0]);
        $this->assertInstanceOf(StaticContent::class, $all['css'][0]);
        $this->assertInstanceOf(StaticContent::class, $all['js'][0]);
        $this->assertInstanceOf(StaticContent::class, $all['layout'][0]);
        $this->assertInstanceOf(DynamicContent::class, $all['template'][0]);
        $this->assertInstanceOf(DynamicContent::class, $all['view'][0]);
    }

    public function testGetContent()
    {
        $vc = new ViewContent();
        $vc->addStyle('style.css');
        $vc->addTemplate('template.php', ['foo' => 'bar']);

        $style = $vc->get('style');
        $template = $vc->get('template');
        $notExist = $vc->get('not_exist');

        $this->assertIsArray($style);
        $this->assertInstanceOf(StaticContent::class, $style[0]);
        $this->assertIsArray($template);
        $this->assertInstanceOf(DynamicContent::class, $template[0]);
        $this->assertNull($notExist);
    }

    public function testClearContent()
    {
        $vc = new ViewContent();
        $vc->addStyle('style.css');
        $vc->addScript('script.js');
        $vc->addCss('body { color: red; }');
        $vc->addJs('console.log("hi");');
        $vc->addLayout('layout.php');
        $vc->addTemplate('template.php', ['foo' => 'bar']);
        $vc->addView('view.php', ['bar' => 'baz']);

        $vc->clear();

        $all = $vc->getAll();
        foreach ($all as $key => $value) {
            $this->assertEmpty($value, "Failed asserting that $key is empty after clear().");
        }
    }
}
