<?php

namespace App\Services\Pdf\Files;

use App\Services\Pdf\PdfService;

class PdfFileInvoice extends PdfService
{
    public function __construct()
    {
        parent::__construct();
        $this->view = 'pdf.invoice';
    }

    /**
     * for testing purpose
     * @return void
     */
    public function setDummyData(): void
    {
        $this->data = [
            'number' => 'IN-2025-001',
            'date' => '02.14.2025',
            'period_start' => '01.01.2025',
            'period_end' => '01.31.2025',
            'customer' => [
                'business_name' => 'Connor McGregor LTD',
                'address' => '4 Father Griffin Place',
                'zipcode' => 'H91 FVF8',
                'city' => 'Galway',
                'country' => 'Ireland',
                'vat_number' => 'IE123456789'
            ],
            'products' => [
                [
                    'name' => 'Digital services licensed to Packie for resale',
                    'quantity' => 1,
                    'unit_price' => 100,
                    'total' => 100
                ]
            ],
            'currency' => '€',
            'total_vat_excl' => 100,
            'vat' => 23,
            'total_vat_incl' => 123,
        ];
    }

}