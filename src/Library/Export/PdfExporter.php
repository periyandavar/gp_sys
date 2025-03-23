<?php

namespace System\Libraray\Export;

use Fpdf\Fpdf;

class PdfExporter
{
    /**
     * Pdf file pointer
     *
     * @var FPDF
     */
    private $_pdf = null;

    /**
     * Instantiate new PdfExporter instance
     */
    public function __construct()
    {
        $this->_pdf = new Fpdf();
        $this->_pdf->AliasNbPages();
    }

    /**
     * Generates Excel
     *
     * @param array      $data       Data
     * @param null|array $ignoreList Ignore values
     *
     * @return void
     */
    public function generate(array $data, ?array $ignoreList)
    {
        $this->_pdf->AddPage();
        $data = json_decode(json_encode($data), true);
        $this->_pdf->SetFont('Arial', 'B', 7);
        $cellWidth = 30;
        $cellHeight = 10;
        $currentY = $this->_pdf->GetY();
        $startX = $currentX = $this->_pdf->GetX();
        $this->_pdf->SetXY($currentX, $currentY);
        $this->_pdf->Multicell(10, $cellHeight, 'Sl. No', 1);
        $currentX += 10;
        $headings = array_keys($data[0]);
        foreach ($headings as $heading) {
            if (in_array($heading, $ignoreList)) {
                continue;
            }
            $this->_pdf->SetXY($currentX, $currentY);
            $this->_pdf->multicell($cellWidth, $cellHeight, ucfirst($heading), 1);
            $currentX += $cellWidth;
        }
        $currentX = $startX;
        $currentY += $cellHeight;
        $i = 1;
        foreach ($data as $row) {
            $this->_pdf->Ln();
            $this->_pdf->SetXY($currentX, $currentY);
            $this->_pdf->multicell(10, $cellHeight, $i++, 1);
            $currentX += 10;
            foreach ($row as $column) {
                if (in_array(array_search($column, $row), $ignoreList)) {
                    continue;
                }
                $this->_pdf->SetXY($currentX, $currentY);
                $this->_pdf->multicell($cellWidth, $cellHeight, $column, 1);
                $currentX += $cellWidth;
            }
            $currentX = $startX;
            $currentY += $cellHeight;
        }
    }

    /**
     * Stores the pdf file on the server
     *
     * @param string $destination Destination with filename
     *
     * @return void
     */
    public function store(string $destination)
    {
        $this->_pdf->Output('F', $destination);
    }

    /**
     * Send pdf file to the client
     *
     * @return void
     */
    public function send()
    {
        $filename = uniqid();
        $this->_pdf->Output('d', $filename . '.pdf');
    }
}
