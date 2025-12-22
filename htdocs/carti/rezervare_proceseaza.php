<?php
include "../includes/config.php"; 

if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'cititor') {
    die("Acces neautorizat. Trebuie să fii logat ca cititor.");
}

$token = $_GET['token'] ?? '';
//verific token csrf
if (empty($token) || $token !== $_SESSION['csrf_token']) {
    die("Eroare de securitate: Cerere invalidă.");
}

$id_carte = intval($_GET['id'] ?? 0);
// scot prefix pt id si transf in nr intreg
$id_utilizator = intval(str_replace("id_", "", $_SESSION['user_id']));

if ($id_carte > 0) {
    $conn->begin_transaction();
    try {
        $check = $conn->prepare("SELECT nr_exemplare FROM carti WHERE id_carte = ? FOR UPDATE");
        $check->bind_param("i", $id_carte);
        $check->execute();
        $res = $check->get_result()->fetch_assoc();

        if ($res && $res['nr_exemplare'] > 0) {
            $update = $conn->prepare("UPDATE carti SET nr_exemplare = nr_exemplare - 1 WHERE id_carte = ?");
            $update->bind_param("i", $id_carte);
            $update->execute();

            $stmt = $conn->prepare("INSERT INTO rezervari (id_utilizator, id_carte, data_rezervare, status) VALUES (?, ?, NOW(), 'activa')");
            $stmt->bind_param("ii", $id_utilizator, $id_carte);
            $stmt->execute();

            $conn->commit();
            header("Location: carti_list.php?msg=Rezervare efectuată cu succes!");
        } else {
            throw new Exception("Nu mai sunt exemplare disponibile.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: carti_list.php?eroare=" . urlencode($e->getMessage()));
    }
} else {
    header("Location: carti_list.php");
}
exit;