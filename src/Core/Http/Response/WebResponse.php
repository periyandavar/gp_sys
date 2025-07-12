<?php

namespace System\Core\Http\Response;

class WebResponse extends Response
{
    /**
    * Render and send an HTML view.
    */
    public function html(string $html, int $status = 200): void
    {
        $this->setHeader('Content-Type', 'text/html; charset=utf-8');
        $this->setStatusCode($status);
        $this->setBody($html);
        $this->send();
    }

    /**
     * Render and send a file download response.
     */
    public function download(string $filePath, ?string $downloadName = null): void
    {
        if (!file_exists($filePath)) {
            $this->setStatusCode(404);
            $this->setBody('File not found.');
            $this->send();

            return;
        }

        $downloadName = $downloadName ?? basename($filePath);
        $this->setHeader('Content-Description', 'File Transfer');
        $this->setHeader('Content-Type', 'application/octet-stream');
        $this->setHeader('Content-Disposition', 'attachment; filename="' . $downloadName . '"');
        $this->setHeader('Expires', '0');
        $this->setHeader('Cache-Control', 'must-revalidate');
        $this->setHeader('Pragma', 'public');
        $this->setHeader('Content-Length', (string) filesize($filePath));
        readfile($filePath);
        exit;
    }

    /**
     * Handle exceptions and send an error response.
     *
     * @param  \Exception  $e
     * @return WebResponse
     */
    public function handleException(\Exception $e): Response
    {
        $this->setStatusCode($e->getCode());
        $this->html($e->getMessage() . '' . $e->getFile() . '' . $e->getLine() . '' . $e->getTraceAsString(), $e->getCode());

        return $this;
    }
}
