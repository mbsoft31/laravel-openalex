<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenAlex API Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for storing the configuration for the OpenAlex API.
    | You can specify the base URL and a contact email, which is good
    | practice for API etiquette.
    |
    */

    'base_url' => env('OPENALEX_BASE_URL', 'https://api.openalex.org'),

    /*
    | A "mailto" address is a courtesy to OpenAlex so they can contact you
    | if your application is causing issues.
    */
    'email' => env('OPENALEX_MAILTO_EMAIL'),
];
