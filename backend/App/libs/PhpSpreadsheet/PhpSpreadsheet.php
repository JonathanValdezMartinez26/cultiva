<?php

require __DIR__ . '/vendor/autoload.php';

class PHPSpreadsheet extends \PhpOffice\PhpSpreadsheet
{
    public function __construct($configuracion = null)
    {
        parent::__construct($configuracion);
    }
}