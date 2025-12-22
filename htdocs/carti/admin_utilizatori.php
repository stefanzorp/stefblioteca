<?php
include "../includes/config.php";

if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] !== 'admin') {
    die("Acces refuzat! Doar administratorii pot accesa această pagină.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    // verific csrf
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Eroare de securitate: Cerere invalidă (CSRF detected).");
    }

    $titlu = $_POST['titlu'];
    
    $id_u = intval($_POST['id_utilizator']);
    $noul_rol = $_POST['rol'];
    $noul_status = $_POST['status'];
    
    //adminul nu poate sa scoata propriul rol
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

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Administrare Utilizatori - Stefblioteca</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f7f6; margin: 20px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 1000px; margin: auto; }
        h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
        th { background-color: #34495e; color: white; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .role-cititor { background: #e8f4fd; color: #2980b9; }
        .role-bibliotecar { background: #fef9e7; color: #f39c12; }
        .role-admin { background: #f4ecf7; color: #8e44ad; }
        .status-activ { color: #27ae60; }
        .status-inactiv { color: #e74c3c; }
        select { padding: 5px; border-radius: 4px; border: 1px solid #ccc; }
        .btn-update { background: #3498db; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; }
        .btn-update:hover { background: #2980b9; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .danger { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<div class="card">
    <h2>👥 Gestionare Membri</h2>
    <a href="carti_list.php" style="text-decoration: none; color: #3498db;">← Înapoi la listă</a>

    <?php if (isset($mesaj)): ?>
        <div class="alert success"><?php echo $mesaj; ?></div>
    <?php endif; ?>
    <?php if (isset($eroare)): ?>
        <div class="alert danger"><?php echo $eroare; ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Nume & Email</th>
                <th>Data Înscriere</th>
                <th>Rol</th>
                <th>Status</th>
                <th>Acțiuni</th>
            </tr>
        </thead>
        <tbody>
            <?php while($user = $rezultat->fetch_assoc()): ?>
            <tr>
                <td>
                    <strong><?php echo htmlspecialchars($user['nume']); ?></strong><br>
                    <small style="color: #7f8c8d;"><?php echo htmlspecialchars($user['email']); ?></small>
                </td>
                <td><?php echo date("d.m.Y", strtotime($user['data_inregistrare'])); ?></td>
                <td>
                    <span class="badge role-<?php echo $user['rol']; ?>">
                        <?php echo $user['rol']; ?>
                    </span>
                </td>
                <td class="status-<?php echo $user['status']; ?>">
                    <strong><?php echo $user['status']; ?></strong>
                </td>
                <td>
                    <form method="POST" style="display: flex; gap: 5px;">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="id_utilizator" value="<?php echo $user['id_utilizator']; ?>">
                        
                        <select name="rol">
                            <option value="cititor" <?php if($user['rol'] == 'cititor') echo 'selected'; ?>>Cititor</option>
                            <option value="bibliotecar" <?php if($user['rol'] == 'bibliotecar') echo 'selected'; ?>>Bibliotecar</option>
                            <option value="admin" <?php if($user['rol'] == 'admin') echo 'selected'; ?>>Admin</option>
                        </select>

                        <select name="status">
                            <option value="activ" <?php if($user['status'] == 'activ') echo 'selected'; ?>>Activ</option>
                            <option value="inactiv" <?php if($user['status'] == 'inactiv') echo 'selected'; ?>>Inactiv</option>
                        </select>

                        <button type="submit" name="update_user" class="btn-update">OK</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>