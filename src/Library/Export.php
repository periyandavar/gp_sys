<?php

/**
 * Exporter
 * php version 7.3.5
 *
 * @category Exporter
 * @package  Library
 * @author   Periyandavar <periyandavar@gmail.com>
 * @license  http://license.com license
 * @link     http://url.com
 */

namespace System\Library;

use System\Libraray\Export\CsvExporter;
use System\Libraray\Export\PdfExporter;

/**
 * Exporter Class used to store the input Exporter
 * User defined Error controller should implement this interface
 *
 * @category Exporter
 * @package  Library
 * @author   Periyandavar <periyandavar@gmail.com>
 * @license  http://license.com license
 * @link     http://url.com
 */
class Export
{
    private $_exporter;

    /**
     * Instantiate new Export instance
     *
     * @param string $type Export type
     */
    public function __construct(string $type)
    {
        $this->_exporter = $type == 'csv' ? new CsvExporter() : new PdfExporter();
    }

    /**
     * Generates the export file
     *
     * @param array      $data   Data
     * @param null|array $ignore Ignore values
     *
     * @return void
     */
    public function generate(array $data, ?array $ignore)
    {
        $this->_exporter->generate($data, $ignore);
    }

    /**
     * Sends the export file
     *
     * @return void
     */
    public function send()
    {
        $this->_exporter->send();
    }

    /**
     * Store the export file
     *
     * @param string $destination Destination with filename
     *
     * @return void
     */
    public function store(string $destination)
    {
        $this->_exporter->store($destination);
    }
}
