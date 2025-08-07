<?php

return [
    'views_path' => env('LOCALE_GENERATOR_VIEWS_PATH', resource_path('views')),
    'google_translate' => [
        'key_file' => env('GOOGLE_TRANSLATE_KEY_FILE', storage_path('app/google-credentials.json')),
        'project_id' => env('GOOGLE_PROJECT_ID'),
    ],
    'deepl_api_key' => env('DEEPL_API_KEY'),
];
