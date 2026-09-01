<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connection.php';

if (!isset($_SESSION['user']) || !isset($_GET['id'])) {
    header('Location: homepage.php');
    exit();
}

$username_corrente = $_SESSION['user'];
$ordine_id = (int)$_GET['id'];
$metodo_pagamento = isset($_GET['metodo']) ? htmlspecialchars($_GET['metodo']) : 'Carta di Credito';
$ultime_cifre = isset($_GET['carta']) ? htmlspecialchars($_GET['carta']) : '••••';

// Recupero dati dell'ordine, del prodotto e dell'utente dal database
$stmt = $conn->prepare("SELECT acq.*, m.tipo_prodotto, m.prezzo, m.immagine_prodotto, alb.titolo AS album_titolo, art.nome AS artista_nome, u.username, u.nome_completo, u.indirizzo 
                        FROM `acquisti_merch` acq 
                        JOIN `merchandise_album` m ON acq.merchandise_id = m.id 
                        JOIN `" . TAB_ALBUMS . "` alb ON m.album_id = alb.id 
                        JOIN `" . TAB_ARTISTS . "` art ON alb.artista_id = art.id 
                        JOIN `" . TAB_USERS . "` u ON acq.username = u.username 
                        WHERE acq.id = ? AND acq.username = ?");
$stmt->bind_param("is", $ordine_id, $username_corrente);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $row = $res->fetch_assoc()) {
    $prezzo_unitario = (float)$row['prezzo'];
    $quantita = (int)$row['quantita'];
    $totale = $prezzo_unitario * $quantita;
} else {
    die("Ordine non trovato o accesso non autorizzato.");
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify - Ricevuta Ordine #<?php echo $row['id']; ?></title>
    <style type="text/css">
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 40px;
        }
        .receipt-container {
            max-width: 700px;
            margin: 0 auto;
            background-color: #181818;
            padding: 40px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 10px 30px rgba(0,0,0,0.8);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #282828;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo-text {
            font-size: 24px;
            font-weight: 900;
            color: #1db954;
            letter-spacing: -1px;
        }
        .order-id {
            font-size: 14px;
            color: #b3b3b3;
            text-align: right;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #1db954;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            border-bottom: 1px solid #333;
            padding-bottom: 4px;
        }
        .info-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 20px;
        }
        .info-box {
            flex: 1;
            background-color: #222;
            padding: 16px;
            border-radius: 8px;
            font-size: 13px;
            line-height: 1.6;
            color: #b3b3b3;
        }
        .info-box strong {
            color: #ffffff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background-color: #222;
            color: #b3b3b3;
            font-size: 12px;
            text-transform: uppercase;
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #333;
        }
        td {
            padding: 14px 12px;
            font-size: 14px;
            border-bottom: 1px solid #222;
        }
        .total-section {
            background-color: #222;
            padding: 20px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .total-label {
            font-size: 16px;
            font-weight: bold;
        }
        .total-amount {
            font-size: 24px;
            font-weight: 900;
            color: #1db954;
        }
        .btn-print {
            background-color: #1db954;
            color: #000000;
            border: none;
            padding: 14px 28px;
            border-radius: 500px;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.2s ease;
        }
        .btn-print:hover {
            background-color: #1ed760;
        }
        @media print {
            body {
                background-color: #ffffff;
                color: #000000;
                padding: 0;
            }
            .receipt-container {
                background-color: #ffffff;
                border: none;
                box-shadow: none;
                padding: 0;
                color: #000000;
            }
            .info-box {
                background-color: #f9f9f9;
                color: #333;
                border: 1px solid #ddd;
            }
            .info-box strong {
                color: #000;
            }
            th {
                background-color: #eee;
                color: #333;
            }
            td {
                border-bottom: 1px solid #ddd;
                color: #000;
            }
            .total-section {
                background-color: #f9f9f9;
                border: 1px solid #ddd;
            }
            .total-amount {
                color: #000;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="receipt-container">
        
        <!-- Intestazione Ricevuta -->
        <div class="header">
            <div>
                <div class="logo-text">🟢 Spotify Official Store</div>
                <p style="color: #b3b3b3; font-size: 12px; margin: 4px 0 0 0;">Ricevuta di Conferma Ordine & Pagamento</p>
            </div>
            <div class="order-id">
                <strong>Ordine #<?php echo $row['id']; ?></strong><br />
                <span style="font-size: 11px; color: #888;"><?php echo $row['data_acquisto']; ?></span>
            </div>
        </div>

        <!-- Sezione Dati Cliente e Transazione -->
        <div class="info-grid">
            <div class="info-box">
                <div class="section-title" style="margin-top: 0;">Dati di Spedizione</div>
                <strong>Intestatario:</strong> <?php echo !empty($row['nome_completo']) ? htmlspecialchars($row['nome_completo']) : htmlspecialchars($row['username']); ?><br />
                <strong>Indirizzo:</strong> <?php echo !empty($row['indirizzo']) ? htmlspecialchars($row['indirizzo']) : 'Non specificato'; ?><br />
                <strong>Account:</strong> <?php echo htmlspecialchars($row['username']); ?>
            </div>
            <div class="info-box">
                <div class="section-title" style="margin-top: 0;">Dettagli Pagamento</div>
                <strong>Stato:</strong> Confermato e Saldato ✅<br />
                <strong>Metodo:</strong> <?php echo strtoupper($metodo_pagamento); ?><?php echo ($metodo_pagamento === 'carta') ? ' (**** ' . $ultime_cifre . ')' : ''; ?><br />
                <strong>Corriere:</strong> Espresso Tracciato
            </div>
        </div>

        <!-- Tabella Articoli -->
        <div class="section-title">Riepilogo Articoli</div>
        <table>
            <thead>
                <tr>
                    <th>Prodotto</th>
                    <th>Album / Artista</th>
                    <th style="text-align: center;">Q.tà</th>
                    <th style="text-align: right;">Prezzo Unit.</th>
                    <th style="text-align: right;">Totale</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['tipo_prodotto']); ?></strong></td>
                    <td style="color: #b3b3b3; font-size: 13px;"><?php echo htmlspecialchars($row['album_titolo']); ?> (<?php echo htmlspecialchars($row['artista_nome']); ?>)</td>
                    <td style="text-align: center;"><?php echo $quantita; ?></td>
                    <td style="text-align: right;">€ <?php echo number_format($prezzo_unitario, 2, ',', '.'); ?></td>
                    <td style="text-align: right; font-weight: bold; color: #1db954;">€ <?php echo number_format($totale, 2, ',', '.'); ?></td>
                </tr>
            </tbody>
        </table>

        <!-- Totale Complessivo -->
        <div class="total-section">
            <span class="total-label">Totale Complessivo Pagato:</span>
            <span class="total-amount">€ <?php echo number_format($totale, 2, ',', '.'); ?></span>
        </div>

        <!-- Pulsanti Azione (Nascosti in stampa) -->
        <div class="no-print" style="text-align: center; display: flex; justify-content: center; gap: 16px;">
            <button onclick="window.print();" class="btn-print">🖨️ Stampa / Salva come PDF</button>
            <a href="discografia.php" style="background-color: #282828; color: #ffffff; padding: 14px 28px; border-radius: 500px; font-weight: bold; font-size: 14px; text-decoration: none; display: inline-block;">Torna allo Store</a>
        </div>

    </div>

</body>
</html>