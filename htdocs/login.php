<?php
session_start();
include "includes/config.php";
include "includes/header.php";

$eroare = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificare CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Eroare de securitate.");
    }

    // reCAPTCHA
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

                    echo "<script>window.location.href='index.php';</script>";
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

<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow border-0">
            <div class="card-body p-4 p-md-5">
                <h2 class="text-center mb-4 text-dark">Autentificare</h2>

                <?php if ($eroare != ""): ?>
                    <div class="alert alert-danger text-center"><?php echo $eroare; ?></div>
                <?php endif; ?>

                <?php if (isset($_GET['msg'])): ?>
                    <div class="alert alert-success text-center"><?php echo htmlspecialchars($_GET['msg']); ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" required placeholder="exemplu@mail.com">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Parolă</label>
                        <input type="password" name="parola" class="form-control" required placeholder="••••••••">
                    </div>

                    <div class="d-flex justify-content-center mb-4">
                        <div class="g-recaptcha" data-sitekey="6LcV3zMsAAAAAN3yCpnTPrSDwDU6oH-_jsNoFXd4"></div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold">Intră în cont</button>
                    </div>
                </form>

                <div class="mt-4 text-center">
                    <p class="mb-1 text-muted">Nu ai cont? <a href="register.php" class="text-decoration-none fw-bold">Înregistrează-te acum</a></p>
                    <a href="index.php" class="text-decoration-none text-secondary small">← Pagina principală</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include "includes/footer.php"; 
?>