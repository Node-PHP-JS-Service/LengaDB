```php
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

// Authorizationヘッダー取得
$headers = getallheaders();

$authorization = $headers["Authorization"]
    ?? $headers["authorization"]
    ?? "";

if (!preg_match('/^Bearer ([a-f0-9]{64})$/i', $authorization, $matches)) {
    response(401, "INVALID_TOKEN", "Token is required");
}

$token = $matches[1];

// DB接続
try {
    require_once __DIR__ . "/../config/database.php";

    $pdo = getDatabase();

    // 期限切れトークンを削除
    $deleteStmt = $pdo->prepare("
        DELETE FROM logintoken
        WHERE expires_at <= CURRENT_TIMESTAMP
    ");

    $deleteStmt->execute();

    // トークンのハッシュを計算
    $tokenHash = hash("sha256", $token);

    // トークン検索
    $stmt = $pdo->prepare("
        SELECT
            logintoken.account_id,
            users.name,
            users.description,
            users.created_at
        FROM logintoken
        INNER JOIN users
            ON users.id = logintoken.account_id
        WHERE logintoken.token_hash = :token_hash
          AND logintoken.expires_at > CURRENT_TIMESTAMP
        LIMIT 1
    ");

    $stmt->execute([
        ":token_hash" => $tokenHash
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        response(401, "INVALID_TOKEN", "Invalid or expired token");
    }

    // 認証成功
    response(200, "TOKEN_VALID", "Token is valid", [
        "user" => $user
    ]);

} catch (Throwable $e) {

    error_log("Token Verify Error: " . $e->getMessage());

    response(500, "INTERNAL_ERROR", "Internal server error");
}
```
