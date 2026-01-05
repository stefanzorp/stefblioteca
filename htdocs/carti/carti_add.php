<?php
include "../includes/config.php";
include "../includes/header.php";

//erori
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Verificare permisiuni
$rol = $_SESSION["user_rol"] ?? 'cititor';
if (!isset($_SESSION['user_id']) || ($rol !== 'admin' && $rol !== 'bibliotecar')) { 
    die("<div class='alert alert-danger m-5'>Acces refuzat! Nu aveți permisiunea de a adăuga cărți.</div>");
}

$eroare = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
    $descriere = $_POST['descriere'];
    $imagine = $_POST['imagine'];

    $stmt = $conn->prepare("INSERT INTO carti (titlu, id_autor, id_categorie, ISBN, editura, an_publicare, nr_exemplare, descriere, imagine) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("siissiiss", $titlu, $id_autor, $id_categorie, $ISBN, $editura, $an_publicare, $nr_exemplare, $descriere, $imagine);
    
    if($stmt->execute()){
        echo "<script>window.location.href='carti_list.php?msg=Carte adăugată cu succes!';</script>";
        exit;
    } else {
        $eroare = "Eroare la adăugare carte!";
    }
}

$autori = $conn->query("SELECT id_autor, nume FROM autori ORDER BY nume ASC");
$categorii = $conn->query("SELECT id_categorie, nume_categorie FROM categorii ORDER BY nume_categorie ASC");
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        
        <div class="mb-3">
            <a href="carti_list.php" class="text-decoration-none text-secondary small">← Înapoi la listă</a>
        </div>

        <div class="card shadow border-0">
            <div class="card-header bg-success text-white py-3">
                <h4 class="mb-0 fw-bold">📖 Adaugă Carte Nouă</h4>
            </div>
            <div class="card-body p-4">

                <?php if($eroare): ?>
                    <div class="alert alert-danger"><?php echo $eroare; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Titlu</label>
                        <input type="text" name="titlu" class="form-control" placeholder="Ex: Maitreyi" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Autor</label>
                            <select name="id_autor" class="form-select" required>
                                <option value="">-- Selectează Autor --</option>
                                <?php while($a=$autori->fetch_assoc()): ?>
                                    <option value="<?php echo $a['id_autor']; ?>"><?php echo htmlspecialchars($a['nume']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Categorie</label>
                            <select name="id_categorie" class="form-select" required>
                                <option value="">-- Selectează Categorie --</option>
                                <?php while($c=$categorii->fetch_assoc()): ?>
                                    <option value="<?php echo $c['id_categorie']; ?>"><?php echo htmlspecialchars($c['nume_categorie']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">ISBN</label>
                            <input type="text" name="ISBN" class="form-control" placeholder="Ex: 978-606-...">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Editură</label>
                            <input type="text" name="editura" class="form-control">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">An Publicare</label>
                            <input type="number" name="an_publicare" class="form-control" placeholder="YYYY">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Număr Exemplare</label>
                            <input type="number" name="nr_exemplare" class="form-control" value="1" min="1">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Cale Imagine (URL sau nume fișier)</label>
                        <input type="text" name="imagine" class="form-control" placeholder="coperta1.jpg">
                        <div class="form-text text-muted small">Imaginea trebuie să existe în folderul /images/</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Descriere / Rezumat</label>
                        <textarea name="descriere" class="form-control" rows="4" placeholder="O scurtă descriere a cărții..."></textarea>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg fw-bold">Adaugă Cartea în Bibliotecă</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<?php 
include "../includes/footer.php"; 
?>