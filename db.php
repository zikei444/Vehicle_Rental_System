<?php

header("Content-Type: application/json"); // only api will interact with db so everthing return is jsons

$host = "localhost";
$db   = "vehicle_rental_system";   // DB NAME
$user = "root";      
$pass = "";      // takde password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode([
        "status" => "error",
        "message" => "Connection failed: " . $e->getMessage()
    ]));
}