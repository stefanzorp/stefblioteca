<?php
include "includes/header.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'libs/PHPMailer/Exception.php';
require 'libs/PHPMailer/PHPMailer.php';
require 'libs/PHPMailer/SMTP.php';

$mesaj_status = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'bibioteca16@gmail.com'; 
        $mail->Password   = 'boyk ezpl bdam gmwk'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('bibioteca16@gmail.com', 'Stefblioteca Support');
        $mail->addAddress('carticica7@gmail.com'); 

        $mail->isHTML(true);
        $mail->Subject = 'Mesaj nou: ' . $_POST['subiect'];
        $mail->Body    = "<h3>Mesaj nou de la: " . htmlspecialchars($_POST['nume']) . "</h3>" .
                         "<p>Email: " . htmlspecialchars($_POST['email']) . "</p>" .
                         "<p>Mesaj: " . nl2br(htmlspecialchars($_POST['mesaj'])) . "</p>";

        $mail->send();
        $mesaj_status = '<div class="alert alert-success">✅ Mesajul a fost trimis cu succes!</div>';
    } catch (Exception $e) {
        $mesaj_status = '<div class="alert alert-danger">❌ Eroare la trimitere: ' . $mail->ErrorInfo . '</div>';
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h3 class="mb-0">📧 Contact</h3>
            </div>
            <div class="card-body p-4">
                
                <?php echo $mesaj_status; ?>

                <p class="text-muted text-center mb-4">Ai o întrebare? Trimite-ne un mesaj și îți vom răspunde în cel mai scurt timp.</p>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Numele tău</label>
                        <input type="text" name="nume" class="form-control" placeholder="Ex: Popescu Ion" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email-ul tău</label>
                        <input type="email" name="email" class="form-control" placeholder="nume@exemplu.com" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Subiect</label>
                        <input type="text" name="subiect" class="form-control" placeholder="Despre ce este vorba?" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Mesajul tău</label>
                        <textarea name="mesaj" class="form-control" rows="4" placeholder="Scrie aici mesajul..." required></textarea>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Trimite Email</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center bg-light">
                <a href="index.php" class="text-decoration-none text-secondary small">← Înapoi la pagina principală</a>
            </div>
        </div>
    </div>
</div>

<?php 
include "includes/footer.php"; 
?>