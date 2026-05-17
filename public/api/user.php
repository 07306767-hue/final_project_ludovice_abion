<?php
//for backups
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../../vendor/autoload.php';
    require_once __DIR__ . '/../../src/APIs/UserAPI.php';

    $database = App\Helpers\Database::getInstance();
    $userModel = new App\Models\UserModel($database);
    $api = new UserAPI($userModel);
    $api->handleRequest();
} catch (Throwable $e) {
    echo json_encode([
        'valid' => false,
        'available' => false,
        'errors' => [
            'Unable to verify availability right now.',
            $e->getMessage()
        ]
    ]);
}
