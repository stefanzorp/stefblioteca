<?php
include "../includes/config.php";
include "../includes/header.php"; // Acesta aduce Bootstrap și deschide containerul principal

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$id_utilizator_raw = $_SESSION['user_id'];
$id_utilizator = intval(str_replace("id_", "", $id_utilizator_raw));

// Logica de anulare
if (isset($_GET['anuleaza']) && isset($_GET['token'])) {
    if ($_GET['token'] === ($_SESSION['csrf_token'] ?? '')) {
        $id_rez = intval($_GET['anuleaza']);
        $conn->begin_transaction();
        try {
            $stmt_info = $conn->prepare("SELECT id_carte FROM rezervari WHERE id_rezervare = ? AND id_utilizator = ? AND status = 'activa'");
            $stmt_info->bind_param("ii", $id_rez, $id_utilizator);
            $stmt_info->execute();
            $res_info = $stmt_info->get_result()->fetch_assoc();

            if ($res_info) {
                $id_carte_anulata = $res_info['id_carte'];
                $conn->query("UPDATE carti SET nr_exemplare = nr_exemplare + 1 WHERE id_carte = $id_carte_anulata");
                $stmt_del = $conn->prepare("UPDATE rezervari SET status = 'anulata' WHERE id_rezervare = ?");
                $stmt_del->bind_param("i", $id_rez);
                $stmt_del->execute();
                $conn->commit();
                $msg_succes = "Rezervarea a fost anulată cu succes!";
            }
        } catch (Exception $e) {
            $conn->rollback();
            $msg_eroare = "Eroare la anulare: " . $e->getMessage();
        }
    }
}

// Interogare date
$sql = "SELECT r.id_rezervare AS id_rez, r.data_rezervare, c.titlu, c.editura 
        FROM rezervari r
        JOIN carti c ON r.id_carte = c.id_carte
        WHERE r.id_utilizator = ? AND r.status = 'activa'
        ORDER BY r.data_rezervare DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_utilizator);
$stmt->execute();
$rezultate = $stmt->get_result();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>📚 Rezervările mele</h2>
    <a href="carti_list.php" class="btn btn-outline-secondary btn-sm">← Înapoi la listă</a>
</div>

<?php if (isset($msg_succes)): ?>
    <div class="alert alert-success shadow-sm"><?php echo $msg_succes; ?></div>
<?php endif; ?>

<?php if (isset($msg_eroare)): ?>
    <div class="alert alert-danger shadow-sm"><?php echo $msg_eroare; ?></div>
<?php endif; ?>

<div class="table-responsive shadow-sm border rounded bg-white">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-primary">
            <tr>
                <th class="ps-3">Titlu Carte</th>
                <th>Data Rezervării</th>
                <th class="text-center">Acțiuni</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rezultate && $rezultate->num_rows > 0): ?>
                <?php while($row = $rezultate->fetch_assoc()): ?>
                    <tr>
                        <td class="ps-3">
                            <span class="fw-bold d-block text-dark"><?php echo htmlspecialchars($row['titlu']); ?></span>
                            <small class="text-muted"><?php echo htmlspecialchars($row['editura']); ?></small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <?php echo date("d.m.Y", strtotime($row['data_rezervare'])); ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="rezervarile_mele.php?anuleaza=<?php echo $row['id_rez']; ?>&token=<?php echo $_SESSION['csrf_token'] ?? ''; ?>" 
                               class="btn btn-sm btn-outline-danger" 
                               onclick="return confirm('Ești sigur că vrei să anulezi rezervarea?')">
                                🗑 Anulează
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="text-center py-5 text-muted">
                        <p class="mb-0">Nu ai nicio rezervare activă în acest moment.</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php 
include "../includes/footer.php"; // Închide containerul și aduce JS-ul
?>