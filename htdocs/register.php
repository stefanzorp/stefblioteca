<?php
session_start();
include "includes/config.php";
include "includes/header.php";

$eroare = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificare CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Eroare de securitate: Cerere invalidă.");
    }

    // reCAPTCHA
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
                // Redirecționare cu mesaj de succes
                echo "<script>window.location.href='login.php?msg=Cont creat cu succes!';</script>";
                exit;
            } else {
                $eroare = "A apărut o eroare la înregistrare.";
            }
        }
    } 
}
?>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow border-0">
            <div class="card-body p-4 p-md-5">
                <h2 class="text-center mb-4 text-primary">Creare cont</h2>

                <?php if ($eroare != ""): ?>
                    <div class="alert alert-danger text-center py-2"><?php echo $eroare; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Nume complet</label>
                        <input type="text" name="nume" class="form-control form-control-lg" required placeholder="Ex: Popescu Ion">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Email</label>
                        <input type="email" name="email" class="form-control form-control-lg" required placeholder="nume@email.com">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Parolă</label>
                        <input type="password" name="parola" class="form-control form-control-lg" required placeholder="Minim 6 caractere">
                    </div>

                    <div class="d-flex justify-content-center mb-4">
                        <div class="g-recaptcha" data-sitekey="6LcV3zMsAAAAAN3yCpnTPrSDwDU6oH-_jsNoFXd4"></div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg fw-bold">Înregistrare</button>
                    </div>
                </form>

                <div class="mt-4 text-center">
                    <p class="mb-1 text-muted">Ai deja cont? <a href="login.php" class="text-decoration-none fw-bold text-primary">Loghează-te aici</a></p>
                    <a href="index.php" class="text-decoration-none text-secondary small">← Înapoi la început</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
include "includes/footer.php"; 
?>