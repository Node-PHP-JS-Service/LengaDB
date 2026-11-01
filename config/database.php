<?php

function getDatabase() {

    $url = parse_url(getenv("DATABASE_URL"));

    $host = $url["host"];
    $port = $url["port"] ?? 5432;
    $dbname = ltrim($url["path"], "/");

    $user = urldecode($url["user"]);
    $password = urldecode($url["pass"]);

    return new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );
}
