<?php

namespace System\Core\Base\View\Content;

class StaticContent
{
    public string $content;

    public function __construct($content)
    {
        $this->content = $content;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
