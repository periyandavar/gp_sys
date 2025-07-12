<?php

/**
 * Mailer
 */

namespace System\Library;

use Loader\Config\ConfigLoader;

/**
 * Mailer class to send mail
 *
 */
class Mailer
{
    /**
     * This function will sends the mail
     *
     * @param string     $from    From address
     * @param string     $to      To address
     * @param string     $subject Subject
     * @param string     $layout  Layout
     * @param array|null $data    Data to be inclued to layout
     *
     * @return bool
     */
    public function send(
        string $from,
        string $to,
        string $subject,
        string $layout,
        ?array $data = null
    ) {
        $config = ConfigLoader::getConfig('config')->getAll();
        $path = $config['layout'] . '' . $layout;
        if (file_exists($path)) {
            $message = file_get_contents($path);
            if ($data != null) {
                foreach ($data as $key => $value) {
                    $key = strtoupper($key);
                    $message = str_replace('{{' . $key . '}}', $value, $message);
                }
            }

            $headers = 'From: ' . $from . "\r\n";
            $headers .= 'Reply-To: ' . $from . "\r\n";
            $headers .= "CC: susan@example.com\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

            return (mail($to, $subject, $message, $headers));
        } else {
            return false;
        }
    }
}
