<?php

/**
 * CsvExporter
 */

namespace System\Libraray\Export;

/**
 * Export the data into csv
 */
class CsvExporter
{
    /**
     * Filename
     *
     * @var string|null
     */
    private $_filename = null;

    /**
     * Deletes the csv file
     */
    public function __destruct()
    {
        if ($this->_filename != null) {
            @unlink($this->_filename);
        }
    }

    /**
     * Generates Csv
     *
     * @param array      $data       Data
     * @param null|array $ignoreList Ignore values
     *
     * @return void
     */
    public function generate(array $data, ?array $ignoreList)
    {
        $this->_filename = uniqid() . '.csv';
        $data = json_decode(json_encode($data), true);
        $fp = fopen($this->_filename, 'w');
        $headings = array_keys($data[0]);
        foreach ($ignoreList as $ignore) {
            unset($headings[array_search($ignore, $headings)]);
        }
        foreach ($headings as $key => $value) {
            $headings[$key] = ucfirst($value);
        }
        fputcsv($fp, $headings);
        foreach ($data as $row) {
            foreach ($ignoreList as $ignore) {
                unset($row[$ignore]);
            }
            fputcsv($fp, $row);
        }
        fclose($fp);
    }

    /**
     * Stores the csv file on the server
     *
     * @param string $destination Destination with filename
     *
     * @return void
     */
    public function store(string $destination)
    {
        rename($this->_filename, $destination);
    }

    /**
     * Send csv file to the client
     *
     * @return void
     */
    public function send()
    {
        header("Content-Disposition: attachment; filename=\"$this->_filename\"");
        header('Content-Type: application/vnd.ms-excel');
        readfile($this->_filename);
    }
}
