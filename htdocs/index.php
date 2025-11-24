<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Biblioteca – Acasă</title>
</head>
<body>
<h1>Bun venit la Stefblioteca</h1>

<?php 

// Verifică dacă utilizatorul este logat (folosind sesiunea setată cu prefixul 'id_')
if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] != ""): 
?>

    <?php $rol = $_SESSION["user_rol"] ?? 'cititor'; // Extragem rolul utilizatorului ?>
    <p>Bun venit, <strong><?php echo htmlspecialchars($_SESSION["user_name"]); ?></strong>! (Rol: <?php echo htmlspecialchars($rol); ?>)</p>
    <p><a href="logout.php">Logout</a></p>
    <h2>Meniu principal</h2>
    <ul>
        
        <li><a href="carti/carti_list.php">Vizualizare Cărți și Recenzii</a></li>

    </ul>

<?php else: ?>

    <p>Nu ești logat.</p>
    <a href="login.php">Login</a> |
    <a href="register.php">Register</a>

<?php endif; ?>
</body>
</html>