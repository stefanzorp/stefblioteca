<?php
$servername = "sqlXXX.infinityfree.com"; // host MySQL de la InfinityFree
$username = "if0_40376414";
$password = "zarzavat10";
$database = "if0_40376414_biblioteca";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Eroare la conectare: " . $conn->connect_error);
}
?>
