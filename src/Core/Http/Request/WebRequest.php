<?php

namespace System\Core\Http\Request;

class WebRequest extends Request
{
    /**
     * Get the client's IP address.
     */
    public function getClientIp(): ?string
    {
        $ip = $this->server('HTTP_CLIENT_IP');
        if (!empty($ip)) {
            return $ip;
        }
        $forwarded = $this->server('HTTP_X_FORWARDED_FOR');
        if (!empty($forwarded)) {
            return explode(',', $forwarded)[0];
        }
        return $this->server('REMOTE_ADDR');
    }

    /**
     * Get the user agent string.
     */
    public function getUserAgent(): ?string
    {
        return $this->server('HTTP_USER_AGENT');
    }

    /**
     * Get the referer URL.
     */
    public function getReferer(): ?string
    {
        return $this->server('HTTP_REFERER');
    }

    /**
     * Check if the request is over HTTPS.
     */
    public function isSecure(): bool
    {
        $https = $this->server('HTTPS');
        $port = $this->server('SERVER_PORT');
        return (!empty($https) && $https !== 'off') || ($port == 443);
    }
}