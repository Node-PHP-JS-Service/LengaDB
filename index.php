<?php

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/config/database.php";

try {
    $pdo = getDatabase();

    $result = $pdo->query("SELECT version()")->fetchColumn();

    echo json_encode([
        "status" => "success",
        "database" => "connected",
        "version" => $result
    ]);

} catch (Throwable $e) {
    error_log("Database Error: " . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
