<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    ini_set("session.use_strict_mode", "1");

    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/",
        "domain" => "",
        "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
        "httponly" => true,
        "samesite" => "Lax",
    ]);

    session_start();
}

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/security.php";
