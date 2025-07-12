<?php

/**
 * WebController
 */

namespace System\Core\Base\Controller;

use System\Core\Base\View\View;
use System\Core\Utility;

/**
 * Super class for all WebController. All WebControllers should extend this WebController
 * WebController class consists of basic level functions for various purposes
 *
 */
class WebController extends Controller
{
    protected View $view;

    public function __construct()
    {
        $this->view = new View();
        parent::__construct();
    }

    /**
     * Sets the view instance.
     *
     * @param View $view
     *
     * @return void
     */
    public function setView(View $view): void
    {
        $this->view = $view;
    }

    /**
     * Gets the view instance.
     *
     * @return View
     */
    public function getView(): View
    {
        return $this->view;
    }

    /**
     * This function will load the required View(php) file without error on failure
     * only files with .php extension are allowed and those files should
     * store on View Folder
     *
     * @param string $file     filename without extension
     * @param array  $data     varaibles to passed to the view
     * @param bool   $newGroup
     *
     * @return void
     */
    final protected function addView(string $file, array $data = [], bool $newGroup = false)
    {
        $this->view->addView($file, $data, $newGroup);
    }

    /**
     * This function will redirect the page
     *
     * @param string $url       page to redirect
     * @param bool   $permanent optional default:false indicates
     *                          whether the redirect is permanent or not
     *
     * @return void
     */
    final protected function redirect(string $url, bool $permanent = false)
    {
        Utility::redirectURL($url, $permanent);
    }

    /**
     * This function loads html layout files
     *
     * @param string $file     html filename with extension
     * @param bool   $newGroup
     *
     * @return void
     */
    final protected function addLayout(string $file, bool $newGroup = false)
    {
        $this->view->addLayout($file, $newGroup);
    }

    /**
     * This functions include the script file
     *
     * @param string $script   filename with extension
     * @param bool   $newGroup
     *
     * @return void
     */
    final public function addScript(string $script, bool $newGroup = false)
    {
        $this->view->addScript($script, $newGroup);
    }

    /**
     * This functions include the style sheet
     *
     * @param string $sheet    filename with extension
     * @param bool   $newGroup
     *
     * @return void
     */
    final public function addStyle($sheet, bool $newGroup = false)
    {
        $this->view->addStyle($sheet, $newGroup);
    }

    /**
     * Adds the Js script on the view
     *
     * @param string $script   Script
     * @param bool   $newGroup
     *
     * @return void
     */
    final public function addJs(string $script, bool $newGroup = false)
    {
        $this->view->addJs($script, $newGroup);
    }

    /**
     * Adds the CSS style on the view
     *
     * @param string $style    Style
     * @param bool   $newGroup
     *
     * @return void
     */
    final public function addCss(string $style, bool $newGroup = false)
    {
        $this->view->addCss($style, $newGroup);
    }
}
