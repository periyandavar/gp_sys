<?php

/**
 * BaseRestController
 */

namespace System\Core;

if (!defined('API_REQ')) {
    return;
}
/**
 * Super class for all rest based controller. All rest basef controllers should
 * extend this controller
 * BaseRestController class consists of basic level functions for various purposes
 */
abstract class BaseRestController extends SysController
{
    /**
     * Handles GET requests
     *
     * @return void
     */
    public function get()
    {
    }
    /**
     * Handles POST request
     *
     * @return void
     */
    public function create()
    {
    }
    /**
     * Handles PUT request
     *
     * @return void
     */
    public function update()
    {
    }
    /**
     * Handles DELETE request
     *
     * @return void
     */
    public function delete()
    {
    }
    /**
     * Handles PATCH request
     *
     * @return void
     */
    public function patch()
    {
    }
}
