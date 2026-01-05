<?php
include "../includes/config.php";
include "../includes/header.php";

// Verificare acces (doar bibliotecar sau admin)
if (!isset($_SESSION['user_rol']) || ($_SESSION['user_rol'] !== 'bibliotecar' && $_SESSION['user_rol'] !== 'admin')) {
    die("<div class='alert alert-danger m-5'>Acces refuzat! Nu aveți permisiunile necesare.</div>");
}

$msg_err = "";
$msg_success = isset($_GET['msg']) ? $_GET['msg'] : "";

// Logica pentru confirmarea ridicarii (Rezervare -> Împrumut)
if (isset($_GET['confirma_ridicare'])) {
    $id_rez = intval($_GET['confirma_ridicare']);
    
    $res = $conn->query("SELECT id_utilizator, id_carte FROM rezervari WHERE id_rezervare = $id_rez");
    if ($row = $res->fetch_assoc()) {
        $id_u = $row['id_utilizator'];
        $id_c = $row['id_carte'];
        $data_limita = date('Y-m-d', strtotime('+14 days')); 

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO imprumuturi (id_utilizator, id_carte, data_imprumut, data_limita, status) VALUES (?, ?, CURDATE(), ?, 'activ')");
            $stmt->bind_param("iis", $id_u, $id_c, $data_limita);
            $stmt->execute();

            $conn->query("UPDATE rezervari SET status = 'finalizata' WHERE id_rezervare = $id_rez");

            $conn->commit();
            echo "<script>window.location.href='gestiune_rezervari.php?msg=Cartea a fost ridicată!';</script>";
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $msg_err = "Eroare: " . $e->getMessage();
        }
    }
}

// Logica pentru returnare
if (isset($_GET['returneaza_imprumut'])) {
    $id_imp = intval($_GET['returneaza_imprumut']);
    
    $res = $conn->query("SELECT id_carte FROM imprumuturi WHERE id_imprumut = $id_imp");
    if ($row = $res->fetch_assoc()) {
        $id_c = $row['id_carte'];
        
        $conn->begin_transaction();
        try {
            $conn->query("UPDATE imprumuturi SET status = 'returnat', data_returnare = CURDATE() WHERE id_imprumut = $id_imp");
            $conn->query("UPDATE carti SET nr_exemplare = nr_exemplare + 1 WHERE id_carte = $id_c");
            
            $conn->commit();
            echo "<script>window.location.href='gestiune_rezervari.php?msg=Carte returnată cu succes!';</script>";
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $msg_err = "Eroare la returnare.";
        }
    }
}

// Preluare date pentru tabele
$rezervari = $conn->query("SELECT r.id_rezervare, u.nume as cititor, c.titlu, r.data_rezervare 
                           FROM rezervari r 
                           JOIN utilizatori u ON r.id_utilizator = u.id_utilizator 
                           JOIN carti c ON r.id_carte = c.id_carte 
                           WHERE r.status = 'activa' ORDER BY r.data_rezervare ASC");

$imprumuturi = $conn->query("SELECT i.id_imprumut, u.nume as cititor, c.titlu, i.data_imprumut, i.data_limita 
                             FROM imprumuturi i 
                             JOIN utilizatori u ON i.id_utilizator = u.id_utilizator 
                             JOIN carti c ON i.id_carte = c.id_carte 
                             WHERE i.status = 'activ' ORDER BY i.data_limita ASC");
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-dark">🛠️ Panou Control Bibliotecar</h2>
        <a href="carti_list.php" class="btn btn-outline-secondary btn-sm">← Înapoi la listă</a>
    </div>

    <?php if ($msg_success): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            ✅ <?php echo htmlspecialchars($msg_success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($msg_err): ?>
        <div class="alert alert-danger shadow-sm">
            ❌ <?php echo $msg_err; ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-5">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="card-title mb-0"><i class="bi bi-bookmark-check"></i> 1. Rezervări Online (Așteaptă ridicarea)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Cititor</th>
                            <th>Carte</th>
                            <th>Data Rezervării</th>
                            <th class="text-end pe-4">Acțiune</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($rezervari->num_rows > 0): ?>
                            <?php while($r = $rezervari->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?php echo htmlspecialchars($r['cititor']); ?></td>
                                <td><?php echo htmlspecialchars($r['titlu']); ?></td>
                                <td><?php echo date("d.m.Y", strtotime($r['data_rezervare'])); ?></td>
                                <td class="text-end pe-4">
                                    <a href="gestiune_rezervari.php?confirma_ridicare=<?php echo $r['id_rezervare']; ?>" 
                                       class="btn btn-sm btn-primary px-3 shadow-sm"
                                       onclick="return confirm('Confirmi că cititorul a ridicat cartea?')">
                                       Confirmă Ridicarea
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">Nu există rezervări active.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-success text-white py-3">
            <h5 class="card-title mb-0"><i class="bi bi-book"></i> 2. Împrumuturi Active (Cărți la cititori)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Cititor</th>
                            <th>Carte</th>
                            <th>Data Împrumut</th>
                            <th>Termen Limită</th>
                            <th class="text-end pe-4">Acțiune</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($imprumuturi->num_rows > 0): ?>
                            <?php while($i = $imprumuturi->fetch_assoc()): 
                                $este_intarziat = (strtotime($i['data_limita']) < time());
                            ?>
                            <tr class="<?php echo $este_intarziat ? 'table-danger' : ''; ?>">
                                <td class="ps-4 fw-bold"><?php echo htmlspecialchars($i['cititor']); ?></td>
                                <td><?php echo htmlspecialchars($i['titlu']); ?></td>
                                <td><?php echo date("d.m.Y", strtotime($i['data_imprumut'])); ?></td>
                                <td class="<?php echo $este_intarziat ? 'fw-bold text-danger' : ''; ?>">
                                    <?php echo date("d.m.Y", strtotime($i['data_limita'])); ?>
                                    <?php if($este_intarziat) echo ' <span class="badge bg-danger">DEPĂȘIT!</span>'; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="gestiune_rezervari.php?returneaza_imprumut=<?php echo $i['id_imprumut']; ?>" 
                                       class="btn btn-sm btn-success px-3 shadow-sm"
                                       onclick="return confirm('Confirmi returnarea cărții?')">
                                       Marchează Returnat
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">Nu există împrumuturi active.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>