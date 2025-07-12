<?php

namespace System\Library\Security;

/**
 * Security class used to perform encryption and decryption
 *
 */
class Security
{
    private $_method;
    private $_key;
    private $_options;
    private $_iv;

    /**
     * Instantiate the new security instance
     *
     * @param string $method  Method name
     * @param string $key     Key
     * @param int    $options Options
     * @param string $iv      Initialization vector
     */
    public function __construct(
        string $method = 'aes-128-cbc',
        string $key = null,
        int $options = 0,
        string $iv = null
    ) {
        $this->_method = $method;
        $this->_key = ($key != null) ? $key : random_bytes(32);
        $this->_options = $options;
        $this->_iv = (($iv != null)
            ? $iv
            : random_bytes(openssl_cipher_iv_length($this->_method)));
    }

    /**
     * Encryption
     *
     * @param string $data plain text
     *
     * @return string
     */
    public function encrypt(string $data): string
    {
        $cipher = openssl_encrypt(
            $data,
            $this->_method,
            $this->_key,
            $this->_options,
            $this->_iv
        );

        return $cipher;
    }

    /**
     * Decryption
     *
     * @param string $cipher Chipher text
     *
     * @return string|bool
     */
    public function decrypt(string $cipher)
    {
        $data = openssl_decrypt(
            $cipher,
            $this->_method,
            $this->_key,
            $this->_options,
            $this->_iv
        );

        return $data;
    }
}
