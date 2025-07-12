<?php

namespace System\Core;

use System\Core\Exception\FrameworkException;

class TemplateEngine
{
    private string $templateDir;
    private array $variables = [];

    /**
     * Constructor.
     *
     * @param string $templateDir
     */
    public function __construct(string $templateDir)
    {
        $this->templateDir = rtrim($templateDir, '/') . '/';
    }

    /**
     * Assign value to the template key.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function assign(string $key, $value): void
    {
        $this->variables[$key] = $value;
    }

    /**
     * Render the template with assigned variables.
     *
     * @param  string             $template
     * @return string
     * @throws FrameworkException
     */
    public function render(string $template): string
    {
        $content = $this->loadTemplate($template);
        $content = $this->parseIncludes($content);
        $content = $this->parseConditionals($content);
        $content = $this->parseLoops($content);
        $content = $this->parseVariables($content);

        return $content;
    }

    /**
     * Load template file
     *
     * @param  string                                    $template
     * @throws \System\Core\Exception\FrameworkException
     *
     * @return string
     */
    private function loadTemplate(string $template): string
    {
        $path = $this->templateDir . $template;
        if (!file_exists($path)) {
            throw new FrameworkException("Template not found: $template", FrameworkException::FILE_NOT_FOUND);
        }

        return file_get_contents($path);
    }

    /**
     * Parse and replace the variable in the template.
     *
     * @param string $content
     *
     * @return string
     */
    private function parseVariables(string $content): string
    {
        foreach ($this->variables as $key => $value) {
            if (!is_array($value)) {
                $content = str_replace('{{ ' . $key . ' }}', $value, $content);
            }
        }

        return $content;
    }

    /**
     * Handle the template include.
     *
     * @param string $content
     *
     * @return string
     */
    private function parseIncludes(string $content): string
    {
        return preg_replace_callback('/\{\{\s*include\s+[\'"](.+?)[\'"]\s*\}\}/', function($matches) {
            return $this->loadTemplate($matches[1]);
        }, $content);
    }

    /**
     * Parse the conditional operator.
     *
     * @param string $content
     *
     * @return string
     */
    private function parseConditionals(string $content): string
    {
        return preg_replace_callback('/\{\{\s*if\s+(\w+)\s*\}\}(.*?)\{\{\s*endif\s*\}\}/s', function($matches) {
            $var = $matches[1];
            $block = $matches[2];

            return !empty($this->variables[$var]) ? $block : '';
        }, $content);
    }

    /**
     * Handle the loops.
     *
     * @param  string $content
     * @return string
     */
    private function parseLoops(string $content): string
    {
        return preg_replace_callback('/\{\{\s*foreach\s+(\w+)\s+as\s+(\w+)\s*\}\}(.*?)\{\{\s*endforeach\s*\}\}/s', function($matches) {
            $arrayVar = $matches[1];
            $itemVar = $matches[2];
            $block = $matches[3];
            $output = '';

            if (!empty($this->variables[$arrayVar]) && is_array($this->variables[$arrayVar])) {
                foreach ($this->variables[$arrayVar] as $item) {
                    $temp = str_replace('{{ ' . $itemVar . ' }}', $item, $block);
                    $output .= $temp;
                }
            }

            return $output;
        }, $content);
    }
}
