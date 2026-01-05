<?php
include "../includes/config.php";
include "../includes/header.php";

$id_carte = intval($_GET['id_carte'] ?? 0);
if ($id_carte <= 0) {
    die("<div class='alert alert-danger m-5'>Carte invalidă.</div>");
}

$mesaj_form = "";

// Procesare formular
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    
    // Verificare CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Eroare de securitate: Cerere invalidă.");
    }

    // reCAPTCHA v2 (Verificare Server)
    $secret_key = 'CHEIA_TA_SECRETA_REALA'; 
    $response = $_POST['g-recaptcha-response'] ?? '';
    
    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret_key}&response={$response}");
    $captcha_success = json_decode($verify);

    if (!$captcha_success->success) {
        $mesaj_form = "<div class='alert alert-warning shadow-sm'>⚠️ Te rugăm să bifezi căsuța 'Nu sunt robot'.</div>";
    } else {
        $rating = intval($_POST['rating']);
        $comentariu = trim($_POST['comentariu']); 
        $id_utilizator = intval(str_replace("id_", "", $_SESSION['user_id']));

        $stmt = $conn->prepare("INSERT INTO recenzii (id_utilizator, id_carte, rating, comentariu, data_recenzie) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("iiis", $id_utilizator, $id_carte, $rating, $comentariu);
        
        if ($stmt->execute()) {
            $mesaj_form = "<div class='alert alert-success shadow-sm'>✨ Recenzie adăugată cu succes!</div>";
        } else {
            $mesaj_form = "<div class='alert alert-danger shadow-sm'>❌ Eroare la salvarea recenziei.</div>";
        }
    }
}

// Preluare date carte
$stmt_c = $conn->prepare("SELECT titlu FROM carti WHERE id_carte = ?");
$stmt_c->bind_param("i", $id_carte);
$stmt_c->execute();
$carte = $stmt_c->get_result()->fetch_assoc();

// Preluare recenzii existente
$stmt_r = $conn->prepare("SELECT r.*, u.nume FROM recenzii r JOIN utilizatori u ON r.id_utilizator = u.id_utilizator WHERE r.id_carte = ? ORDER BY r.data_recenzie DESC");
$stmt_r->bind_param("i", $id_carte);
$stmt_r->execute();
$recenzii = $stmt_r->get_result();
?>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="carti_list.php">Bibliotecă</a></li>
                    <li class="breadcrumb-item active text-muted"><?php echo htmlspecialchars($carte['titlu']); ?></li>
                </ol>
            </nav>
            <div class="p-4 bg-dark text-white rounded-3 shadow">
                <h1 class="h3 mb-1">💬 Recenzii: <?php echo htmlspecialchars($carte['titlu']); ?></h1>
                <p class="mb-0 opacity-75">Vezi ce spun ceilalți cititori sau lasă propria ta părere.</p>
            </div>
        </div>
    </div>

    <?php echo $mesaj_form; ?>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-body p-4">
                    <h5 class="card-title fw-bold mb-3">Scrie o recenzie</h5>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Nota ta</label>
                                <select name="rating" class="form-select border-primary-subtle shadow-sm" required>
                                    <option value="5">5 ★★★★★ (Excelent)</option>
                                    <option value="4">4 ★★★★☆ (Foarte Bun)</option>
                                    <option value="3">3 ★★★☆☆ (Bun)</option>
                                    <option value="2">2 ★★☆☆☆ (Slab)</option>
                                    <option value="1">1 ★☆☆☆☆ (Foarte Slab)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Comentariul tău</label>
                                <textarea name="comentariu" class="form-control border-primary-subtle shadow-sm" rows="5" placeholder="Ce ți-a plăcut la această carte?" required></textarea>
                            </div>

                            <div class="mb-3">
                                <div class="g-recaptcha" data-sitekey="6LcV3zMsAAAAAN3yCpnTPrSDwDU6oH-_jsNoFXd4"></div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm">
                                Postează Recenzia
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="text-center py-4 px-2 bg-light rounded border border-dashed">
                            <p class="small text-muted mb-3">Trebuie să fii autentificat pentru a lăsa o recenzie.</p>
                            <a href="../login.php" class="btn btn-sm btn-outline-primary px-4">Loghează-te</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <h5 class="fw-bold mb-4">Toate comentariile (<?php echo $recenzii->num_rows; ?>)</h5>
            
            <?php if ($recenzii->num_rows > 0): ?>
                <?php while($r = $recenzii->fetch_assoc()): ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="text-warning h5 mb-0">
                                    <?php 
                                        echo str_repeat("★", $r['rating']); 
                                        echo "<span class='text-muted opacity-25'>".str_repeat("★", 5 - $r['rating'])."</span>"; 
                                    ?>
                                </div>
                                <span class="badge bg-light text-muted fw-normal">
                                    <?php echo date("d.m.Y", strtotime($r['data_recenzie'])); ?>
                                </span>
                            </div>
                            
                            <p class="mb-2 text-dark" style="white-space: pre-wrap;"><?php echo htmlspecialchars($r['comentariu']); ?></p>
                            
                            <hr class="opacity-10 my-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                    <?php echo strtoupper(substr($r['nume'], 0, 1)); ?>
                                </div>
                                <span class="small text-muted">Postat de <strong><?php echo htmlspecialchars($r['nume']); ?></strong></span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="display-1 text-muted opacity-25 mb-3"><i class="bi bi-chat-dots"></i></div>
                    <p class="text-muted">Nu există încă recenzii pentru această carte. Fii primul care lasă una!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
include "../includes/footer.php"; 
?>