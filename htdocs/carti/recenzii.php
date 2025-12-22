<?php
session_start(); 
include "../includes/config.php";

$id_carte = intval($_GET['id_carte'] ?? 0);
if ($id_carte <= 0) die("Carte invalidă.");

$mesaj_form = "";

// procesare formular
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    
    // verific CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Eroare de securitate: Cerere invalidă.");
    }

    // recaptcha
    $secret_key = '6LcV3zMsAAAAAGLN0yaJE6YWO3Dk_V3zA8Gd29MF'; 
    $response = $_POST['g-recaptcha-response'] ?? '';
    
    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret_key}&response={$response}");
    $captcha_success = json_decode($verify);

    if (!$captcha_success->success) {
        $mesaj_form = "<div class='alert danger'>Te rugăm să bifezi căsuța 'Nu sunt robot'.</div>";
    } else {
        //salvez recenzia
        $rating = intval($_POST['rating']);
        $comentariu = trim($_POST['comentariu']); 
        $id_utilizator = str_replace("id_", "", $_SESSION['user_id']);

        $stmt = $conn->prepare("INSERT INTO recenzii (id_utilizator, id_carte, rating, comentariu, data_recenzie) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("iiis", $id_utilizator, $id_carte, $rating, $comentariu);
        
        if ($stmt->execute()) {
            $mesaj_form = "<div class='alert success'>Recenzie adăugată cu succes!</div>";
        } else {
            $mesaj_form = "<div class='alert danger'>Eroare la salvarea recenziei.</div>";
        }
    }
}

// preluare date
$stmt_c = $conn->prepare("SELECT titlu FROM carti WHERE id_carte = ?");
$stmt_c->bind_param("i", $id_carte);
$stmt_c->execute();
$carte = $stmt_c->get_result()->fetch_assoc();

$stmt_r = $conn->prepare("SELECT r.*, u.nume FROM recenzii r JOIN utilizatori u ON r.id_utilizator = u.id_utilizator WHERE r.id_carte = ? ORDER BY r.data_recenzie DESC");
$stmt_r->bind_param("i", $id_carte);
$stmt_r->execute();
$recenzii = $stmt_r->get_result();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recenzii - <?php echo htmlspecialchars($carte['titlu']); ?></title>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f7f8; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: auto; }
        .header-box { background: #2c3e50; color: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; position: relative; }
        .back-link { color: #ecf0f1; text-decoration: none; font-size: 0.9em; }
        
        .recenzie-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 15px; }
        .rating-stars { color: #f1c40f; font-size: 1.2em; font-weight: bold; }
        .user-meta { color: #7f8c8d; font-size: 0.85em; margin-bottom: 10px; }
        .comment-text { line-height: 1.5; color: #34495e; }

        .form-card { background: #fff; padding: 25px; border-radius: 12px; border-left: 5px solid #3498db; margin-bottom: 30px; }
        textarea { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; font-family: inherit; box-sizing: border-box; margin-bottom: 15px; }
        
        
        .captcha-container { margin: 15px 0; }
        
        button { background: #3498db; color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; font-size: 1.1em; }
        button:hover { background: #2980b9; }

        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; text-align: center; }
        .success { background: #d4edda; color: #155724; }
        .danger { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-box">
        <a href="carti_list.php" class="back-link">← Înapoi la listă</a>
        <h1><?php echo htmlspecialchars($carte['titlu']); ?></h1>
        <p>Secțiunea de comentarii și note</p>
    </div>

    <?php echo $mesaj_form; ?>

    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="form-card">
            <h3 style="margin-top:0;">Lasă părerea ta</h3>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <label>Nota ta:</label><br>
                <select name="rating" required style="padding:10px; border-radius:8px; border:1px solid #ddd; margin-bottom:15px; width:100%;">
                    <option value="5">5 ★★★★★ (Excelent)</option>
                    <option value="4">4 ★★★★☆ (Foarte Bun)</option>
                    <option value="3">3 ★★★☆☆ (Bun)</option>
                    <option value="2">2 ★★☆☆☆ (Slab)</option>
                    <option value="1">1 ★☆☆☆☆ (Foarte Slab)</option>
                </select>

                <textarea name="comentariu" rows="4" placeholder="Ce ți-a plăcut la această carte?" required></textarea>

                <div class="captcha-container">
                    <div class="g-recaptcha" data-sitekey="6LcV3zMsAAAAAN3yCpnTPrSDwDU6oH-_jsNoFXd4"></div>
                </div>

                <button type="submit">Postează Recenzia</button>
            </form>
        </div>
    <?php else: ?>
        <div class="alert danger" style="background:#eee; color:#333;">
            Trebuie să fii <a href="../login.php">logat</a> pentru a lăsa o recenzie.
        </div>
    <?php endif; ?>

    <h3>Recenziile cititorilor</h3>
    
    <?php if ($recenzii->num_rows > 0): ?>
        <?php while($r = $recenzii->fetch_assoc()): ?>
            <div class="recenzie-card">
                <div class="rating-stars">
                    <?php 
                        echo str_repeat("★", $r['rating']); 
                        echo str_repeat("☆", 5 - $r['rating']); 
                    ?>
                </div>
                <div class="user-meta">
                    Postat de <strong><?php echo htmlspecialchars($r['nume']); ?></strong> 
                    pe <?php echo date("d.m.Y H:i", strtotime($r['data_recenzie'])); ?>
                </div>
                <div class="comment-text">
                    <?php echo nl2br(htmlspecialchars($r['comentariu'])); ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align:center; color:#7f8c8d;">Nu există încă recenzii. Fii primul care lasă una!</p>
    <?php endif; ?>
</div>

</body>
</html>