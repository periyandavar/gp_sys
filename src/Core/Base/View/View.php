<?php

namespace System\Core\Base\View;

use Loader\Config\ConfigLoader;
use Loader\Container;
use Router\Response\Response;
use System\Core\Base\View\Content\DynamicContent;
use System\Core\TemplateEngine;
use System\Core\Utility;

class View
{
    protected ConfigLoader $config;

    /**
     * @var ViewContent[]
     */
    protected array $content = [];

    protected string $output = '';

    public function __construct()
    {
        $this->config = ConfigLoader::getConfig('config');
    }

    public function getHeaderContent(): array
    {
        return [];
    }

    public function getFooterContent(): array
    {
        return [];
    }

    public function BodyContent(): array
    {
        return [];
    }

    public function frameViewContents(array $data): array
    {
        $viewContents = [];
        foreach ($data as $item) {
            if ($item instanceof ViewContent) {
                $viewContents[] = $item;
            } elseif (is_array($item) && Utility::isAssociative($item)) {
                $viewContents[] = $this->createViewContent($item);//exit;
            }
        }

        return $viewContents;
    }

    public function createViewContent(array $data): ViewContent
    {
        $viewContent = new ViewContent();

        foreach ($data as $key => $value) {
            $isDynamic = in_array($key, ['template', 'view']);
            if ($isDynamic) {
                if (is_array($value) && (isset($value['file']) || isset($value[0]))) {
                    $file = $value['file'] ?? $value[0] ?? null;
                    $dataArr = $value['data'] ?? $value[1] ?? [];
                    if ($key === 'template') {
                        $viewContent->addTemplate($file, is_array($dataArr) ? $dataArr : []);
                    } else {
                        $viewContent->addView($file, is_array($dataArr) ? $dataArr : []);
                    }
                } elseif (is_string($value)) {
                    if ($key === 'template') {
                        $viewContent->addTemplate($value);
                    } else {
                        $viewContent->addView($value);
                    }
                }
            } elseif (is_array($value)) {
                // For static content arrays (style, script, css, js, layout)
                foreach ($value as $item) {
                    $method = 'add' . ucfirst($key);
                    if (method_exists($viewContent, $method)) {
                        $viewContent->$method($item);
                    }
                }
            } elseif (is_string($value)) {
                $method = 'add' . ucfirst($key);
                if (method_exists($viewContent, $method)) {
                    $viewContent->$method($value);
                }
            }
        }

        return $viewContent;
    }

    public function add(ViewContent $item): void
    {
        $this->content[] = $item;
    }

    /**
     * Render all items in the queue in the specified order and return as string.
     */
    public function render(): string
    {
        $this->output = '';
        $content = $this->getAllContents();
        // Render based on the order and grouping in $content array
        foreach ($content as $group) {
            foreach (['style', 'css', 'script', 'js', 'layout', 'template', 'view'] as $type) {
                foreach ($group->$type as $item) {
                    $this->output .= $this->renderItem($type, $item);
                }
            }
        }

        return $this->output;
    }

    public function getAllContents(): array
    {
        $content = [];
        $content = array_merge($content, $this->getHeaderContent());
        $content = array_merge($content, $this->BodyContent());
        $content = array_merge($content, $this->getContent());
        $content = array_merge($content, $this->getFooterContent());

        return $this->frameViewContents($content);
    }

    public function getContent(): array
    {
        return $this->content;
    }

    public function renderTemplate(DynamicContent $template): string
    {
        $templatePath = $this->config->get('template', '');
        $templateEngine = new TemplateEngine($templatePath);
        foreach ($template->getData() as $key => $value) {
            $templateEngine->assign($key, $value);
        }

        return $templateEngine->render($template->content);
    }

    /**
     * Render a single item based on its type and return as string.
     */
    protected function renderItem(string $type, $item): string
    {
        switch ($type) {
            case 'style':
                return $this->renderSheet($item->getContent());
            case 'css':
                return "<style>\n" . $item->getContent() . "\n</style>\n";
            case 'script':
                return $this->renderScript($item->getContent());
            case 'js':
                return "<script>\n" . $item->getContent() . "\n</script>\n";
            case 'template':
                return $this->renderTemplate($item);
            case 'layout':
                return $this->renderLayout($item->getContent());
            case 'view':
                return $this->renderView($item->getContent(), $item->getData());
        }

        return '';
    }

