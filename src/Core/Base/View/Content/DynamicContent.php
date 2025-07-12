<?php

namespace System\Core\Base\View\Content;

class DynamicContent extends StaticContent
{
    public $data = [];

    /**
     * Constructor for DynamicContent.
     *
     * @param string $content The content to be displayed.
     * @param array  $data    Additional data to be passed to the view.
     */
    public function __construct(string $content, array $data = [])
    {
        parent::__construct($content);
        $this->data = $data;
    }

    /**
     * Gets the data associated with the dynamic content.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
