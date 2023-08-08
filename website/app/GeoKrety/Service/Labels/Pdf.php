<?php

namespace GeoKrety\Service\Labels;

use GeoKrety\Model\Geokret;

class Pdf extends \TCPDF {
    use Traits\Languages;

    public const LABEL_OUTPUT_DPI = 300;

    private array $geokrety = [];

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false) {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);

        // set document information
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor('GeoKrety.org website');
        $this->SetTitle('GeoKrety label - https://geokrety.org');
        $this->SetSubject('GeoKrety label');
        $this->SetKeywords('GeoKrety, PDF, label');

        // set default header data
        $this->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'GeoKrety label', "by Kumy\nhttps://geokrety.org", [0, 64, 255], [0, 64, 128]);
        $this->setFooterData([0, 64, 0], [0, 64, 128]);

        // set header and footer fonts
        $this->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
        $this->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);

        // set default monospaced font
        $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $this->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $this->SetAutoPageBreak(false, 10);

        // set image scale factor
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
        //        $this->setRasterizeVectorImages(true);

        // set default font subsetting mode
        $this->setFontSubsetting(true);

        // Set font
        // dejavusans is a UTF-8 Unicode font, if you only need to
        // print standard ASCII chars, you can use core fonts like
        // helvetica or times to reduce file size.
        $this->SetFont('dejavusans', '', 14, '', true);

        // Add a page
        // This method has several options, check the source code documentation for more information.
        $this->AddPage();
    }

    // Page header
    public function Header() {        // Set font
        $this->SetY(8);
        $this->SetFont('helvetica', 'B', 16);
        // Title
        $this->Cell(0, 15, 'GeoKrety Labels', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        //        // Position at 15 mm from bottom
        //        $this->SetY(5);
        //        // Set font
        //        $this->SetFont('helvetica', 'I', 8);
        //        // Page number
        //        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');

        //        // Logo
        //        $image_file = 'https://cdn.geokrety.org/images/banners/logo-puste.png';
        //        $this->Image($image_file, $this->original_lMargin, 5, 45, '', 'PNG', '', 'M', false, 300, '', false, false, 0, false, false, false);

        //        $this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => '#f00'));
        //        $this->SetY(15);
        //        $this->SetX($this->original_lMargin);
        //        $this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, '', 'T', 0, 'C');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }

    public function addGeokrety(Geokret ...$geokrety) {
        $this->geokrety = array_merge($this->geokrety, $geokrety);
    }

    public function render() {
        $startLeft = $posX = PDF_MARGIN_LEFT;
        $startTop = $posY = 13;
        $image = new Image();
        $image->setLanguages($this->languages);

        $imgPrevH = 0;
        for ($i = 0; $i < sizeof($this->geokrety); ++$i) {
            $labelPNGData = $image->png($this->geokrety[$i]);

            // px to mm
            $imageSize = getimagesizefromstring($labelPNGData);
            $imgW = $imageSize[0] * 25.4 / self::LABEL_OUTPUT_DPI;
            $imgH = $imageSize[1] * 25.4 / self::LABEL_OUTPUT_DPI;

            if ($posX + $imgW > $this->getPageWidth() - $startLeft) {
                $posX = $startLeft;
                $posY += $imgPrevH;
            }

            if ($posY + $imgH > $this->getPageHeight() - $startTop) {
                $posY = $startTop;
                $this->AddPage();
            }

            //            $this->ImageSVG($file='@'.$labelSVGData, $x=PDF_MARGIN_LEFT, $y=$pos, $w=$imgW, $h=$imgH, $link='', $align='', $palign='', $border=1, $fitonpage=false);
            $this->Image('@'.$labelPNGData, $x = $posX, $y = $posY, $w = $imgW, $h = $imgH, 'PNG', $link = '', '', true, self::LABEL_OUTPUT_DPI, '', false, false, 1, false, false, false);

            $posX += $imgW;
            $imgPrevH = max($imgH, $imgPrevH);
        }

        // ---------------------------------------------------------

        // Close and output PDF document
        // This method has several options, check the source code documentation for more information.
        $filename = 'geokrety-labels.pdf';
        if (sizeof($this->geokrety) === 1) {
            $filename = sprintf('%s-label.pdf', $this->geokrety[0]->gkid);
        }
        $this->Output($filename, 'I');
    }
}
