<?php
session_start();

include "../includes/config.php";

if (!isset($_SESSION['user_id'])) {
    die("Acces neautorizat. Te rugăm să te loghezi.");
}

if (ob_get_length()) ob_end_clean();

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Inventar_Stefblioteca_" . date('d-m-Y') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

$sql = "SELECT c.id_carte, c.titlu, a.nume AS autor, cat.nume_categorie AS categorie, 
               c.ISBN, c.editura, c.an_publicare, c.nr_exemplare
        FROM carti c
        LEFT JOIN autori a ON c.id_autor = a.id_autor
        LEFT JOIN categorii cat ON c.id_categorie = cat.id_categorie
        ORDER BY c.titlu ASC";

$result = $conn->query($sql);

echo '<table border="1">';
echo '<tr>
        <th style="background-color: #2c3e50; color: white;">ID</th>
        <th style="background-color: #2c3e50; color: white;">Titlu</th>
        <th style="background-color: #2c3e50; color: white;">Autor</th>
        <th style="background-color: #2c3e50; color: white;">Categorie</th>
        <th style="background-color: #2c3e50; color: white;">ISBN</th>
        <th style="background-color: #2c3e50; color: white;">Editura</th>
        <th style="background-color: #2c3e50; color: white;">An</th>
        <th style="background-color: #2c3e50; color: white;">Stoc</th>
      </tr>';

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id_carte'] . "</td>";
        echo "<td>" . htmlspecialchars($row['titlu']) . "</td>";
        echo "<td>" . htmlspecialchars($row['autor'] ?? 'Nespecificat') . "</td>";
        echo "<td>" . htmlspecialchars($row['categorie'] ?? 'General') . "</td>";
        // Adăugăm un mic stil pentru ISBN ca să nu fie transformat în format științific
        echo "<td style='vnd.ms-excel.numberformat:@'>" . htmlspecialchars($row['ISBN']) . "</td>";
        echo "<td>" . htmlspecialchars($row['editura']) . "</td>";
        echo "<td>" . $row['an_publicare'] . "</td>";
        echo "<td>" . $row['nr_exemplare'] . "</td>";
        echo "</tr>";
    }
}
echo '</table>';
exit;