<?php

return [
    'default_file_size_limit' => '5120',
    'allowed_file_extensions' => [
        'image' => ['jpeg', 'jpg', 'png', 'gif'],
        'doc' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'],
        'text' => ['txt'],
        'others' => []
    ],
    'chunk_file_upload_path' => public_path('upload/chunks')
];