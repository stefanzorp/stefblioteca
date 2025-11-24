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
    $titlu = $_POST['titlu'];
    $id_autor = $_POST['id_autor'];
    $id_categorie = $_POST['id_categorie'];
    $ISBN = $_POST['ISBN'];
    $editura = $_POST['editura'];
    $an_publicare = $_POST['an_publicare'];
    $nr_exemplare = $_POST['nr_exemplare'];

    $stmt = $conn->prepare("INSERT INTO carti (titlu,id_autor,id_categorie,ISBN,editura,an_publicare,nr_exemplare) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param("siissii",$titlu,$id_autor,$id_categorie,$ISBN,$editura,$an_publicare,$nr_exemplare);
    if($stmt->execute()){
        header("Location: carti_list.php");
        exit;
    } else {
        $eroare = "Eroare la adăugare carte!";
    }
}

// Preluare autori si categorii pentru dropdown
$autori = $conn->query("SELECT id_autor,nume FROM autori");
$categorii = $conn->query("SELECT id_categorie,nume_categorie FROM categorii");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Adaugă carte</title>
</head>
<body>
<h2>Adaugă Carte</h2>
<?php if($eroare) echo "<p style='color:red;'>$eroare</p>"; ?>

<form method="POST">
    Titlu:<br><input type="text" name="titlu" required><br><br>
    Autor:<br>
    <select name="id_autor" required>
        <?php while($a=$autori->fetch_assoc()): ?>
            <option value="<?php echo $a['id_autor']; ?>"><?php echo htmlspecialchars($a['nume']); ?></option>
        <?php endwhile; ?>
    </select><br><br>
    Categorie:<br>
    <select name="id_categorie" required>
        <?php while($c=$categorii->fetch_assoc()): ?>
            <option value="<?php echo $c['id_categorie']; ?>"><?php echo htmlspecialchars($c['nume_categorie']); ?></option>
        <?php endwhile; ?>
    </select><br><br>
    ISBN:<br><input type="text" name="ISBN" required><br><br>
    Editura:<br><input type="text" name="editura"><br><br>
    An publicare:<br><input type="number" name="an_publicare"><br><br>
    Nr. exemplare:<br><input type="number" name="nr_exemplare"><br><br>
    <button type="submit">Adaugă</button>
</form>
</body>
</html>
