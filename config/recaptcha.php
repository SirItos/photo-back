<?php

return [
    'enabled' => env('RECAPTCHA_ENABLED', true),
    'key'     => env('RECAPTCHA_SITE_KEY'),
    'secret'  => env('RECAPTCHA_SECRET_KEY'),
];