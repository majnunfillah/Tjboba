<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Dompdf;
use Dompdf\Options;
use HTML5;
use InvalidArgumentException;

class DomPDFService
{
    private $dompdf;
    private $title = "LAPORAN";
    private $leftHeader;
    private $leftWidth;
    private $midHeader;
    private $midWidth;
    private $rightHeader;
    private $rightWidth;
    private $headerCount = 0;

    private $bodyHtml;

    public function __construct($options = null)
    {
        if ($options === null) {
            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isJavascriptEnabled', true);
            $options->set('defaultPaperSize', 'A4');
            $options->set('defaultPaperOrientation', 'portrait');
            $options->set('dpi', 300);
            $options->set('defaultMediaType', 'all');
            $options->set('isFontSubsettingEnabled', true);
        } else if (is_array($options)) {
            $options = array_merge([
                'defaultPaperSize' => 'A4',
                'defaultPaperOrientation' => 'portrait',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'isJavascriptEnabled' => true,
                'dpi' => 300,
                'defaultMediaType' => 'all',
                'isFontSubsettingEnabled' => true,
            ], $options);

            $options = new Options($options);
        }

        $this->dompdf = new Dompdf($options);
    }

    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }

    public function setLeftHeader($html, $width = null)
    {
        // if (!($html instanceof HTML5 || is_string($html))) {
        //     throw new InvalidArgumentException('Argument must be a string or an instance of HTML5.');
        // }

        if (!$this->leftHeader) {
            $this->headerCount++;
        }

        $this->leftHeader = $html;
        $this->leftWidth = $width;

        return $this;
    }

    public function setMidHeader($html, $width = null)
    {
        // if (!($html instanceof HTML5 || is_string($html))) {
        //     throw new InvalidArgumentException('Argument must be a string or an instance of HTML5.');
        // }

        if (!$this->midHeader) {
            $this->headerCount++;
        }

        $this->midHeader = $html;
        $this->midWidth = $width;

        return $this;
    }

    public function setRightHeader($html, $width = null)
    {
        // if (!($html instanceof HTML5 || is_string($html))) {
        //     throw new InvalidArgumentException('Argument must be a string or an instance of HTML5.');
        // }

        if (!$this->rightHeader) {
            $this->headerCount++;
        }

        $this->rightHeader = $html;
        $this->rightWidth = $width;

        return $this;
    }

    public function setBody($html)
    {

        $this->bodyHtml = $html;
        return $this;
    }

    public function outputDomPdf()
    {

        // $this->dompdf->loadHtml($layout);
        // $this->dompdf->render();

        return Pdf::loadView(...$this->remapHtml());
    }

    public function outputHtml()
    {
        $layout = $this->outputView()->render();

        return $layout;
    }
    
    public function outputView() {
        return view(...$this->remapHtml());
    }

    public function mapHeader()
    {
        return view('components.pdf-service.header', [
            'headerCount' => $this->headerCount,
            'leftHeader' => $this->leftHeader,
            'leftWidth' => $this->leftWidth,
            'midHeader' => $this->midHeader,
            'midWidth' => $this->midWidth,
            'rightHeader' => $this->rightHeader,
            'rightWidth' => $this->rightWidth,
        ]);
    }

    public function remapHtml()
    {
        return ['components.pdf-service.layout', [
            'headerHtml' => $this->mapHeader(),
            'title' => $this->title,
            'bodyHtml' => $this->bodyHtml,
        ]];
    }
}
