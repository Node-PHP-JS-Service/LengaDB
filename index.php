<?php

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/config/database.php";

try {

    $pdo = getDatabase();

    echo json_encode([
        "status" => "success",
        "database" => "connected"
    ]);

} catch (Throwable $e) {

    // 詳細なエラーはRenderのログに記録
    error_log("Database Error: " . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed"
    ]);
}
