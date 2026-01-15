<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudinary Configuration
    |--------------------------------------------------------------------------
    |
    | An optional configuration file for the Cloudinary Laravel package.
    |
    */

    'cloud_url' => env('CLOUDINARY_URL'),

    'notification_url' => env('CLOUDINARY_NOTIFICATION_URL'),

    /*
    |--------------------------------------------------------------------------
    | Cloudinary Upload Preset
    |--------------------------------------------------------------------------
    |
    | Upload preset for signed uploads.
    |
    */

    'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET'),

    /*
    |--------------------------------------------------------------------------
    | Cloudinary Upload Route
    |--------------------------------------------------------------------------
    |
    | Route for unsigned uploads.
    |
    */

    'upload_route' => env('CLOUDINARY_UPLOAD_ROUTE'),

    /*
    |--------------------------------------------------------------------------
    | Cloudinary Upload Action
    |--------------------------------------------------------------------------
    |
    | Action for unsigned uploads.
    |
    */

    'upload_action' => env('CLOUDINARY_UPLOAD_ACTION'),

];
