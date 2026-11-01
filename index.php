<?php

header("Content-Type: application/json; charset=utf-8");

echo json_encode([
    "php_version" => PHP_VERSION,
    "pdo" => extension_loaded("PDO"),
    "pdo_pgsql" => extension_loaded("pdo_pgsql"),
    "pgsql" => extension_loaded("pgsql")
]);
