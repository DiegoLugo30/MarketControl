<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Store Name
    |--------------------------------------------------------------------------
    */
    'name' => env('STORE_NAME', 'Mi Tienda'),

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Number
    |--------------------------------------------------------------------------
    | International format WITHOUT + or spaces: country code + area code + number
    | Argentina example: 5491112345678  (54 + 9 + 11 + 12345678)
    |
    */
    'whatsapp_number' => env('STORE_WHATSAPP', '549XXXXXXXXXX'),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    */
    'currency_symbol' => env('STORE_CURRENCY', '$'),
    'currency_locale' => env('STORE_CURRENCY_LOCALE', 'es-AR'),

];
