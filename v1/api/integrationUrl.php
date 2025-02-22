<?php
require '../assets/header.php';

$jsonFile = __DIR__ . '/integration.json';
$jsonData = file_get_contents($jsonFile);

header("Content-Type: application/json");
echo $jsonData;
