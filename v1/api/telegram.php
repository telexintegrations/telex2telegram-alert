<?php
require_once '../assets/header.php';

use telegramBot\telegramBot;


// Decode JSON into an associative array
$update = json_decode($input, true);

if ($update) {
    // Convert array to object
    $update_Object = json_decode(json_encode($update));

    // Process Request
    telegramBot::handleRequest($update_Object);
}

