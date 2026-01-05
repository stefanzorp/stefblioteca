<?php
include "../includes/config.php";
include "../includes/header.php";

// Verificare permisiuni (Admin sau Bibliotecar)
$rol = $_SESSION["user_rol"] ?? 'cititor';
if (!isset($_SESSION['user_id']) || ($rol !== 'admin' && $rol !== 'bibliotecar')) { 
    die("<div class='alert alert-danger m-5 text-center'>Acces refuzat! Nu aveți permisiunea de a edita cărți.</div>");
}

$id = intval($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM carti WHERE id_carte=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$carte = $result->fetch_assoc();

if (!$carte) {
    die("<div class='alert alert-warning m-5 text-center'>Eroare: Cartea nu a fost găsită.</div>");
}

$eroare = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificare CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Eroare de securitate: Cerere invalidă.");
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
        echo "<script>window.location.href='carti_list.php?msg=Modificare salvată cu succes!';</script>";
        exit;
    } else {
        $eroare = "Eroare la actualizarea datelor în baza de date!";
    }
}

$autori = $conn->query("SELECT id_autor, nume FROM autori ORDER BY nume ASC");
$categorii = $conn->query("SELECT id_categorie, nume_categorie FROM categorii ORDER BY nume_categorie ASC");
?>

<div class="row justify-content-center py-4">
    <div class="col-md-8 col-lg-6">
        
        <div class="mb-3">
            <a href="carti_list.php" class="text-decoration-none text-secondary small">← Înapoi la listă</a>
        </div>

        <div class="card shadow border-0">
            <div class="card-header bg-dark text-white py-3">
                <h4 class="mb-0 fw-bold">✏️ Editare: <?php echo htmlspecialchars($carte['titlu']); ?></h4>
            </div>
            <div class="card-body p-4 p-md-5">

                <?php if($eroare): ?>
                    <div class="alert alert-danger shadow-sm"><?php echo $eroare; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Titlu Carte</label>
                        <input type="text" name="titlu" class="form-control" value="<?php echo htmlspecialchars($carte['titlu']); ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Autor</label>
                            <select name="id_autor" class="form-select" required>
                                <?php while($a = $autori->fetch_assoc()): ?>
                                    <option value="<?php echo $a['id_autor']; ?>" 
                                        <?php if($a['id_autor'] == $carte['id_autor']) echo "selected"; ?>>
                                        <?php echo htmlspecialchars($a['nume']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Categorie</label>
                            <select name="id_categorie" class="form-select" required>
                                <?php while($c = $categorii->fetch_assoc()): ?>
                                    <option value="<?php echo $c['id_categorie']; ?>" 
                                        <?php if($c['id_categorie'] == $carte['id_categorie']) echo "selected"; ?>>
                                        <?php echo htmlspecialchars($c['nume_categorie']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-7 mb-3">
                            <label class="form-label fw-bold">ISBN</label>
                            <input type="text" name="ISBN" class="form-control" value="<?php echo htmlspecialchars($carte['ISBN']); ?>" required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label fw-bold">Nr. Exemplare</label>
                            <input type="number" name="nr_exemplare" class="form-control" value="<?php echo $carte['nr_exemplare']; ?>" min="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">Editură</label>
                            <input type="text" name="editura" class="form-control" value="<?php echo htmlspecialchars($carte['editura']); ?>">
                        </div>
                        <div class="col-md-4 mb-4">
                            <label class="form-label fw-bold">An Publicare</label>
                            <input type="number" name="an_publicare" class="form-control" value="<?php echo $carte['an_publicare']; ?>">
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-2">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm">Salvează Modificările</button>
                        <a href="carti_list.php" class="btn btn-outline-secondary">Anulează</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<?php 
include "../includes/footer.php"; 
?>