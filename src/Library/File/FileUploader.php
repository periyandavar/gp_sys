<?php

/**
 * FileUploader
 */

namespace System\Library;

use Loader\Config\ConfigLoader;

/**
 * FileUploader used to validate and handle file upload operations
 * User defined Error controller should implement this interface
 *
 */
class FileUploader
{
    /**
     * Allowed files for uploading
     *
     * @var array|string allowedFiles
     */
    private $_allowedFiles = '*';

    /**
     * Maximum file size of the uploaded file
     *
     * @var int maxFileSize
     */
    private $_maxFileSize = 2000000;

    /**
     * Sets Allowed files
     *
     * @param array $extensions Allowed Extensions list
     *
     * @return void
     */
    public function setAllowedFile(array $extensions)
    {
        $this->_allowedFiles = $extensions;
    }

    /**
     * Sets the maximum file uploaded size
     *
     * @param int $size Maximum file size
     *
     * @return void
     */
    public function setMaxFileSize(int $size)
    {
        $this->_maxFileSize = $size;
    }

    /**
     * Checks wheter the file has valid extension or not
     *
     * @param string $filename File name
     *
     * @return bool
     */
    public function checkExtension(string $filename): bool
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($this->_allowedFiles == '*') {
            return true;
        } elseif (is_array($this->_allowedFiles)) {
            return in_array($extension, $this->_allowedFiles);
        } elseif (strtolower($this->_allowedFiles) == $extension) {
            return true;
        }

        return false;
    }

    /**
     * Validates the file size
     *
     * @param int $size File size
     *
     * @return bool
     */
    public function validateSize(int $size): bool
    {
        if ($this->_maxFileSize < $size) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Checks whether the file is valid or not
     *
     * @param array|null $file file array
     *
     * @return bool
     */
    public function validateFile(?array $file): bool
    {
        if ($file == null) {
            return false;
        }
        if ($this->checkExtension($file['name'])
            && $this->validateSize($file['size'])
        ) {
            return true;
        }

        return false;
    }

    /**
     * Upload the file
     *
     * @param array|null  $file        File array
     * @param string      $filename    File Name
     * @param string|null $subfolder   Sub folder name
     * @param string      $destination destination location
     * @param bool        $overwrite   wheter override the existing file or not
     *
     * @return bool
     */
    public function uploadFile(
        ?array $file,
        string $filename,
        ?string $subfolder = null,
        ?string $destination = null,
        bool $overwrite = false
    ): bool {
        $config = ConfigLoader::getConfig('config')->getAll();
        if ($file == null) {
            return false;
        }
        $destination = $destination
            ?? ($config['upload'] == '' ? 'upload' : $config['upload']);
        $destination .= '/' . ($subfolder ?? '');
        $destination .= '/' . $filename;
        if ($this->validateFile($file)) {
            if (!$overwrite) {
                if (file_exists($destination)) {
                    return false;
                }
            }
            move_uploaded_file($file['tmp_name'], $destination);

            return true;
        }

        return false;
    }
}
