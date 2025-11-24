<?php
session_start();
include "../includes/config.php";
ini_set('display_errors', 1);
error_reporting(E_ALL);

$rol = $_SESSION["user_rol"] ?? 'cititor';

// Preluare toate cărțile
$sql = "SELECT c.id_carte, c.titlu, a.nume AS autor, cat.nume_categorie AS categorie, 
               c.ISBN, c.editura, c.an_publicare, c.nr_exemplare
        FROM carti c
        LEFT JOIN autori a ON c.id_autor = a.id_autor
        LEFT JOIN categorii cat ON c.id_categorie = cat.id_categorie";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Lista Cărți</title>
</head>
<body>

<h2>Lista Cărților</h2>

<?php if (!empty($_SESSION['user_id']) && $_SESSION['user_id'] != ""): ?>
    <p>Bun venit, <?php echo htmlspecialchars($_SESSION['user_name']); ?> | <a href="../logout.php">Logout</a></p>
<?php endif; ?>

<?php if ($rol === 'admin' || $rol === 'bibliotecar'): ?>
    <p><a href="carti_add.php">Adaugă carte</a></p>
<?php endif; ?>
    
    

<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Titlu</th>
        <th>Autor</th>
        <th>Categorie</th>
        <th>ISBN</th>
        <th>Editura</th>
        <th>An Publicare</th>
        <th>Nr. Exemplare</th>
        <?php if ($rol === 'admin' || $rol === 'bibliotecar'): ?>
        <th>Acțiuni</th>
        <?php endif; ?>
    </tr>

    <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id_carte']; ?></td>
            <td><?php echo htmlspecialchars($row['titlu']); ?></td>
            <td><?php echo htmlspecialchars($row['autor']); ?></td>
            <td><?php echo htmlspecialchars($row['categorie']); ?></td>
            <td><?php echo htmlspecialchars($row['ISBN']); ?></td>
            <td><?php echo htmlspecialchars($row['editura']); ?></td>
            <td><?php echo $row['an_publicare']; ?></td>
            <td><?php echo $row['nr_exemplare']; ?></td>
            <?php if ($rol === 'admin' || $rol === 'bibliotecar'): ?>
            <td>
                <a href="carti_edit.php?id=<?php echo $row['id_carte']; ?>">Editează</a> |
                <a href="carti_delete.php?id=<?php echo $row['id_carte']; ?>" onclick="return confirm('Șterge această carte?')">Șterge</a>
            </td>

            <?php endif; ?>
        </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
