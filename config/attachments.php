<?php

return [
    'max_kb' => env('ATTACHMENTS_MAX_KB', 10240), // 10MB
    'allowed_mimes' => explode(',', env('ATTACHMENTS_ALLOWED_MIMES',
        'image/jpeg,image/png,image/webp,application/pdf,text/plain'
    )),
];
