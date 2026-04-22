<?php

return [

    'base_url' => env('PDFMONKEY_BASE_URL', 'https://api.pdfmonkey.io/api/v1'),

    'api_key' => env('PDFMONKEY_API_KEY'),

    'template_id' => env('PDFMONKEY_TEMPLATE_ID'),

    'timeout' => env('PDFMONKEY_TIMEOUT', 30),

    'connect_timeout' => env('PDFMONKEY_CONNECT_TIMEOUT', 10),

    'default_status' => env('PDFMONKEY_DEFAULT_STATUS', 'pending'),

    'default_filename_prefix' => env('PDFMONKEY_FILENAME_PREFIX', 'spotstay'),

];
