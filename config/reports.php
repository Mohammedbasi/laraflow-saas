<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Weekly Reports Storage Disk
    |--------------------------------------------------------------------------
    |
    | This disk is used to store generated weekly PDF reports.
    | Should be private (not publicly accessible).
    |
    */

    'disk' => env('REPORTS_DISK', 'reports'),

];
