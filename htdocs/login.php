<?php
session_start();
include "includes/config.php";

$eroare = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //verific CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Eroare de securitate.");
    }

    //recaptcha
    $secret_key = '6LcV3zMsAAAAAGLN0yaJE6YWO3Dk_V3zA8Gd29MF'; 
    $response = $_POST['g-recaptcha-response'] ?? '';
    
    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret_key}&response={$response}");
    $captcha_success = json_decode($verify);

    if (!$captcha_success->success) {
        $eroare = "Confirmă că nu ești robot!";
    } else {
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
                
                if ($user["status"] === "inactiv") {
                    $eroare = "Contul tău a fost suspendat. Contactează administratorul.";
                } else {

                    $_SESSION["user_id"] = "id_" . $user["id_utilizator"]; 
                    $_SESSION["user_name"] = $user["nume"];
                    $_SESSION["user_rol"] = $user["rol"]; 

                    header("Location: index.php");
                    exit;
                }
            } else {
                $eroare = "Parolă incorectă!";
            }
        } else {
            $eroare = "Email inexistent!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Stefblioteca</title>
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

        .login-card {
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
            margin-bottom: 30px;
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
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #34495e;
            font-weight: 500;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 1em;
        }

        input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.2);
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #2c3e50;
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
            background-color: #34495e;
        }

        .footer-links {
            text-align: center;
            margin-top: 25px;
            font-size: 0.9em;
        }

        .footer-links a {
            color: #3498db;
            text-decoration: none;
        }

        .g-recaptcha {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <h2>Autentificare</h2>

    <?php if ($eroare != ""): ?>
        <div class="error-msg"><?php echo $eroare; ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required placeholder="exemplu@mail.com">
        </div>

        <div class="form-group">
            <label>Parolă</label>
            <input type="password" name="parola" required placeholder="••••••••">
        </div>

        <div class="g-recaptcha" data-sitekey="6LcV3zMsAAAAAN3yCpnTPrSDwDU6oH-_jsNoFXd4"></div>

        <button type="submit">Intră în cont</button>
    </form>

    <div class="footer-links">
        <p>Nu ai cont? <a href="register.php">Înregistrează-te acum</a></p>
        <p><a href="index.php">← Pagina principală</a></p>
    </div>
</div>

</body>
</html>