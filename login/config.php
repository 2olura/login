<?php
session_start();

$host = 'localhost';
$dbname = 'login_system';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed");
}

function clean($data) {
    return htmlspecialchars(trim($data));
}

function validPhone($phone) {
    if(strlen($phone) != 9) return false;
    if(!ctype_digit($phone)) return false;
    $prefix = substr($phone, 0, 2);
    $allowed = ['70', '71', '73', '77', '78'];
    return in_array($prefix, $allowed);
}
