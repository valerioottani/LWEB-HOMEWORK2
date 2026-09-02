<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connection.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$username_corrente = $_SESSION['user'];

// 1. Recupero Storico Acquisti Merchandise (Filtrato per utente)
$acquisti_merch = [];
$stmt_m = $conn->prepare("SELECT acq.id AS ordine_id, acq.data_acquisto, acq.quantita, m.tipo_prodotto, m.prezzo, m.immagine_prodotto, alb.titolo AS album_titolo 
                          FROM `acquisti_merch` acq 
                          JOIN `merchandise_album` m ON acq.merchandise_id = m.id 
                          JOIN `" . TAB_ALBUMS . "` alb ON m.album_id = alb.id 
                          WHERE acq.username = ? 
                          ORDER BY acq.data_acquisto DESC");
if ($stmt_m) {
    $stmt_m->bind_param("s", $username_corrente);
    $stmt_m->execute();
    $res_m = $stmt_m->get_result();
    while ($row = $res_m->fetch_assoc()) {
        $acquisti_merch[] = $row;
    }
    $stmt_m->close();
}

// 2. Recupero Storico Biglietti Eventi Live (Filtrato per utente)
$acquisti_eventi = [];
$stmt_e = $conn->prepare("SELECT p.id AS prenotazione_id, p.settore, p.numero_posto, p.prezzo, e.titolo AS evento_titolo, e.giorno, e.mese, e.luogo 
                          FROM `posti_evento` p 
                          JOIN `" . TAB_EVENTS . "` e ON p.evento_id = e.id 
                          WHERE p.occupato = 1 AND p.username = ? 
                          ORDER BY p.id DESC");
if ($stmt_e) {
    $stmt_e->bind_param("s", $username_corrente);
    $stmt_e->execute();
    $res_e = $stmt_e->get_result();
    while ($row = $res_e->fetch_assoc()) {
        $acquisti_eventi[] = $row;
    }
    $stmt_e->close();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify - Storico Acquisti e Ricevute</title>
    <style type="text/css">
        .table-storico {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 6px;
            background-color: transparent;
            margin-bottom: 30px;
        }
        .table-storico th {
            padding: 10px 16px;
            text-align: left;
            background-color: transparent;
            color: #b3b3b3;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 1px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .table-storico td {
            padding: 14px 16px;
            font-size: 14px;
        }
        /* Effetto in rilievo al passaggio del cursore sulle righe di entrambe le tabelle */
        .acq-row {
            background-color: #181818;
            transition: background-color 0.25s ease, transform 0.2s ease, box-shadow 0.2s ease;
            border: 1px solid rgba(255,255,255,0.03);
        }
        .acq-row:hover {
            background-color: #2a2a2a !important;
            transform: scale(1.01);
            box-shadow: 0 6px 20px rgba(0,0,0,0.7);
            position: relative;
            z-index: 2;
        }
        .btn-ricevuta {
            background-color: #1db954;
            color: #000000;
            padding: 6px 14px;
            border-radius: 500px;
            font-weight: bold;
            font-size: 11px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.2s ease;
            white-space: nowrap;
        }
        .btn-ricevuta:hover {
            background-color: #1ed760;
        }
    </style>
</head>
<body style="background: linear-gradient(180deg, #1f1f1f 0%, #121212 40%, #0a0a0a 100%); background-attachment: fixed; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0; min-height: 100vh;">

    <?php include 'menu.php'; ?>

    <div style="margin-left: 230px; padding: 32px; max-width: 1200px; box-sizing: border-box;">
        
        <h1 style="font-size: 28px; font-weight: 900; margin-bottom: 8px;">📦 Storico Acquisti & Ricevute</h1>
        <p style="color: #b3b3b3; font-size: 14px; margin-bottom: 30px;">Visualizza l'elenco completo di tutti i prodotti di merchandise e i biglietti acquistati con il tuo account.</p>

        <!-- SEZIONE MERCHANDISE -->
        <h2 style="font-size: 18px; font-weight: bold; margin-bottom: 14px; color: #1db954;">Articoli Merchandise</h2>
        <?php if (!empty($acquisti_merch)): ?>
            <table class="table-storico">
                <thead>
                    <tr>
                        <th style="width: 50px;">Prodotto</th>
                        <th>Articolo / Tipologia</th>
                        <th>Album Riferimento</th>
                        <th>Quantità</th>
                        <th>Data Ordine</th>
                        <th>Prezzo Totale</th>
                        <th style="text-align: center;">Ricevuta</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($acquisti_merch as $item): 
                        $totale_articolo = $item['prezzo'] * $item['quantita'];
                    ?>
                        <tr class="acq-row">
                            <td style="border-top-left-radius: 6px; border-bottom-left-radius: 6px;">
                                <img src="img/<?php echo htmlspecialchars($item['immagine_prodotto']); ?>" alt="" style="width: 36px; height: 36px; border-radius: 4px; object-fit: cover; background-color: #222;" />
                            </td>
                            <td><strong style="color: #fff;"><?php echo htmlspecialchars($item['tipo_prodotto']); ?></strong></td>
                            <td style="color: #b3b3b3;"><?php echo htmlspecialchars($item['album_titolo']); ?></td>
                            <td><?php echo (int)$item['quantita']; ?></td>
                            <td style="color: #888; font-size: 12px;"><?php echo htmlspecialchars($item['data_acquisto']); ?></td>
                            <td style="font-weight: bold; color: #1db954;">€ <?php echo number_format($totale_articolo, 2, ',', '.'); ?></td>
                            <td style="text-align: center; border-top-right-radius: 6px; border-bottom-right-radius: 6px;">
                                <a href="ricevuta_ordine.php?id=<?php echo $item['ordine_id']; ?>" target="_blank" class="btn-ricevuta">📄 Ricevuta</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="background-color: #181818; padding: 24px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05); margin-bottom: 30px;">
                <p style="color: #b3b3b3; font-size: 13px; margin: 0;">Non hai ancora effettuato alcun acquisto di merchandise.</p>
            </div>
        <?php endif; ?>

        <!-- SEZIONE BIGLIETTI EVENTI LIVE -->
        <h2 style="font-size: 18px; font-weight: bold; margin-bottom: 14px; color: #1db954;">Biglietti & Eventi Live</h2>
        <?php if (!empty($acquisti_eventi)): ?>
            <table class="table-storico">
                <thead>
                    <tr>
                        <th>Evento Live</th>
                        <th>Luogo</th>
                        <th>Settore & Posto</th>
                        <th>Data Evento</th>
                        <th>Prezzo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($acquisti_eventi as $ev): ?>
                        <tr class="acq-row">
                            <td style="border-top-left-radius: 6px; border-bottom-left-radius: 6px;"><strong style="color: #fff;"><?php echo htmlspecialchars($ev['evento_titolo']); ?></strong></td>
                            <td style="color: #b3b3b3;"><?php echo htmlspecialchars($ev['luogo']); ?></td>
                            <td><?php echo htmlspecialchars($ev['settore'] . ' - Posto ' . $ev['numero_posto']); ?></td>
                            <td style="color: #888; font-size: 12px; border-top-right-radius: 6px; border-bottom-right-radius: 6px;"><?php echo htmlspecialchars($ev['giorno'] . ' ' . $ev['mese']); ?></td>
                            <td style="font-weight: bold; color: #1db954;">€ <?php echo number_format($ev['prezzo'], 2, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="background-color: #181818; padding: 24px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05);">
                <p style="color: #b3b3b3; font-size: 13px; margin: 0;">Nessun biglietto per eventi live registrato al momento.</p>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>
<?php 
if (isset($conn) && !$conn->connect_error) {
    $conn->close(); 
}
?>