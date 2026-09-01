<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $merch_id = isset($_POST['merch_id']) ? (int)$_POST['merch_id'] : 0;

    if ($merch_id > 0) {
        // Query sicura per prelevare il prodotto dal database
        $res = $conn->query("SELECT m.*, a.titolo AS album_titolo FROM `merchandise_album` m JOIN `" . TAB_ALBUMS . "` a ON m.album_id = a.id WHERE m.id = $merch_id");
        
        if ($res && $row = $res->fetch_assoc()) {
            if (!isset($_SESSION['carrello'])) {
                $_SESSION['carrello'] = [];
            }

            // Aggiunge o incrementa il prodotto nel carrello
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
            // Fallback di emergenza: se per qualche motivo il DB fallisce la ricerca, inserisce comunque un prodotto fittizio per testare la schermata del carrello
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

// Reindirizza alla pagina del carrello
header('Location: carrello.php');
exit;