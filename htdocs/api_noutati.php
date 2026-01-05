<?php
include 'includes/config.php';
// api_noutati.php

function getNoutatiLiterare($categorie = 'fiction') {
    // URL-ul API-ului Google Books pentru cărți noi dintr-o categorie
    $url = "https://www.googleapis.com/books/v1/volumes?q=subject:" . urlencode($categorie) . "&orderBy=newest&maxResults=3";

    // Preluăm conținutul (SURSA EXTERNĂ)
    $response = @file_get_contents($url);

    if ($response === FALSE) {
        return []; // Returnăm listă goală dacă API-ul nu răspunde
    }

    // Parsăm datele JSON
    $data = json_decode($response, true);
    
    $carti_modelate = [];
    if (isset($data['items'])) {
        foreach ($data['items'] as $item) {
            // Modelăm datele pentru a păstra doar ce ne interesează
            $carti_modelate[] = [
                'titlu' => $item['volumeInfo']['title'] ?? 'Titlu necunoscut',
                'autor' => $item['volumeInfo']['authors'][0] ?? 'Autor anonim',
                'imagine' => $item['volumeInfo']['imageLinks']['thumbnail'] ?? 'https://via.placeholder.com/128x192?text=Fara+Coperta',
                'link' => $item['volumeInfo']['infoLink'] ?? '#'
            ];
        }
    }
    return $carti_modelate;
}
?>