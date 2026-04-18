<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Master Barcode
    |--------------------------------------------------------------------------
    | Scanning this special barcode opens the manual / custom product entry
    | modal in the POS instead of performing a catalogue lookup.
    |
    | Override in .env:  MASTER_BARCODE=9999999999999
    |
    */
    'master_barcode' => env('MASTER_BARCODE', '9999999999999'),

];
