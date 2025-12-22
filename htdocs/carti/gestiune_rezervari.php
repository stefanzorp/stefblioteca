<?php
include "../includes/config.php";

if (!isset($_SESSION['user_rol']) || ($_SESSION['user_rol'] !== 'bibliotecar' && $_SESSION['user_rol'] !== 'admin')) {
    die("Acces refuzat!");
}

if (isset($_GET['confirma_ridicare'])) {
    $id_rez = intval($_GET['confirma_ridicare']);
    
    $res = $conn->query("SELECT id_utilizator, id_carte FROM rezervari WHERE id_rezervare = $id_rez");
    if ($row = $res->fetch_assoc()) {
        $id_u = $row['id_utilizator'];
        $id_c = $row['id_carte'];
        $data_limita = date('Y-m-d', strtotime('+14 days')); // termen de 2 săptămâni

        $conn->begin_transaction();
        try {
            // insert in imprumuturi
            $stmt = $conn->prepare("INSERT INTO imprumuturi (id_utilizator, id_carte, data_imprumut, data_limita, status) VALUES (?, ?, CURDATE(), ?, 'activ')");
            $stmt->bind_param("iis", $id_u, $id_c, $data_limita);
            $stmt->execute();

            $conn->query("UPDATE rezervari SET status = 'finalizata' WHERE id_rezervare = $id_rez");

            $conn->commit();
            header("Location: gestiune_rezervari.php?msg=Cartea a fost ridicată!");
        } catch (Exception $e) {
            $conn->rollback();
            $msg_err = "Eroare: " . $e->getMessage();
        }
    }
}

if (isset($_GET['returneaza_imprumut'])) {
    $id_imp = intval($_GET['returneaza_imprumut']);
    
    $res = $conn->query("SELECT id_carte FROM imprumuturi WHERE id_imprumut = $id_imp");
    if ($row = $res->fetch_assoc()) {
        $id_c = $row['id_carte'];
        
        $conn->begin_transaction();
        try {

            $conn->query("UPDATE imprumuturi SET status = 'returnat', data_returnare = CURDATE() WHERE id_imprumut = $id_imp");
            
            // +1 stoc carte
            $conn->query("UPDATE carti SET nr_exemplare = nr_exemplare + 1 WHERE id_carte = $id_c");
            
            $conn->commit();
            header("Location: gestiune_rezervari.php?msg=Carte returnată cu succes!");
        } catch (Exception $e) {
            $conn->rollback();
        }
    }
}

$rezervari = $conn->query("SELECT r.id_rezervare, u.nume as cititor, c.titlu, r.data_rezervare 
                           FROM rezervari r 
                           JOIN utilizatori u ON r.id_utilizator = u.id_utilizator 
                           JOIN carti c ON r.id_carte = c.id_carte 
                           WHERE r.status = 'activa'");

$imprumuturi = $conn->query("SELECT i.id_imprumut, u.nume as cititor, c.titlu, i.data_imprumut, i.data_limita 
                             FROM imprumuturi i 
                             JOIN utilizatori u ON i.id_utilizator = u.id_utilizator 
                             JOIN carti c ON i.id_carte = c.id_carte 
                             WHERE i.status = 'activ'");
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Panou Bibliotecar - Stefblioteca</title>
    <style>
        body { font-family: sans-serif; background: #f0f2f5; padding: 20px; }
        .section { background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #34495e; color: white; }
        .btn { padding: 6px 12px; text-decoration: none; border-radius: 4px; color: white; font-weight: bold; }
        .btn-blue { background: #3498db; }
        .btn-green { background: #27ae60; }
        .msg { color: green; font-weight: bold; }
    </style>
</head>
<body>

    <h2>Panou Control Bibliotecar</h2>
    <a href="carti_list.php" style="display:inline-block; margin-bottom:20px;">← Înapoi la listă</a>
    
    <?php if(isset($_GET['msg'])) echo "<p class='msg'>".$_GET['msg']."</p>"; ?>

    <div class="section">
        <h3>1. Rezervări Online (Așteaptă ridicarea de la raft)</h3>
        <table>
            <thead>
                <tr>
                    <th>Cititor</th>
                    <th>Carte</th>
                    <th>Data Rezervării</th>
                    <th>Acțiune</th>
                </tr>
            </thead>
            <tbody>
                <?php while($r = $rezervari->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $r['cititor']; ?></td>
                    <td><?php echo $r['titlu']; ?></td>
                    <td><?php echo $r['data_rezervare']; ?></td>
                    <td>
                        <a href="gestiune_rezervari.php?confirma_ridicare=<?php echo $r['id_rezervare']; ?>" class="btn btn-blue">Confirmă Ridicarea</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>2. Împrumuturi Active (Cărți aflate la cititori)</h3>
        <table>
            <thead>
                <tr>
                    <th>Cititor</th>
                    <th>Carte</th>
                    <th>Data Împrumut</th>
                    <th>Termen Limită</th>
                    <th>Acțiune</th>
                </tr>
            </thead>
            <tbody>
                <?php while($i = $imprumuturi->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $i['cititor']; ?></td>
                    <td><?php echo $i['titlu']; ?></td>
                    <td><?php echo $i['data_imprumut']; ?></td>
                    <td style="color:red; font-weight:bold;"><?php echo $i['data_limita']; ?></td>
                    <td>
                        <a href="gestiune_rezervari.php?returneaza_imprumut=<?php echo $i['id_imprumut']; ?>" class="btn btn-green">Marchează Returnat</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>
</html>