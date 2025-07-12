<?php

namespace System\Core\Base\View\Content;

class DynamicContent extends StaticContent
{
    public $data = [];

    public function __construct(string $content, array $data = [])
    {
        parent::__construct($content);
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
