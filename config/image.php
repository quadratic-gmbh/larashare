<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    |
    | Intervention Image supports "GD Library" and "Imagick" to process images
    | internally. You may choose one of them according to your PHP
    | configuration. By default PHP's "GD Library" implementation is used.
    |
    | Supported: "gd", "imagick"
    |
    */

    'driver' => env('IMAGE_DRIVER', 'imagick'),
    'thumb_sizes' => ['50','150','300'],
    'max_size' => env('IMAGE_MAX_SIZE', 20000), // 5 mb default?
];
