<?php

namespace System\Core\Base\View;

use System\Core\Base\View\Content\DynamicContent;
use System\Core\Base\View\Content\StaticContent;

class ViewContent
{
    /** @var StaticContent[] */
    public array $style = [];
    /** @var StaticContent[] */
    public array $script = [];
    /** @var StaticContent[] */
    public array $css = [];
    /** @var StaticContent[] */
    public array $js = [];
    /** @var StaticContent[] */
    public array $layout = [];
    /** @var DynamicContent[] */
    public array $template = [];
    /** @var DynamicContent[] */
    public array $view = [];

    public const DYNAMIC_CONTENT_KEYS = [
        'template',
        'view',
    ];

    public function __construct(array $data = [])
    {
        $this->setValues($data);
    }

    public function setValues(array $values)
    {
        foreach ($values as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function add(string $key, $value, array $data = [])
    {
        if (property_exists($this, $key)) {
            if (in_array($key, self::DYNAMIC_CONTENT_KEYS)) {
                $this->$key[] = new DynamicContent($value, $data);
            } else {
                $this->$key[] = new StaticContent($value);
            }
        }
    }

    public function addStyle(string $style)
    {
        $this->add('style', $style);
    }
    public function addScript(string $script)
    {
        $this->add('script', $script);
    }
    public function addCss(string $css)
    {
        $this->add('css', $css);
    }
    public function addJs(string $js)
    {
        $this->add('js', $js);
    }
    public function addLayout(string $layout)
    {
        $this->add('layout', $layout);
    }
    public function addTemplate(string $template, array $data = [])
    {
        $this->add('template', $template, $data);
    }
    public function addView(string $view, array $data = [])
    {
        $this->add('view', $view, $data);
    }

    public function get(string $key)
    {
        if (property_exists($this, $key)) {
            return $this->$key;
        }

        return null;
    }

    public function getAll(): array
    {
        return [
            'style' => $this->style,
            'script' => $this->script,
            'css' => $this->css,
            'js' => $this->js,
            'template' => $this->template,
            'layout' => $this->layout,
            'view' => $this->view,
        ];
    }

    public function clear()
    {
        $this->style = [];
        $this->script = [];
        $this->css = [];
        $this->js = [];
        $this->template = [];
        $this->layout = [];
        $this->view = [];
    }
}
