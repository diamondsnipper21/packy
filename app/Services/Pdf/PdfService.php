<?php

namespace App\Services\Pdf;

use Dompdf\Dompdf;
use Illuminate\Support\Facades\View;

class PdfService
{
    private Dompdf $dompdf;

    protected string $html;
    protected string $view;
    protected array $data;
    protected string $paperSize = 'A4';
    protected string $orientation = 'portrait';

    public function __construct()
    {
        $this->dompdf = new Dompdf(['enable_remote' => true]);
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return void
     */
    public function steam(): void
    {
        try {
            $this->dompdf->loadHtml(View::make($this->view, $this->data));
            $this->setOptions();

            // Render the HTML as PDF
            $this->dompdf->render();

            // Output the generated PDF to Browser
            $this->dompdf->stream();
        } catch (\Exception $e) {
            \Log::error("PDF generation failed: {$e->getMessage()}");
        }
    }

    /**
     * (Optional) Setup the paper size and orientation
     * @return void
     */
    private function setOptions(): void
    {
        $this->dompdf->setPaper(
            size: $this->paperSize,
            orientation: $this->orientation
        );
    }
}