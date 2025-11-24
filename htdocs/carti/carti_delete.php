<?php
session_start();
include "../includes/config.php";

$rol = $_SESSION["user_rol"] ?? 'cititor';
if (!isset($_SESSION['user_id']) || ($rol !== 'admin' && $rol !== 'bibliotecar')) { 
    die("Nu ai permisiunea de a accesa această pagină.");
}

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("DELETE FROM carti WHERE id_carte=?");
$stmt->bind_param("i",$id);
$stmt->execute();

header("Location: carti_list.php");
exit;
?>
