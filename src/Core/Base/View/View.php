<?php

namespace System\Core\Base\View;

use Loader\Container;
use Router\Response\Response;
use System\Core\Base\Context\Context;
use System\Core\Base\View\Content\DynamicContent;
use System\Core\TemplateEngine;
use System\Core\Utility;

class View
{
    protected Context $context;

    /**
     * @var ViewContent[]
     */
    protected array $content = [];

    protected string $output = '';

    /**
     * View constructor.
     */
    public function __construct()
    {
        $this->context = Container::get('context');
    }

    /**
     * Returns Header content.
     *
     * @return array
     */
    public function getHeaderContent(): array
    {
        return [];
    }

    /**
     * Returns Footer content.
     *
     * @return array
     */
    public function getFooterContent(): array
    {
        return [];
    }

    /**
     * Returns Body content.
     *
     * @return array
     */
    public function BodyContent(): array
    {
        return [];
    }

    /**
     * Frames the view contents from the provided data.
     *
     * @param array $data
     *
     * @return ViewContent[]
     */
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

    /**
     * Create a ViewContent instance from the provided data.
     *
     * @param array $data
     *
     * @return ViewContent
     */
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

    /**
     * Add a view content.
     *
     * @param \System\Core\Base\View\ViewContent $item
     *
     * @return void
     */
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

    /**
     * Get all contents from the view.
     *
     * @return ViewContent[]
     */
    public function getAllContents(): array
    {
        $content = [];
        $content = array_merge($content, $this->getHeaderContent());
        $content = array_merge($content, $this->BodyContent());
        $content = array_merge($content, $this->getContent());
        $content = array_merge($content, $this->getFooterContent());

        return $this->frameViewContents($content);
    }

    /**
     * Get the content of the view.
     *
     * @return array
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * Render a template with the provided data.
     *
     * @param DynamicContent $template
     *
     * @return string
     */
    public function renderTemplate(DynamicContent $template): string
    {
        $config = $this->getConfig();
        $templatePath = $config->get('template', '');
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

    /**
     * Handle file extension for the view.
     *
     * @param string $file
     * @param string $extension
     *
     * @return string
     */
    private function handleExtension(string $file, string $extension): string
    {
        if (pathinfo($file, PATHINFO_EXTENSION) !== $extension) {
            $file .= '.' . $extension;
        }

        return $file;
    }

    /**
     * Render a view file with the provided data.
     *
     * @param string $file
     * @param array  $data
     *
     * @return string
     */
    protected function renderView(string $file, array $data = []): string
    {
        $path = $this->getConfig()->get('view', '') . DIRECTORY_SEPARATOR . $file;
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

    /**
     * Render a script file.
     *
     * @param string $script
     *
     * @return string
     */
    final public function renderScript(string $script)
    {
        $scriptPath = $this->getConfig()->get('static', '') . DIRECTORY_SEPARATOR . 'js';
        $this->handleExtension($scriptPath, 'js');
        $scriptPath = rtrim($scriptPath, '/') . '/' . $script;

        return "<script src='$scriptPath'></script>";
    }

    /**
     * Render a style sheet.
     *
     * @param string $sheet
     *
     * @return string
     */
    final public function renderSheet(string $sheet)
    {
        $sheetPath = $this->getConfig()->get('static', '') . DIRECTORY_SEPARATOR . 'css';
        $this->handleExtension($sheetPath, 'css');
        $sheetPath = rtrim($sheetPath, '/') . '/' . $sheet;

        return "<link rel='stylesheet' type='text/css' href='$sheetPath'>";
    }

    /**
     * Render a layout file.
     *
     * @param string $file
     *
     * @return string
     */
    protected function renderLayout(string $file)
    {
        $path = $this->getConfig()->get('layout', '');
        $path = rtrim($path, '/') . '/' . $file;
        $path = $this->handleExtension($path, 'html');

        if (!file_exists($path)) {
            throw new \RuntimeException('Layout file not found: ' . $path);
        }

        return file_get_contents($path);
    }

    /**
     * Clear the view content.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->content = [];
        $this->output = '';
    }

    /**
     * Add content
     *
     * @param  array|ViewContent $content
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

    /**
     * Add multiple contents.
     *
     * @param array $contents
     *
     * @return void
     */
    public function addContents(array $contents): void
    {
        foreach ($contents as $content) {
            $this->addContent($content);
        }
    }

    /**
     * Add a style sheet.
     *
     * @param string $href
     * @param bool   $newGroup
     *
     * @return void
     */
    public function addStyle($href, bool $newGroup = false)
    {
        if ($newGroup || empty($this->content)) {
            $this->content[] = new ViewContent();
        }
        $this->content[array_key_last($this->content)]->addStyle($href);
    }

    /**
     * Add a script file.
     *
     * @param string $src
     * @param bool   $newGroup
     *
     * @return void
     */
    public function addScript($src, bool $newGroup = false)
    {
        if ($newGroup || empty($this->content)) {
            $this->content[] = new ViewContent();
        }
        $this->content[array_key_last($this->content)]->addScript($src);
    }

    /**
     * Add a CSS style.
     *
     * @param string $css
     * @param bool   $newGroup
     *
     * @return void
     */
    public function addCss($css, bool $newGroup = false)
    {
        if ($newGroup || empty($this->content)) {
            $this->content[] = new ViewContent();
        }
        $this->content[array_key_last($this->content)]->addCss($css);
    }

    /**
     * Add a JavaScript file.
     *
     * @param string $js
     * @param bool   $newGroup
     *
     * @return void
     */
    public function addJs($js, bool $newGroup = false)
    {
        if ($newGroup || empty($this->content)) {
            $this->content[] = new ViewContent();
        }
        $this->content[array_key_last($this->content)]->addJs($js);
    }

    /**
     * Add a template file.
     *
     * @param string $tpl
     * @param array  $data
     * @param bool   $newGroup
     *
     * @return void
     */
    public function addTemplate($tpl, array $data = [], bool $newGroup = false)
    {
        if ($newGroup || empty($this->content)) {
            $this->content[] = new ViewContent();
        }
        $this->content[array_key_last($this->content)]->addTemplate($tpl, $data);
    }

    /**
     * Add a layout file.
     *
     * @param string $layout
     * @param bool   $newGroup
     *
     * @return void
     */
    public function addLayout($layout, bool $newGroup = false)
    {
        if ($newGroup || empty($this->content)) {
            $this->content[] = new ViewContent();
        }
        $this->content[array_key_last($this->content)]->addLayout($layout);
    }

    /**
     * Add a view file with data.
     *
     * @param string $view
     * @param array  $data
     * @param bool   $newGroup
     *
     * @return void
     */
    public function addView($view, array $data = [], bool $newGroup = false)
    {
        if ($newGroup || empty($this->content)) {
            $this->content[] = new ViewContent();
        }
        $this->content[array_key_last($this->content)]->addView($view, $data);
    }

    /**
     * Convert the view to a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * Get the response object.
     *
     * @return Response
     */
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

    /**
     * Get the configuration loader.
     *
     * @return \Loader\Config\ConfigLoader
     */
    public function getConfig()
    {
        return $this->context->getConfig();
    }
}
