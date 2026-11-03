<?php

function res(code, message, status, data = null)
{
    echo json_encode([
        "code" => code,
        "message" => message,
        "status" => status,
        "data" => data
    ]);
}

$input = file_get_contents("php://input");

if($_SERVER["REQUEST_METHOD"] !== "POST")
{
    http_response_code(400);
    res("METHOD_NOT_SUPPORT", "このmethodはサポートされていません。", false);
    exit;
}

if($input === null)
{
    http_response_code(400);
    res("DATA_NULL", "データがありません", false);
    exit;
}

$data = json_decode($input, true);

if ($data === null) {
    http_response_code(400);
    res("INVALID_JSON", "JSON形式が正しくありません", false);
    exit;
}



?>
