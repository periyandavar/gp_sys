<?php

/**
 * Cache
 */

namespace System\Core;

class Cache
{
    private $file;

    private $cachetime;

    /**
     * Instantiate new Cache instance
     *
     * @param string $file Filename
     */
    public function __construct($file, $dir, $cachetime = 18000)
    {
        $file = str_replace('/', '.', trim($file, '/'));
        $this->cachetime = $cachetime;
        $this->file = $dir . $file . '.cache.html';
        if (!is_dir($dir)) {
            mkdir($dir, 0777);
        }
    }

    /**
     * Sends the cache file if exists
     *
     * @return void
     */
    public function cache()
    {
        if (file_exists($this->file) && time() - $this->cachetime < filemtime($this->file)) {
            readfile($this->file);
            exit;
        }
    }

    /**
     * Creates the cache file
     *
     * @param string|null $content Content
     *
     * @return void
     */
    public function store(?string $content = null)
    {
        $content = $content ?? ob_get_contents();
        $cache = fopen($this->file, 'w');
        $content = '<!-- Cached copy, generated ' . date('H:i', filemtime($this->file)) . " -->\n" . $content;
        fwrite($cache, $content);
        fclose($cache);
    }
}
