<?php
session_start();
include "../includes/config.php";
ini_set('display_errors', 1);
error_reporting(E_ALL);

$rol = $_SESSION["user_rol"] ?? 'cititor';
if (!isset($_SESSION['user_id']) || ($rol !== 'admin' && $rol !== 'bibliotecar')) { 
    die("Nu ai permisiunea de a accesa această pagină.");
}

$eroare = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
    $descriere = $_POST['descriere'];
    $imagine = $_POST['imagine'];
    $stmt = $conn->prepare("INSERT INTO carti (titlu, id_autor, id_categorie, ISBN, editura, an_publicare, nr_exemplare, descriere, imagine) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("siissiiss", $titlu, $id_autor, $id_categorie, $ISBN, $editura, $an_publicare, $nr_exemplare, $descriere, $imagine);
    
    if($stmt->execute()){
        header("Location: carti_list.php?msg=Carte adăugată cu succes!");
        exit;
    } else {
        $eroare = "Eroare la adăugare carte!";
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
    <title>Adaugă Carte Nouă - Stefblioteca</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f7f8; margin: 0; padding: 20px; }
        .container { max-width: 700px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { color: #2c3e50; border-bottom: 2px solid #27ae60; padding-bottom: 10px; margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #34495e; }
        input, select, textarea {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 14px; font-family: inherit;
        }
        input:focus, select:focus, textarea:focus { border-color: #27ae60; outline: none; box-shadow: 0 0 5px rgba(39, 174, 96, 0.2); }
        .btn-add { background-color: #27ae60; color: white; padding: 12px; border: none; border-radius: 6px; cursor: pointer; width: 100%; font-size: 16px; font-weight: bold; margin-top: 10px; }
        .btn-add:hover { background-color: #219150; }
        .btn-back { display: inline-block; margin-bottom: 20px; text-decoration: none; color: #7f8c8d; }
        .grid-half { display: flex; gap: 15px; }
        .grid-half > div { flex: 1; }
        .error { color: #e74c3c; background: #fdeaea; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="container">
    <a href="carti_list.php" class="btn-back">← Înapoi la listă</a>
    <h2>Adaugă Carte Nouă</h2>

    <?php if($eroare): ?>
        <div class="error"><?php echo $eroare; ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <div class="form-group">
            <label>Titlu:</label>
            <input type="text" name="titlu" placeholder="Ex: Maitreyi" required>
        </div>

        <div class="grid-half">
            <div class="form-group">
                <label>Autor:</label>
                <select name="id_autor" required>
                    <option value="">-- Selectează Autor --</option>
                    <?php while($a=$autori->fetch_assoc()): ?>
                        <option value="<?php echo $a['id_autor']; ?>"><?php echo htmlspecialchars($a['nume']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Categorie:</label>
                <select name="id_categorie" required>
                    <option value="">-- Selectează Categorie --</option>
                    <?php while($c=$categorii->fetch_assoc()): ?>
                        <option value="<?php echo $c['id_categorie']; ?>"><?php echo htmlspecialchars($c['nume_categorie']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div class="grid-half">
            <div class="form-group">
                <label>ISBN:</label>
                <input type="text" name="ISBN" placeholder="Ex: 978-606-...">
            </div>
            <div class="form-group">
                <label>Editură:</label>
                <input type="text" name="editura">
            </div>
        </div>

        <div class="grid-half">
            <div class="form-group">
                <label>An Publicare:</label>
                <input type="number" name="an_publicare" placeholder="YYYY">
            </div>
            <div class="form-group">
                <label>Număr Exemplare:</label>
                <input type="number" name="nr_exemplare" value="1" min="1">
            </div>
        </div>

        <div class="form-group">
            <label>Cale Imagine (URL sau nume fișier):</label>
            <input type="text" name="imagine" placeholder="coperta1.jpg">
        </div>

        <div class="form-group">
            <label>Descriere / Rezumat:</label>
            <textarea name="descriere" rows="4" placeholder="O scurtă descriere a cărții..."></textarea>
        </div>

        <button type="submit" class="btn-add">Adaugă Cartea în Bibliotecă</button>
    </form>
</div>

</body>
</html>