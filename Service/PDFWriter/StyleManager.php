<?php

namespace Crealoz\EasyAudit\Service\PDFWriter;

use Zend_Pdf_Color_Rgb;
use Zend_Pdf_Font;
use Zend_Pdf_Style;
use Zend_Pdf_Page;

class StyleManager
{
    /**
     * Sets the general style of the text. It will be black.
     *
     * @param Zend_Pdf_Page $page
     * @param int $size
     * @param float $r
     * @param float $g
     * @param float $b
     * @return void
     * @throws \Zend_Pdf_Exception
     */
    public function setGeneralStyle(Zend_Pdf_Page $page, int $size = 9, float $r = 0, float $g = 0, float $b = 0): void
    {
        $style = new Zend_Pdf_Style();
        $style->setLineColor(new Zend_Pdf_Color_Rgb($r, $g, $b));
        $style->setFillColor(new Zend_Pdf_Color_Rgb($r, $g, $b));
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, $size);
        $page->setStyle($style);
    }

    /**
     * Sets the style of the title. It will be blue.
     *
     * @param Zend_Pdf_Page $page
     * @param int $size
     * @return void
     */
    public function setTitleStyle(Zend_Pdf_Page $page, int $size = 20): void
    {
        $this->setColorStyle($page, $size, 0, 0, 0.85); // Blue
    }

    /**
     * Sets the style of the subtitle. It will be greenish blue.
     *
     * @param Zend_Pdf_Page $page
     * @param int $size
     * @return void
     */
    public function setSubTitleStyle(Zend_Pdf_Page $page, int $size = 12): void
    {
        $this->setColorStyle($page, $size, 0, 0.45, 0.85); // Greenish Blue
    }

    public function setErrorStyle(Zend_Pdf_Page $page, int $size = 11): void
    {
        $this->setColorStyle($page, $size, 0.85, 0, 0); // Red
    }

    public function setWarningStyle(Zend_Pdf_Page $page, int $size = 11): void
    {
        $this->setColorStyle($page, $size, 0.85, 0.45, 0); // Orange
    }

    private function setColorStyle(Zend_Pdf_Page $page, int $size, float $r, float $g, float $b): void
    {
        $style = new Zend_Pdf_Style();
        $style->setLineColor(new Zend_Pdf_Color_Rgb($r, $g, $b));
        $style->setFillColor(new Zend_Pdf_Color_Rgb($r, $g, $b));
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES);
        $style->setFont($font, $size);
        $page->setStyle($style);
    }
}
