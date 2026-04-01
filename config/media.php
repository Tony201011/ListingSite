<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Upload Disk
    |--------------------------------------------------------------------------
    |
    | Disk used for write/delete operations for user-managed media.
    |
    */
    'upload_disk' => env('MEDIA_UPLOAD_DISK', env('FILESYSTEM_CLOUD', env('FILESYSTEM_DISK', 'public'))),

    /*
    |--------------------------------------------------------------------------
    | Delivery Disk
    |--------------------------------------------------------------------------
    |
    | Disk used for generating publicly accessible media URLs.
    |
    */
    'delivery_disk' => env('MEDIA_DELIVERY_DISK', env('MEDIA_UPLOAD_DISK', env('FILESYSTEM_CLOUD', env('FILESYSTEM_DISK', 'public')))),

    /*
    |--------------------------------------------------------------------------
    | Avatar Disk
    |--------------------------------------------------------------------------
    |
    | Disk used for generating user avatar URLs.
    |
    */
    'avatar_disk' => env('AVATAR_DISK', env('MEDIA_DELIVERY_DISK', env('FILESYSTEM_DISK', 'public'))),
];
