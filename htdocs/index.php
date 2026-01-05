<?php
session_start();
include "includes/config.php";
include "includes/header.php"; 
require_once 'api_noutati.php';

$noutati = getNoutatiLiterare('romance'); 

// Statistici pentru grafic
$sql_statistici = "SELECT cat.nume_categorie, COUNT(c.id_carte) as total 
                   FROM categorii cat 
                   LEFT JOIN carti c ON cat.id_categorie = c.id_categorie 
                   GROUP BY cat.id_categorie";
$rezultat_stats = $conn->query($sql_statistici);

$etichete = []; $valori = [];
if ($rezultat_stats->num_rows > 0) {
    while($row = $rezultat_stats->fetch_assoc()) {
        $etichete[] = $row['nume_categorie'];
        $valori[] = (int)$row['total'];
    }
} else {
    $etichete = ['Fără date']; $valori = [0];
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-body text-center">
                <?php if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] != ""): ?>
                    <?php 
                        $rol = $_SESSION["user_rol"] ?? 'cititor'; 
                        $nume = $_SESSION["user_name"] ?? 'Utilizator';
                    ?>
                    <div class="mb-3">
                        <img src="https://ui-avatars.com/api/?name=<?php echo $nume; ?>&background=random" class="rounded-circle mb-2" width="80">
                        <h4><?php echo htmlspecialchars($nume); ?></h4>
                        <span class="badge bg-primary text-uppercase"><?php echo htmlspecialchars($rol); ?></span>
                    </div>
                    
                    <div class="list-group">
                        <a href="carti/carti_list.php" class="list-group-item list-group-item-action">🔍 Vizualizare Cărți</a>
                        <?php if ($rol === 'cititor'): ?>
                            <a href="carti/rezervarile_mele.php" class="list-group-item list-group-item-action">📖 Rezervările Mele</a>
                        <?php endif; ?>
                        <?php if ($rol === 'bibliotecar' || $rol === 'admin'): ?>
                            <a href="carti/gestiune_rezervari.php" class="list-group-item list-group-item-action list-group-item-warning">📋 Gestiune Împrumuturi</a>
                        <?php endif; ?>
                        <?php if ($rol === 'admin'): ?>
                            <a href="carti/admin_utilizatori.php" class="list-group-item list-group-item-action list-group-item-danger">👥 Administrare Utilizatori</a>
                        <?php endif; ?>
                        <a href="contact.php" class="list-group-item list-group-item-action list-group-item-success">📧 Contact și Suport</a>
                        <a href="logout.php" class="list-group-item list-group-item-action text-danger fw-bold">🚪 Ieșire</a>
                    </div>

                <?php else: ?>
                    <h4>Vizitator</h4>
                    <p class="text-muted small">Autentifică-te pentru a rezerva cărți.</p>
                    <div class="d-grid gap-2">
                        <a href="login.php" class="btn btn-primary">Login</a>
                        <a href="register.php" class="btn btn-outline-success">Register</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title text-center">📊 Statistici Categorii</h5>
                <canvas id="stefChart" style="max-height: 250px;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">🌍 Recomandări Globale (Google Books)</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($noutati)): ?>
                    <div class="row">
                    <?php foreach ($noutati as $c): ?>
                        <div class="col-12 mb-3">
                            <div class="d-flex align-items-center p-2 border rounded bg-light">
                                <img src="<?php echo $c['imagine']; ?>" class="rounded shadow-sm me-3" style="width: 60px; height: 90px; object-fit: cover;">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($c['titlu']); ?></h6>
                                    <p class="small text-muted mb-1"><?php echo htmlspecialchars($c['autor']); ?></p>
                                    <a href="<?php echo $c['link']; ?>" target="_blank" class="btn btn-sm btn-link p-0 text-decoration-none">Vezi pe Google Books →</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted">Nu s-au putut încărca noutățile.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('stefChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($etichete); ?>,
            datasets: [{
                data: <?php echo json_encode($valori); ?>,
                backgroundColor: ['#3498db', '#2ecc71', '#e74c3c', '#f1c40f', '#9b59b6', '#1abc9c'],
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } }
        }
    });
</script>

<?php include "includes/footer.php"; ?>