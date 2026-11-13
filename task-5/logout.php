<?php
declare(strict_types=1);

require_once "includes/bootstrap.php";

$_SESSION = [];
session_destroy();

redirect("login.php");
