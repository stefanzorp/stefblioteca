<?php
include "../includes/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$id_utilizator_raw = $_SESSION['user_id'];
$id_utilizator = intval(str_replace("id_", "", $id_utilizator_raw));

//anulare
if (isset($_GET['anuleaza']) && isset($_GET['token'])) {
    //verific csrf
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

                //+carte in stoc
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

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Rezervările Mele - Stefblioteca</title>
    <style>
        body { font-family: sans-serif; margin: 30px; background-color: #f4f4f4; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 800px; margin: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: #3498db; color: white; }
        .btn-back { display: inline-block; margin-bottom: 15px; text-decoration: none; color: #3498db; font-weight: bold; }
        .btn-anuleaza { color: #e74c3c; text-decoration: none; font-weight: bold; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="container">
    <a href="carti_list.php" class="btn-back">← Înapoi la listă</a>
    <h2>Cărțile mele rezervate</h2>

    <?php if (isset($msg_succes)): ?>
        <div class="alert success"><?php echo $msg_succes; ?></div>
    <?php endif; ?>

    <?php if (isset($msg_eroare)): ?>
        <div class="alert error"><?php echo $msg_eroare; ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Titlu Carte</th>
                <th>Data Rezervării</th>
                <th>Acțiuni</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rezultate && $rezultate->num_rows > 0): ?>
                <?php while($row = $rezultate->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($row['titlu']); ?></strong><br>
                            <small><?php echo htmlspecialchars($row['editura']); ?></small>
                        </td>
                        <td><?php echo date("d.m.Y", strtotime($row['data_rezervare'])); ?></td>
                        <td>
                            <a href="rezervarile_mele.php?anuleaza=<?php echo $row['id_rez']; ?>&token=<?php echo $_SESSION['csrf_token'] ?? ''; ?>" 
                               class="btn-anuleaza" 
                               onclick="return confirm('Ești sigur că vrei să anulezi rezervarea?')">
                               Anulează
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" style="text-align:center; padding: 20px;">Nu ai nicio rezervare activă în acest moment.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>