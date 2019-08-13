<?php
// измените параметры подключения
$host = "localhost";
$db_name = "fci";
$port = 33061;
$user = "root";
$pass = "";

$pdo = new PDO("mysql:host=$host;dbname=$db_name;port=$port", $user, $pass) or die("Error connection!!!");