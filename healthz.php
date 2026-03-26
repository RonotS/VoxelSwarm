<?php
// Simple health check - bypasses the application entirely
echo json_encode([
    'status' => 'ok',
    'php' => phpversion(),
    'extensions' => get_loaded_extensions(),
    'cwd' => getcwd(),
    'storage_exists' => is_dir('/app/storage'),
    'vendor_exists' => file_exists('/app/vendor/autoload.php'),
    'env_exists' => file_exists('/app/.env'),
    'db_exists' => file_exists('/app/storage/swarm.db'),
    'version_exists' => file_exists('/app/VERSION'),
    'index_exists' => file_exists('/app/index.php'),
    'bootstrap_exists' => file_exists('/app/src/bootstrap.php'),
    'data_dir' => is_dir('/data'),
    'storage_logs_link' => is_link('/app/storage/logs'),
    'storage_instances_link' => is_link('/app/storage/instances'),
]);
