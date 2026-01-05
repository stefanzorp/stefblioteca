<?php
include "../includes/config.php";
include "../includes/header.php";

// Verificare acces
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'admin') {
    die("<div class='alert alert-danger m-5'>Acces refuzat! Doar administratorii pot accesa această pagină.</div>");
}

$mesaj = "";
$eroare = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    // Verificare CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Eroare de securitate: Cerere invalidă.");
    }

    $id_u = intval($_POST['id_utilizator']);
    $noul_rol = $_POST['rol'];
    $noul_status = $_POST['status'];
    
    // Adminul nu poate sa scoata propriul roll
    $current_admin_id = intval(str_replace("id_", "", $_SESSION['user_id']));
    
    if ($id_u === $current_admin_id && $noul_rol !== 'admin') {
        $eroare = "Nu îți poți schimba singur propriul rol de Admin!";
    } else {
        $stmt = $conn->prepare("UPDATE utilizatori SET rol = ?, status = ? WHERE id_utilizator = ?");
        $stmt->bind_param("ssi", $noul_rol, $noul_status, $id_u);
        
        if ($stmt->execute()) {
            $mesaj = "Utilizatorul a fost actualizat cu succes!";
        } else {
            $eroare = "Eroare la actualizare.";
        }
    }
}

$sql = "SELECT id_utilizator, nume, email, rol, status, data_inregistrare FROM utilizatori ORDER BY data_inregistrare DESC";
$rezultat = $conn->query($sql);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>👥 Gestionare Membri</h2>
        <a href="carti_list.php" class="btn btn-outline-secondary btn-sm">← Înapoi la listă</a>
    </div>

    <?php if ($mesaj): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $mesaj; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($eroare): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $eroare; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4">Nume & Email</th>
                            <th>Data Înscriere</th>
                            <th>Rol</th>
                            <th>Status</th>
                            <th class="text-center">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($user = $rezultat->fetch_assoc()): 
                            // culori la badge dupa rol
                            $badge_class = 'bg-secondary';
                            if($user['rol'] == 'admin') $badge_class = 'bg-purple text-white';
                            if($user['rol'] == 'bibliotecar') $badge_class = 'bg-warning text-dark';
                            if($user['rol'] == 'cititor') $badge_class = 'bg-info text-dark';
                            
                            // Culori status
                            $status_class = ($user['status'] == 'activ') ? 'text-success' : 'text-danger';
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold"><?php echo htmlspecialchars($user['nume']); ?></div>
                                <div class="small text-muted"><?php echo htmlspecialchars($user['email']); ?></div>
                            </td>
                            <td><?php echo date("d.m.Y", strtotime($user['data_inregistrare'])); ?></td>
                            <td>
                                <span class="badge <?php echo $badge_class; ?> text-uppercase" style="font-size: 0.75rem;">
                                    <?php echo $user['rol']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold <?php echo $status_class; ?>">
                                    ● <?php echo ucfirst($user['status']); ?>
                                </span>
                            </td>
                            <td class="pe-4">
                                <form method="POST" class="row g-2 align-items-center justify-content-center text-center">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="id_utilizator" value="<?php echo $user['id_utilizator']; ?>">
                                    
                                    <div class="col-auto">
                                        <select name="rol" class="form-select form-select-sm shadow-sm">
                                            <option value="cititor" <?php if($user['rol'] == 'cititor') echo 'selected'; ?>>Cititor</option>
                                            <option value="bibliotecar" <?php if($user['rol'] == 'bibliotecar') echo 'selected'; ?>>Bibliotecar</option>
                                            <option value="admin" <?php if($user['rol'] == 'admin') echo 'selected'; ?>>Admin</option>
                                        </select>
                                    </div>

                                    <div class="col-auto">
                                        <select name="status" class="form-select form-select-sm shadow-sm">
                                            <option value="activ" <?php if($user['status'] == 'activ') echo 'selected'; ?>>Activ</option>
                                            <option value="inactiv" <?php if($user['status'] == 'inactiv') echo 'selected'; ?>>Inactiv</option>
                                        </select>
                                    </div>

                                    <div class="col-auto">
                                        <button type="submit" name="update_user" class="btn btn-primary btn-sm px-3 shadow-sm">OK</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-purple { background-color: #8e44ad !important; }
</style>

<?php 
include "../includes/footer.php"; 
?>