<?php
// Dezactivam afisarea erorilor in browser pentru a nu corupe PDF-ul
ini_set('display_errors', 0);
error_reporting(0);

require('../libs/fpdf/fpdf.php');
include "../includes/config.php";

if (!isset($_SESSION['user_id'])) {
    die("Acces neautorizat.");
}

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'STEBLIOTECA - Raport Inventar Carti', 0, 1, 'C');
        $this->Ln(5);
        
        $this->SetFillColor(44, 62, 80);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 10);
        
        $this->Cell(10, 10, 'ID', 1, 0, 'C', true);
        $this->Cell(80, 10, 'Titlu', 1, 0, 'C', true);
        $this->Cell(50, 10, 'Autor', 1, 0, 'C', true);
        $this->Cell(45, 10, 'ISBN', 1, 1, 'C', true);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$sql = "SELECT c.id_carte, c.titlu, a.nume AS autor, c.ISBN 
        FROM carti c 
        LEFT JOIN autori a ON c.id_autor = a.id_autor";
$result = $conn->query($sql);

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0, 0, 0);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $pdf->Cell(10, 8, $row['id_carte'], 1);
        
        // REPARARE DEPRECATED: folosim iconv in loc de utf8_decode
        $titlu = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $row['titlu']);
        $autor = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $row['autor'] ?? 'Nespecificat');
        
        $pdf->Cell(80, 8, substr($titlu, 0, 45), 1);
        $pdf->Cell(50, 8, substr($autor, 0, 25), 1);
        $pdf->Cell(45, 8, $row['ISBN'], 1);
        $pdf->Ln();
    }
}

// CURATARE BUFFER: Stergem orice text/avertisment trimis de PHP inainte de PDF
if (ob_get_length()) ob_end_clean();

$pdf->Output('D', 'Inventar_Stefblioteca.pdf');
exit;