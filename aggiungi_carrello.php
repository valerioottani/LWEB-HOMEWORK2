<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connection.php';

// Gestisce la richiesta di aggiunta al carrello inviata tramite metodo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $merch_id = isset($_POST['merch_id']) ? (int)$_POST['merch_id'] : 0;

    if ($merch_id > 0) {
        // Recupera dal database i dettagli del prodotto e il titolo dell'album associato
        $res = $conn->query("SELECT m.*, a.titolo AS album_titolo FROM `merchandise_album` m JOIN `" . TAB_ALBUMS . "` a ON m.album_id = a.id WHERE m.id = $merch_id");
        
        if ($res && $row = $res->fetch_assoc()) {
            if (!isset($_SESSION['carrello'])) {
                $_SESSION['carrello'] = [];
            }

            // Incrementa la quantità se il prodotto è già presente nel carrello, altrimenti lo aggiunge ex novo
            if (isset($_SESSION['carrello'][$merch_id])) {
                $_SESSION['carrello'][$merch_id]['quantita']++;
            } else {
                $_SESSION['carrello'][$merch_id] = [
                    'id' => $row['id'],
                    'tipo_prodotto' => $row['tipo_prodotto'],
                    'album_titolo' => $row['album_titolo'],
                    'prezzo' => (float)$row['prezzo'],
                    'quantita' => 1
                ];
            }
        } else {
            // Fallback di emergenza in caso di errore di recupero dal database
            if (!isset($_SESSION['carrello'])) {
                $_SESSION['carrello'] = [];
            }
            $_SESSION['carrello'][$merch_id] = [
                'id' => $merch_id,
                'tipo_prodotto' => 'Vinile / CD Selezionato',
                'album_titolo' => 'Album Ufficiale',
                'prezzo' => 29.90,
                'quantita' => 1
            ];
        }
    }
}
$conn->close();

// Reindirizza l'utente alla pagina di visualizzazione del carrello
header('Location: carrello.php');
exit;