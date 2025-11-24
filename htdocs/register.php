<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "includes/config.php";

$eroare = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nume = $_POST["nume"] ?? "";
    $email = $_POST["email"] ?? "";
    $parola = $_POST["parola"] ?? "";

    $check = $conn->prepare("SELECT * FROM utilizatori WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $rez_check = $check->get_result();

    if ($rez_check->num_rows > 0) {
        $eroare = "Email-ul este deja folosit!";
    } else {
        $hash = password_hash($parola, PASSWORD_DEFAULT);

        $sql = $conn->prepare("INSERT INTO utilizatori (nume,email,parola,rol,status) VALUES (?,?,?,?,?)");
        $rol = "cititor";
        $status = "activ";
        $sql->bind_param("sssss", $nume, $email, $hash, $rol, $status);
        $sql->execute();

        header("Location: login.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Înregistrare</title>
</head>
<body>

<h2>Creare cont</h2>

<?php if ($eroare != "") echo "<p style='color:red;'>$eroare</p>"; ?>

<form method="POST">
    Nume:<br>
    <input type="text" name="nume" required><br><br>

    Email:<br>
    <input type="email" name="email" required><br><br>

    Parola:<br>
    <input type="password" name="parola" required><br><br>

    <button type="submit">Creează cont</button>
</form>

<p><a href="login.php">Ai deja cont? Loghează-te</a></p>

</body>
</html>
