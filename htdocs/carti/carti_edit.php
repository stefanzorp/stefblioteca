<?php
session_start();
include "../includes/config.php";
ini_set('display_errors', 1);
error_reporting(E_ALL);

$rol = $_SESSION["user_rol"] ?? 'cititor';
if (!isset($_SESSION['user_id']) || ($rol !== 'admin' && $rol !== 'bibliotecar')) { 
    die("Nu ai permisiunea de a accesa această pagină.");
}

$id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM carti WHERE id_carte=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$carte = $result->fetch_assoc();
if (!$carte) die("Carte inexistentă.");

$eroare = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // verific CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Eroare de securitate: Cerere invalidă (CSRF detected).");
    }


    $titlu = $_POST['titlu'];
    $id_autor = $_POST['id_autor'];
    $id_categorie = $_POST['id_categorie'];
    $ISBN = $_POST['ISBN'];
    $editura = $_POST['editura'];
    $an_publicare = $_POST['an_publicare'];
    $nr_exemplare = $_POST['nr_exemplare'];

    $stmt = $conn->prepare("UPDATE carti SET titlu=?, id_autor=?, id_categorie=?, ISBN=?, editura=?, an_publicare=?, nr_exemplare=? WHERE id_carte=?");
    $stmt->bind_param("siissiii", $titlu, $id_autor, $id_categorie, $ISBN, $editura, $an_publicare, $nr_exemplare, $id);
    if ($stmt->execute()) {
        header("Location: carti_list.php?msg=Modificare salvată!");
        exit;
    } else {
        $eroare = "Eroare la actualizare!";
    }
}

$autori = $conn->query("SELECT id_autor, nume FROM autori ORDER BY nume ASC");
$categorii = $conn->query("SELECT id_categorie, nume_categorie FROM categorii ORDER BY nume_categorie ASC");
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editare Carte - <?php echo htmlspecialchars($carte['titlu']); ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f8; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #34495e; }
        input[type="text"], input[type="number"], select {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 14px;
        }
        input:focus, select:focus { border-color: #3498db; outline: none; box-shadow: 0 0 5px rgba(52, 152, 219, 0.2); }
        .btn-submit { background-color: #2c3e50; color: white; padding: 12px 20px; border: none; border-radius: 6px; cursor: pointer; width: 100%; font-size: 16px; font-weight: bold; margin-top: 10px; }
        .btn-submit:hover { background-color: #34495e; }
        .btn-back { display: inline-block; margin-bottom: 20px; text-decoration: none; color: #7f8c8d; font-size: 14px; }
        .btn-back:hover { color: #2c3e50; }
        .error { color: #e74c3c; background: #fdeaea; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .grid-half { display: flex; gap: 15px; }
        .grid-half > div { flex: 1; }
    </style>
</head>
<body>

<div class="container">
    <a href="carti_list.php" class="btn-back">← Înapoi la listă</a>
    <h2>Editare Carte</h2>

    <?php if($eroare): ?>
        <div class="error"><?php echo $eroare; ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <div class="form-group">
            <label>Titlu Carte:</label>
            <input type="text" name="titlu" value="<?php echo htmlspecialchars($carte['titlu']); ?>" required>
        </div>

        <div class="grid-half">
            <div class="form-group">
                <label>Autor:</label>
                <select name="id_autor" required>
                    <?php while($a=$autori->fetch_assoc()): ?>
                        <option value="<?php echo $a['id_autor']; ?>" 
                            <?php if($a['id_autor']==$carte['id_autor']) echo "selected"; ?>>
                            <?php echo htmlspecialchars($a['nume']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Categorie:</label>
                <select name="id_categorie" required>
                    <?php while($c=$categorii->fetch_assoc()): ?>
                        <option value="<?php echo $c['id_categorie']; ?>" 
                            <?php if($c['id_categorie']==$carte['id_categorie']) echo "selected"; ?>>
                            <?php echo htmlspecialchars($c['nume_categorie']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>ISBN:</label>
            <input type="text" name="ISBN" value="<?php echo htmlspecialchars($carte['ISBN']); ?>" required>
        </div>

        <div class="form-group">
            <label>Editură:</label>
            <input type="text" name="editura" value="<?php echo htmlspecialchars($carte['editura']); ?>">
        </div>

        <div class="grid-half">
            <div class="form-group">
                <label>An Publicare:</label>
                <input type="number" name="an_publicare" value="<?php echo $carte['an_publicare']; ?>">
            </div>
            <div class="form-group">
                <label>Nr. Exemplare:</label>
                <input type="number" name="nr_exemplare" value="<?php echo $carte['nr_exemplare']; ?>" min="0">
            </div>
        </div>

        <button type="submit" class="btn-submit">Salvează Modificările</button>
    </form>
</div>

</body>
</html>