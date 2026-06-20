<?php

if (!class_exists('TCPDFNetsoluciones') && class_exists('TCPDF')) {
    class TCPDFNetsoluciones extends TCPDF
    {
        public function Footer()
        {
            $this->SetY(-12);
            $this->SetFont('dejavusans', 'U', 8);
            $this->SetTextColor(0, 102, 204);
            $this->Cell(
                0,
                8,
                'Diseñado por Netsoluciones.com',
                0,
                0,
                'C',
                false,
                'https://netsoluciones.com/'
            );
            $this->SetTextColor(0, 0, 0);
        }
    }
}
