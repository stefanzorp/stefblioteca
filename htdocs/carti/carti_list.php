<?php
include "../includes/config.php";

$rol = $_SESSION["user_rol"] ?? 'vizitator'; 
$nume_utilizator = $_SESSION["user_name"] ?? 'Oaspete';

$este_logat = (isset($_SESSION['user_id']) && !empty($_SESSION['user_id']));

$sql = "SELECT c.id_carte, c.titlu, a.nume AS autor, cat.nume_categorie AS categorie, 
               c.ISBN, c.editura, c.an_publicare, c.nr_exemplare
        FROM carti c
        LEFT JOIN autori a ON c.id_autor = a.id_autor
        LEFT JOIN categorii cat ON c.id_categorie = cat.id_categorie";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Stefblioteca - Lista Cărți</title>
    <style>
        body { font-family: sans-serif; margin: 20px; background-color: #f9f9f9; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #2c3e50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .msg { padding: 10px; margin-bottom: 10px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .btn-reserve { color: green; font-weight: bold; text-decoration: none; }
    </style>
</head>
<body>

    <h2>📚 Lista Cărților</h2>

    <?php if (isset($_GET['msg'])): ?>
        <div class="msg success"><?php echo htmlspecialchars($_GET['msg']); ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['eroare'])): ?>
        <div class="msg error"><?php echo htmlspecialchars($_GET['eroare']); ?></div>
    <?php endif; ?>

    <div style="margin-bottom: 20px; background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
    <?php if ($este_logat): ?>
        <p>Utilizator: <strong><?php echo htmlspecialchars($nume_utilizator); ?></strong> | 
           Rol: <span style="text-transform: uppercase; color: #e67e22;"><strong><?php echo htmlspecialchars($rol); ?></strong></span></p>
        
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="../index.php" style="padding: 8px 15px; background: #95a5a6; color: white; text-decoration: none; border-radius: 4px;">Acasă</a>
            
            <?php if ($rol === 'cititor'): ?>
                <a href="rezervarile_mele.php" style="padding: 8px 15px; background: #3498db; color: white; text-decoration: none; border-radius: 4px;">Rezervările Mele</a>
            <?php endif; ?>

            <?php if ($rol === 'bibliotecar' || $rol === 'admin'): ?>
                <a href="gestiune_rezervari.php" style="padding: 8px 15px; background: #2c3e50; color: white; text-decoration: none; border-radius: 4px; border: 2px solid #f1c40f;">📋 GESTIUNE REZERVĂRI & ÎMPRUMUTURI</a>
            <?php endif; ?>

            <?php if ($rol === 'admin'): ?>
                <a href="carti_add.php" style="padding: 8px 15px; background: #27ae60; color: white; text-decoration: none; border-radius: 4px;">+ Adaugă Carte</a>
            <?php endif; ?>
            <?php if ($rol === 'admin'): ?>
                <a href="admin_utilizatori.php" style="padding: 8px 15px; background: #8e44ad; color: white; text-decoration: none; border-radius: 4px;">👥 Gestionare Utilizatori</a>
            <?php endif; ?>

            <a href="../logout.php" style="padding: 8px 15px; background: #e74c3c; color: white; text-decoration: none; border-radius: 4px;">Logout</a>
        </div>
    <?php else: ?>
        <p>👋 <a href="../login.php">Loghează-te</a> pentru a rezerva cărți.</p>
    <?php endif; ?>
</div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Titlu</th>
                <th>Autor</th>
                <th>Categorie</th>
                <th>ISBN</th>
                <th>Editura</th>
                <th>An</th>
                <th>Stoc</th>
                <th>Acțiuni</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id_carte']; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['titlu']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['autor'] ?? 'Nespecificat'); ?></td>
                        <td><?php echo htmlspecialchars($row['categorie'] ?? 'General'); ?></td>
                        <td><?php echo htmlspecialchars($row['ISBN']); ?></td>
                        <td><?php echo htmlspecialchars($row['editura']); ?></td>
                        <td><?php echo (int)$row['an_publicare']; ?></td>
                        <td><?php echo (int)$row['nr_exemplare']; ?></td>
                        <td>
                            <a href="recenzii.php?id_carte=<?php echo $row['id_carte']; ?>">Recenzii</a>

                            <?php if ($rol === 'admin' || $rol === 'bibliotecar'): ?>
                                | <a href="carti_edit.php?id=<?php echo $row['id_carte']; ?>" style="color:blue;">Editează</a>
                                | <a href="carti_delete.php?id=<?php echo $row['id_carte']; ?>&token=<?php echo $_SESSION['csrf_token']; ?>" 
                                     style="color:red;" onclick="return confirm('Ștergi această carte?')">Șterge</a>
                            <?php endif; ?>

                            <?php if ($rol === 'cititor' && $este_logat): ?>
                                | <?php if ($row['nr_exemplare'] > 0): ?>
                                    <a href="rezervare_proceseaza.php?id=<?php echo $row['id_carte']; ?>&token=<?php echo $_SESSION['csrf_token']; ?>" class="btn-reserve">Rezervă</a>
                                  <?php else: ?>
                                    <span style="color:red;">Epuizat</span>
                                  <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="9" style="text-align:center;">Nu există cărți.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>