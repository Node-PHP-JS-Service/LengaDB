<?php

header("Content-Type: application/json; charset=utf-8");

function response($status, $code, $message, $data = null) {
    http_response_code($status);

    echo json_encode([
        "status" => $status < 400 ? "success" : "error",
        "code" => $code,
        "message" => $message,
        "data" => $data
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// POST以外は拒否
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Allow: POST");
    response(405, "METHOD_NOT_ALLOWED", "POST only");
}

// JSON受信
$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (!is_array($data)) {
    response(400, "INVALID_JSON", "Invalid JSON format");
}

// 入力取得
$name = $data["name"] ?? "";
$password = $data["password"] ?? "";

// 入力チェック
if (!is_string($name) || trim($name) === "") {
    response(400, "INVALID_NAME", "Name is required");
}

if (!is_string($password) || $password === "") {
    response(400, "INVALID_PASSWORD", "Password is required");
}

try {
    require_once __DIR__ . "/../config/database.php";

    $pdo = getDatabase();

    // ユーザー検索
    $stmt = $pdo->prepare("
        SELECT id, name, password_hash, description, created_at
        FROM users
        WHERE name = :name
        LIMIT 1
    ");

    $stmt->execute([
        ":name" => trim($name)
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ユーザーが存在しない、またはパスワードが違う
    if (!$user || !password_verify($password, $user["password_hash"])) {
        response(401, "INVALID_CREDENTIALS",
            "Invalid name or password");
    }

    // 認証成功
    response(200, "LOGIN_SUCCESS", "Login successful", [
        "id" => $user["id"],
        "name" => $user["name"],
        "description" => $user["description"],
        "created_at" => $user["created_at"]
    ]);

} catch (Throwable $e) {

    error_log("Login Error: " . $e->getMessage());

    response(500, "INTERNAL_ERROR", "Internal server error");
}
