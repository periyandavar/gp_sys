<?php

/**
 * Captcha
 */

namespace System\Library;

use Exception;

/**
 * Captcha class to generate captcha
 *
 */
class Captcha
{
    private $_allowedChar = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    private $_captcha;

    /**
     * Generates the random string
     *
     * @param int $length length
     *
     * @return string
     */
    public function randomStr(int $length): string
    {
        $str = str_shuffle($this->_allowedChar);
        $str = substr($str, 0, $length);

        return $str;
    }

    /**
     * Generates the captcha
     *
     * @param int $length length
     *
     * @return string
     */
    public function generate(int $length = 4)
    {
        $str = $this->randomStr($length);
        $height = 25;
        $width = 65;
        $this->_captcha = imagecreate($width, $height);
        imagecolorallocate($this->_captcha, 0, 0, 0);
        $white = imagecolorallocate($this->_captcha, 255, 255, 255);
        $font_size = 14;
        imagestring($this->_captcha, $font_size, 5, 5, $str, $white);

        return $str;
    }

    /**
     * Shows captcha Image
     *
     * @return void
     */
    public function show()
    {
        if (!(isset($this->_captcha))) {
            throw new Exception('Captcha is not creted');
        }
        imagejpeg($this->_captcha, null, 80);
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Content-type: image/jpg');
    }

    /**
     * Destroys captcha
     */
    public function __destruct()
    {
        if (isset($this->_captcha)) {
            imagedestroy($this->_captcha);
        }
    }

    /**
     * Stores captcha in server as jpg
     *
     * @param string      $name Filename without extension
     * @param string|null $dir  directory
     *
     * @return void
     */
    public function store(string $name, ?string $dir = null)
    {
        if (!(isset($this->img))) {
            throw new Exception('Captcha is not creted');
        }
        $name .= '.jpg';
        $save = $dir == null ? $name : $dir . '/' . $name;
        imagejpeg($this->_captcha, $save);
    }
}
