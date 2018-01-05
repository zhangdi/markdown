<?php


namespace App\Converters;


class MarkdownToPdfConverter extends Converter
{
    public $markdown;
    public $pageTitle;

    public function __construct()
    {
        $copyFonts = [
            dirname(dirname(__DIR__)) . '/vendor/tecnickcom/tcpdf/fonts/helvetica.php',
        ];

        foreach ($copyFonts as $font) {
            $dest = FONTS_PATH . '/' . pathinfo($font, PATHINFO_FILENAME) . '.' . pathinfo($font, PATHINFO_EXTENSION);
            if (!file_exists($dest)) {
                copy($font, $dest);
            }
        }

        \TCPDF_FONTS::addTTFfont(dirname(dirname(__DIR__)) . '/fonts/DroidSansFallback.ttf', 'TrueTypeUnicode');
    }

    /**
     * @inheritDoc
     */
    public function convert($markdown)
    {
        $htmlConverter = new MarkdownToHtmlConvert();
        $htmlConverter->theme = 'default';
        $htmlConverter->pageTitle = $this->pageTitle;
        $html = $htmlConverter->convert($markdown);

        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator("github.com/zhangdi/markdown");

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        $pdf->AddPage();
        $pdf->setPageMark();

        $pdf->SetFont('droidsansfallback', '', 13);

        $pdf->writeHTML($html);
        $pdf->lastPage();
        return $pdf->Output($this->pageTitle . '.pdf', 'S');
    }

}
