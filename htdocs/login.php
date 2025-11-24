<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "includes/config.php";

$eroare = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"] ?? "");
    $parola = trim($_POST["parola"] ?? "");

    $sql = "SELECT * FROM utilizatori WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $rez = $stmt->get_result();

    if ($rez->num_rows === 1) {
        $user = $rez->fetch_assoc();

        if (password_verify($parola, $user["parola"])) {
            
            // **CORECTAT: Folosește cheia "id_utilizator"**
            // Păstrăm prefixul "id_" pentru a ocoli problema serverului de hosting
            $_SESSION["user_id"] = "id_" . $user["id_utilizator"]; 

            $_SESSION["user_name"] = $user["nume"];
            $_SESSION["user_rol"] = $user["rol"]; 

            header("Location: index.php");
            exit;
        } else {
            $eroare = "Parolă incorectă!";
        }
    } else {
        $eroare = "Email inexistent!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
<h2>Autentificare</h2>

<?php if ($eroare != "") echo "<p style='color:red;'>$eroare</p>"; ?>

<form method="POST">
    Email:<br>
    <input type="email" name="email" required><br><br>

    Parola:<br>
    <input type="password" name="parola" required><br><br>

    <button type="submit">Autentificare</button>
</form>

<p><a href="register.php">Nu ai cont? Înregistrează-te</a></p>
</body>
</html>
