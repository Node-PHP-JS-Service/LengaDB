
<?php

header("Content-Type: application/json; charset=utf-8");

// JSONレスポンス
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

// JSONを受信
$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (!is_array($data)) {
    response(400, "INVALID_JSON", "Invalid JSON format");
}

// 必須情報
$name = $data["name"] ?? "";
$password = $data["password"] ?? "";
$description = $data["description"] ?? null;

// 入力チェック
if (!is_string($name) || trim($name) === "") {
    response(400, "INVALID_NAME", "Name is required");
}

if (mb_strlen($name, "UTF-8") > 100) {
    response(400, "NAME_TOO_LONG", "Name must be 100 characters or less");
}

if (!is_string($password) || $password === "") {
    response(400, "INVALID_PASSWORD", "Password is required");
}

if (strlen($password) < 12 || strlen($password) > 72) {
    response(400, "INVALID_PASSWORD_LENGTH",
        "Password must be between 12 and 72 bytes");
}

if ($description !== null && !is_string($description)) {
    response(400, "INVALID_DESCRIPTION", "Description must be a string");
}

if ($description !== null && mb_strlen($description, "UTF-8") > 2000) {
    response(400, "DESCRIPTION_TOO_LONG", "Description is too long");
}

// DB接続・登録
try {
    require_once __DIR__ . "/../config/database.php";

    $pdo = getDatabase();

    // パスワードをハッシュ化
    $hash = password_hash($password, PASSWORD_DEFAULT);

    if ($hash === false) {
        throw new RuntimeException("Password hashing failed");
    }

    $stmt = $pdo->prepare("
        INSERT INTO users (name, password_hash, description)
        VALUES (:name, :password_hash, :description)
        RETURNING id, name, description, created_at
    ");

    $stmt->execute([
        ":name" => trim($name),
        ":password_hash" => $hash,
        ":description" => $description
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    response(201, "USER_CREATED", "User registered successfully", $user);

} catch (PDOException $e) {

    error_log("Registration DB Error: " . $e->getMessage());

    // PostgreSQLのUNIQUE制約違反
    if ($e->getCode() === "23505") {
        response(409, "NAME_ALREADY_EXISTS", "This name is already registered");
    }

    response(500, "DATABASE_ERROR", "Database operation failed");

} catch (Throwable $e) {

    error_log("Registration Error: " . $e->getMessage());

    response(500, "INTERNAL_ERROR", "Internal server error");
}
