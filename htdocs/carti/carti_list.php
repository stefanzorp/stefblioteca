<?php
include "../includes/config.php";
include "../includes/header.php";

$rol = $_SESSION["user_rol"] ?? 'vizitator'; 
$nume_utilizator = $_SESSION["user_name"] ?? 'Oaspete';
$este_logat = (isset($_SESSION['user_id']) && !empty($_SESSION['user_id']));

$sql = "SELECT c.id_carte, c.titlu, a.nume AS autor, cat.nume_categorie AS categorie, 
               c.ISBN, c.editura, c.an_publicare, c.nr_exemplare
        FROM carti c
        LEFT JOIN autori a ON c.id_autor = a.id_autor
        LEFT JOIN categorii cat ON c.id_categorie = cat.id_categorie
        ORDER BY c.id_carte DESC";

$result = $conn->query($sql);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark mb-0">📚 Catalog Bibliotecă</h2>
        <div class="text-end">
            <span class="badge bg-light text-dark border p-2">
                👤 <?php echo htmlspecialchars($nume_utilizator); ?> 
                <span class="badge bg-warning text-dark ms-1"><?php echo strtoupper($rol); ?></span>
            </span>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body d-flex flex-wrap gap-2 align-items-center">
            <a href="../index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-house"></i> Acasă</a>
            
            <?php if ($rol === 'cititor'): ?>
                <a href="rezervarile_mele.php" class="btn btn-primary btn-sm"><i class="bi bi-bookmark-heart"></i> Rezervările Mele</a>
            <?php endif; ?>

            <?php if ($rol === 'bibliotecar' || $rol === 'admin'): ?>
                <a href="gestiune_rezervari.php" class="btn btn-dark btn-sm"><i class="bi bi-gear-fill text-warning"></i> Gestiune Împrumuturi</a>
                <a href="carti_add.php" class="btn btn-success btn-sm"><i class="bi bi-plus-lg"></i> Adaugă Carte</a>
            <?php endif; ?>

            <?php if ($rol === 'admin'): ?>
                <a href="admin_utilizatori.php" class="btn btn-info btn-sm text-white"><i class="bi bi-people"></i> Utilizatori</a>
            <?php endif; ?>

            <div class="ms-md-auto d-flex gap-2">
                <a href="export_pdf.php" target="_blank" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </a>
                <a href="export_excel.php" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Titlu & Detalii</th>
                        <th>Autor</th>
                        <th>Categorie</th>
                        <th>ISBN / Editură</th>
                        <th class="text-center">Stoc</th>
                        <th class="text-end pe-3">Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): 
                            $stoc = (int)$row['nr_exemplare'];
                            $clasa_stoc = ($stoc <= 0) ? 'bg-danger' : (($stoc < 3) ? 'bg-warning text-dark' : 'bg-success');
                        ?>
                            <tr>
                                <td class="ps-3 text-muted">#<?php echo $row['id_carte']; ?></td>
                                <td>
                                    <div class="fw-bold text-primary"><?php echo htmlspecialchars($row['titlu']); ?></div>
                                    <small class="text-muted">An: <?php echo $row['an_publicare']; ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($row['autor'] ?? 'Nespecificat'); ?></td>
                                <td><span class="badge bg-info text-dark opacity-75"><?php echo htmlspecialchars($row['categorie'] ?? 'General'); ?></span></td>
                                <td>
                                    <div class="small"><strong>ISBN:</strong> <?php echo htmlspecialchars($row['ISBN']); ?></div>
                                    <div class="small text-muted"><em><?php echo htmlspecialchars($row['editura']); ?></em></div>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill <?php echo $clasa_stoc; ?> shadow-sm px-3">
                                        <?php echo $stoc; ?>
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group shadow-sm">
                                        <a href="recenzii.php?id_carte=<?php echo $row['id_carte']; ?>" class="btn btn-sm btn-outline-secondary" title="Recenzii">
                                            <i class="bi bi-chat-text"></i>
                                        </a>

                                        <?php if ($rol === 'admin' || $rol === 'bibliotecar'): ?>
                                            <a href="carti_edit.php?id=<?php echo $row['id_carte']; ?>" class="btn btn-sm btn-outline-primary" title="Editează">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="carti_delete.php?id=<?php echo $row['id_carte']; ?>&token=<?php echo $_SESSION['csrf_token']; ?>" 
                                               class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Sigur dorești să ștergi această carte?')" title="Șterge">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($rol === 'cititor' && $este_logat): ?>
                                            <?php if ($stoc > 0): ?>
                                                <a href="rezervare_proceseaza.php?id=<?php echo $row['id_carte']; ?>&token=<?php echo $_SESSION['csrf_token']; ?>" 
                                                   class="btn btn-sm btn-success">
                                                   Rezervă
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-secondary disabled">Epuizat</button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">Nu există cărți în baza de date.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
include "../includes/footer.php"; 
?>