<?php

header("Content-Type: application/json; charset=utf-8");

echo json_encode([
    "status" => "success",
    "message" => "Unity Game API is running!"
], JSON_UNESCAPED_UNICODE);
