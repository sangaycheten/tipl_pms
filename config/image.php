<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Image Driver
    |--------------------------------------------------------------------------
    |
    | Intervention Image supports "GD Library" and "Imagick" to process images.
    | Supported drivers: Intervention\Image\Drivers\Gd\Driver
    |                    Intervention\Image\Drivers\Imagick\Driver
    |
    */

    'driver' => Intervention\Image\Drivers\Gd\Driver::class,

    'options' => [
        'autoOrientation' => true,
        'decodeAnimation' => true,
        'blendingColor'   => 'ffffff',
    ],

];
