<?php

/**
 * Exporter File
 */

namespace System\Libraray\Export;

/**
 * Exporter Class used to store the input Exporter
 *
 */
interface Exporter
{
    /**
     * Calls exporter generate function
     *
     * @param array      $data   Data
     * @param null|array $ignore Ignore values
     *
     * @return void
     */
    public function generate(array $data, ?array $ignore);

    /**
     * Send csv file to the client
     *
     * @return void
     */
    public function send();

    /**
     * Stores the excel file on the server
     *
     * @param string $destination Destination with filename
     *
     * @return void
     */
    public function store(string $destination);
}
