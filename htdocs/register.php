<?php
session_start();
include "includes/config.php";

$eroare = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //verific CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Eroare de securitate: Cerere invalidă.");
    }

    //recaptcha
    $secret_key = '6LcV3zMsAAAAAGLN0yaJE6YWO3Dk_V3zA8Gd29MF'; 
    $response = $_POST['g-recaptcha-response'] ?? '';
    
    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret_key}&response={$response}");
    $captcha_success = json_decode($verify);

    if (!$captcha_success->success) {
        $eroare = "Te rugăm să bifezi căsuța 'Nu sunt robot'.";
    } else {

        $nume = trim($_POST["nume"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $parola = trim($_POST["parola"] ?? "");

        $check = $conn->prepare("SELECT id_utilizator FROM utilizatori WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $rez_check = $check->get_result();

        if ($rez_check->num_rows > 0) {
            $eroare = "Acest email este deja asociat unui cont!";
        } else {
            $hash = password_hash($parola, PASSWORD_DEFAULT);
            $rol = "cititor";
            $status = "activ";

            $sql = $conn->prepare("INSERT INTO utilizatori (nume, email, parola, rol, status) VALUES (?, ?, ?, ?, ?)");
            $sql->bind_param("sssss", $nume, $email, $hash, $rol, $status);
            
            if ($sql->execute()) {
                header("Location: login.php?msg=Cont creat cu succes!");
                exit;
            } else {
                $eroare = "A apărut o eroare la înregistrare.";
            }
        }
    } 
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Înregistrare - Stefblioteca</title>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f8;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .register-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 25px;
        }

        .error-msg {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.9em;
            border: 1px solid #f5c6cb;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #34495e;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 1em;
        }

        input:focus {
            outline: none;
            border-color: #2ecc71;
            box-shadow: 0 0 5px rgba(46, 204, 113, 0.2);
        }

        .g-recaptcha {
            margin: 20px 0;
            display: flex;
            justify-content: center;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #2ecc71;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }

        button:hover {
            background-color: #27ae60;
        }

        .footer-links {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9em;
        }

        .footer-links a {
            color: #3498db;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="register-card">
    <h2>Creare cont</h2>

    <?php if ($eroare != ""): ?>
        <div class="error-msg"><?php echo $eroare; ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <div class="form-group">
            <label>Nume complet</label>
            <input type="text" name="nume" required placeholder="Ex: Popescu Ion">
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required placeholder="nume@email.com">
        </div>

        <div class="form-group">
            <label>Parolă</label>
            <input type="password" name="parola" required placeholder="Minim 6 caractere">
        </div>

        <div class="g-recaptcha" data-sitekey="6LcV3zMsAAAAAN3yCpnTPrSDwDU6oH-_jsNoFXd4"></div>

        <button type="submit">Înregistrare</button>
    </form>

    <div class="footer-links">
        <p>Ai deja cont? <a href="login.php">Loghează-te aici</a></p>
        <p><a href="index.php" style="color: #7f8c8d;">← Înapoi</a></p>
    </div>
</div>

</body>
</html>