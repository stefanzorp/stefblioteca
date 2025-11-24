<?php
$servername = "sql312.infinityfree.com"; // hostul real de MySQL
$username = "if0_40376414";
$password = "zarzavat10";
$database = "if0_40376414_biblioteca";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Eroare la conectare: " . $conn->connect_error);
}
?>
