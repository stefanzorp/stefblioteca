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
$stmt->bind_param("i",$id);
$stmt->execute();
$result = $stmt->get_result();
$carte = $result->fetch_assoc();
if(!$carte) die("Carte inexistentă.");

$eroare = "";
if($_SERVER['REQUEST_METHOD']=='POST'){
    $titlu = $_POST['titlu'];
    $id_autor = $_POST['id_autor'];
    $id_categorie = $_POST['id_categorie'];
    $ISBN = $_POST['ISBN'];
    $editura = $_POST['editura'];
    $an_publicare = $_POST['an_publicare'];
    $nr_exemplare = $_POST['nr_exemplare'];

    $stmt = $conn->prepare("UPDATE carti SET titlu=?, id_autor=?, id_categorie=?, ISBN=?, editura=?, an_publicare=?, nr_exemplare=? WHERE id_carte=?");
    $stmt->bind_param("siissiii",$titlu,$id_autor,$id_categorie,$ISBN,$editura,$an_publicare,$nr_exemplare,$id);
    if($stmt->execute()){
        header("Location: carti_list.php");
        exit;
    }else{
        $eroare = "Eroare la actualizare!";
    }
}

$autori = $conn->query("SELECT id_autor,nume FROM autori");
$categorii = $conn->query("SELECT id_categorie,nume_categorie FROM categorii");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Editare carte</title>
</head>
<body>
<h2>Editare Carte</h2>
<?php if($eroare) echo "<p style='color:red;'>$eroare</p>"; ?>

<form method="POST">
    Titlu:<br><input type="text" name="titlu" value="<?php echo htmlspecialchars($carte['titlu']); ?>" required><br><br>
    Autor:<br>
	<select name="id_autor" required>
        <?php while($a=$autori->fetch_assoc()): ?>
            <option value="<?php echo $a['id_autor']; ?>" 
                <?php if($a['id_autor']==$carte['id_autor']) echo "selected"; ?>>
                <?php echo htmlspecialchars($a['nume']); ?>
            </option>
        <?php endwhile; ?>
	</select><br><br>
    Categorie:<br>
    <select name="id_categorie" required>
    <?php while($c=$categorii->fetch_assoc()): ?>
        <option value="<?php echo $c['id_categorie']; ?>" 
            <?php if($c['id_categorie']==$carte['id_categorie']) echo "selected"; ?>>
            <?php echo htmlspecialchars($c['nume_categorie']); ?>
        </option>
    <?php endwhile; ?>
    </select><br><br>
    ISBN:<br><input type="text" name="ISBN" value="<?php echo htmlspecialchars($carte['ISBN']); ?>" required><br><br>
    Editura:<br><input type="text" name="editura" value="<?php echo htmlspecialchars($carte['editura']); ?>"><br><br>
    An publicare:<br><input type="number" name="an_publicare" value="<?php echo $carte['an_publicare']; ?>"><br><br>
    Nr. exemplare:<br><input type="number" name="nr_exemplare" value="<?php echo $carte['nr_exemplare']; ?>"><br><br>
    <button type="submit">Actualizează</button>
</form>
</body>
</html>
