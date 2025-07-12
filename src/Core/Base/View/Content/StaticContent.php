<?php

namespace System\Core\Base\View\Content;

class StaticContent
{
    public string $content;

    /**
     * Constructor for StaticContent.
     *
     * @param string $content The content to be displayed.
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * Gets the content.
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }
}