    private function handleExtension(string $file, string $extension): string
    {
        if (pathinfo($file, PATHINFO_EXTENSION) !== $extension) {
            $file .= '.' . $extension;
        }

        return $file;
    }

    protected function renderView(string $file, array $data = []): string
    {
        $path = $this->config->get('view', '') . DS . $file;
        $path = $this->handleExtension($path, 'php');

        if (!file_exists($path)) {
            throw new \RuntimeException('View file not found: ' . $path);
        }

        // Extract variables for use in the view
        if (!empty($data)) {
            extract($data, EXTR_SKIP);
        }

        ob_start();
        include $path;

        return ob_get_clean();
    }

    final public function renderScript(string $script)
    {
        $scriptPath = $this->config->get('static', '') . DS . 'js';
        $this->handleExtension($scriptPath, 'js');
        $scriptPath = rtrim($scriptPath, '/') . '/' . $script;

        return "<script src='$scriptPath'></script>";
    }

    final public function renderSheet(string $sheet)
    {
        $sheetPath = $this->config->get('static', '') . DS . 'css';
        $this->handleExtension($sheetPath, 'css');
        $sheetPath = rtrim($sheetPath, '/') . '/' . $sheet;

        return "<link rel='stylesheet' type='text/css' href='$sheetPath'>";
    }

    protected function renderLayout(string $file)
    {
        $path = $this->config->get('layout', '');
        $path = rtrim($path, '/') . '/' . $file;
        $path = $this->handleExtension($path, 'html');

        if (!file_exists($path)) {
            throw new \RuntimeException('Layout file not found: ' . $path);
        }

        return file_get_contents($path);
    }

    public function clear(): void
    {
        $this->content = [];
        $this->output = '';
    }

    /**
     * Add content
     * 
     * @param array|ViewContent $content
     * @return void
     */
    public function addContent($content): void
    {
        if ($content instanceof ViewContent) {
            $this->add($content);
        } elseif (is_array($content)) {
            $viewContent = $this->createViewContent($content);
            $this->add($viewContent);
        }
    }

    public function addContents(array $contents): void
    {
        foreach ($contents as $content) {
            $this->addContent($content);
        }
    }

    public function addStyle($href, bool $newGroup = false)
    {
        if ($newGroup || empty($this->content)) {
            $this->content[] = new ViewContent();
        }
        $this->content[array_key_last($this->content)]->addStyle($href);
    }

    public function addScript($src, bool $newGroup = false)
    {
        if ($newGroup || empty($this->content)) {
            $this->content[] = new ViewContent();
        }
        $this->content[array_key_last($this->content)]->addScript($src);
    }

    public function addCss($css, bool $newGroup = false)
    {
        if ($newGroup || empty($this->content)) {
            $this->content[] = new ViewContent();
        }
        $this->content[array_key_last($this->content)]->addCss($css);
    }

    public function addJs($js, bool $newGroup = false)
    {
        if ($newGroup || empty($this->content)) {
            $this->content[] = new ViewContent();
        }
        $this->content[array_key_last($this->content)]->addJs($js);
    }

    public function addTemplate($tpl, array $data = [], bool $newGroup = false)
    {
        if ($newGroup || empty($this->content)) {
            $this->content[] = new ViewContent();
        }
        $this->content[array_key_last($this->content)]->addTemplate($tpl, $data);
    }

    public function addLayout($layout, bool $newGroup = false)
    {
        if ($newGroup || empty($this->content)) {
            $this->content[] = new ViewContent();
        }
        $this->content[array_key_last($this->content)]->addLayout($layout);
    }

    public function addView($view, array $data = [], bool $newGroup = false)
    {
        if ($newGroup || empty($this->content)) {
            $this->content[] = new ViewContent();
        }
        $this->content[array_key_last($this->content)]->addView($view, $data);
    }

    public function __toString(): string
    {
        return $this->render();
    }

    public function get()
    {
        /**
         * @var Response
         */
        $response = Container::get(Response::class);
        $response->setBody($this->render());
        $response->setType(Response::TYPE_HTML);

        return $response;
    }
}
