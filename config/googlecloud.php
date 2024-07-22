<?php

return [
    'project_id' => env('GOOGLE_CLOUD_PROJECT_ID', 'your-project-id'),
    'key_file' => base_path(env('GOOGLE_CLOUD_KEY_FILE_PATH', 'config/service.json')),
    'storage_bucket' => env('GOOGLE_CLOUD_STORAGE_BUCKET', 'your-bucket-name'),
];
