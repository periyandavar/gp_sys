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

    /**
     * Constructor for ViewContent.
     *
     * @param array $data Initial data to set in the view content.
     */
    public function __construct(array $data = [])
    {
        $this->setValues($data);
    }

    /**
     * Set values for the view content properties.
     *
     * @param array $values Associative array of property names and their values.
     */
    public function setValues(array $values)
    {
        foreach ($values as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Add a content item to the view content.
     *
     * @param string $key   The property name to add the content to.
     * @param mixed  $value The content value.
     * @param array  $data  Additional data for dynamic content.
     */
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

    /**
     * Add style content to the view.
     *
     * @param string $style
     *
     * @return void
     */
    public function addStyle(string $style)
    {
        $this->add('style', $style);
    }

    /**
     * Add script content to the view.
     *
     * @param string $script
     *
     * @return void
     */
    public function addScript(string $script)
    {
        $this->add('script', $script);
    }

    /**
     * Add CSS content to the view.
     *
     * @param string $css
     *
     * @return void
     */
    public function addCss(string $css)
    {
        $this->add('css', $css);
    }

    /**
     * Add JS content to the view.
     *
     * @param string $js
     *
     * @return void
     */
    public function addJs(string $js)
    {
        $this->add('js', $js);
    }

    /**
     * Add layout content to the view.
     *
     * @param string $layout
     *
     * @return void
     */
    public function addLayout(string $layout)
    {
        $this->add('layout', $layout);
    }

    /**
     * Add template content to the view.
     *
     * @param string $template
     * @param array  $data     Additional data for dynamic content.
     *
     * @return void
     */
    public function addTemplate(string $template, array $data = [])
    {
        $this->add('template', $template, $data);
    }

    /**
     * Add view content to the view.
     *
     * @param string $view The view name.
     * @param array  $data Additional data for the view.
     *
     * @return void
     */
    public function addView(string $view, array $data = [])
    {
        $this->add('view', $view, $data);
    }

    /**
     * Get a content item from the view content.
     *
     * @param string $key The property name to get the content from.
     *
     * @return mixed|null
     */
    public function get(string $key)
    {
        if (property_exists($this, $key)) {
            return $this->$key;
        }

        return null;
    }

    /**
     * Get all content items from the view content.
     *
     * @return array
     */
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

    /**
     * Clear all content items from the view content.
     *
     * @return void
     */
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
