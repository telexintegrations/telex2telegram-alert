<?php
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// If the request is an OPTIONS request, respond with a 200 status to pass the preflight check
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    exit();
}

// CORS Policy
$allowed_origins = [
    "https://telex.im",
    "https://telegram.org"// Fix later
];
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}


require_once('connect.php');
$input = file_get_contents("php://input");
