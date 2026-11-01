<?php

header("Content-Type: application/json; charset=utf-8");

error_log("=== PHP index.php START ===");

echo json_encode([
    "status" => "success",
    "message" => "PHP is running"
]);

error_log("=== PHP index.php END ===");
