<?php

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/config/database.php";

try {
    $pdo = getDatabase();

    $result = $pdo->query("SELECT version()")->fetch();

    echo json_encode([
        "status" => "success",
        "database" => "connected"
    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed"
    ]);
}
