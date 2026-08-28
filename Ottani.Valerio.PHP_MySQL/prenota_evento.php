<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connection.php';

$evento_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// Recupero dati evento
$res_ev = $conn->query("SELECT * FROM `" . TAB_EVENTS . "` WHERE id = $evento_id");
if ($res_ev && $res_ev->num_rows > 0) {
    $evento = $res_ev->fetch_assoc();
} else {
    die("Evento non trovato.");
}

// Gestione aggiunta posto al carrello
$messaggio = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posto_id = isset($_POST['posto_id']) ? (int)$_POST['posto_id'] : 0;
    
    // Verifica che il posto sia libero
    $res_p = $conn->query("SELECT * FROM `posti_evento` WHERE id = $posto_id AND occupato = 0");
    if ($res_p && $posto = $res_p->fetch_assoc()) {
        // Segna il posto come occupato nel database
        $conn->query("UPDATE `posti_evento` SET occupato = 1 WHERE id = $posto_id");

        if (!isset($_SESSION['carrello'])) {
            $_SESSION['carrello'] = [];
        }

        // Aggiunge il biglietto al carrello esistente
        $item_key = 'evento_' . $posto['id'];
        $_SESSION['carrello'][$item_key] = [
            'id' => $posto['id'],
            'tipo_prodotto' => 'Biglietto: ' . $evento['titolo'] . ' (' . $posto['settore'] . ' - Posto ' . $posto['numero_posto'] . ')',
            'album_titolo' => $evento['luogo'] . ' (' . $evento['giorno'] . ' ' . $evento['mese'] . ')',
            'prezzo' => (float)$posto['prezzo'],
            'quantita' => 1
        ];

        header('Location: carrello.php');
        exit;
    } else {
        $messaggio = "Spiacenti, questo posto è appena stato occupato da un altro utente!";
    }
}

// Recupera tutti i posti dell'evento
$res_posti = $conn->query("SELECT * FROM `posti_evento` WHERE evento_id = $evento_id ORDER BY settore, numero_posto");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify - Selezione Posto: <?php echo htmlspecialchars($evento['titolo']); ?></title>
    <style type="text/css">
        .seat-grid {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }
        .seat-box {
            padding: 14px 18px;
            border-radius: 6px;
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            box-sizing: border-box;
            width: 140px;
        }
        .seat-free {
            background-color: #181818;
            border: 1px solid #1db954;
            color: #ffffff;
            cursor: pointer;
            transition: transform 0.2s ease, background-color 0.2s ease;
        }
        .seat-free:hover {
            background-color: #1db954;
            color: #000000;
            transform: translateY(-2px);
        }
        .seat-busy {
            background-color: #222222;
            border: 1px solid #444444;
            color: #666666;
            cursor: not-allowed;
            opacity: 0.6;
        }
        .legend-box {
            display: inline-block;
            width: 14px;
            height: 14px;
            border-radius: 3px;
            margin-right: 6px;
            vertical-align: middle;
        }
    </style>
</head>
<body style="background: linear-gradient(180deg, #1f1f1f 0%, #121212 40%, #0a0a0a 100%); background-attachment: fixed; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0; min-height: 100vh;">

    <?php include 'menu.php'; ?>

    <div style="margin-left: 230px; padding: 32px; max-width: 1000px; box-sizing: border-box;">
        
        <div style="background-color: #181818; padding: 24px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05); margin-bottom: 30px;">
            <span style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: #1db954; letter-spacing: 1px;">Selezione Posto Live</span>
            <h1 style="font-size: 28px; font-weight: 900; margin: 8px 0; color: #ffffff;"><?php echo htmlspecialchars($evento['titolo']); ?></h1>
            <p style="font-size: 14px; color: #b3b3b3; margin: 0;"><?php echo htmlspecialchars($evento['luogo']); ?> • Data: <?php echo htmlspecialchars($evento['giorno'] . ' ' . $evento['mese']); ?></p>
        </div>

        <?php if (!empty($messaggio)): ?>
            <div style="background-color: rgba(233,20,41,0.15); border: 1px solid #e91429; color: #e91429; padding: 12px 16px; border-radius: 6px; margin-bottom: 24px; font-size: 13px;">
                <?php echo htmlspecialchars($messaggio); ?>
            </div>
        <?php endif; ?>

        <!-- Legenda -->
        <div style="display: flex; gap: 20px; margin-bottom: 24px; font-size: 13px; color: #b3b3b3; align-items: center;">
            <div><span class="legend-box" style="background-color: #1db954;"></span> Posto Disponibile (Clicca per comprare)</div>
            <div><span class="legend-box" style="background-color: #444444;"></span> Posto Occupato (Esaurito)</div>
        </div>

        <h2 style="font-size: 20px; font-weight: bold; margin-bottom: 16px; color: #ffffff;">Mappa Settori & Posti</h2>
        
        <div class="seat-grid">
            <?php if ($res_posti && $res_posti->num_rows > 0): ?>
                <?php while ($p = $res_posti->fetch_assoc()): ?>
                    <?php if ($p['occupato'] == 0): ?>
                        <!-- Posto Libero (Cliccabile) -->
                        <form action="prenota_evento.php?id=<?php echo $evento_id; ?>" method="POST" style="margin: 0;">
                            <input type="hidden" name="posto_id" value="<?php echo (int)$p['id']; ?>" />
                            <button type="submit" class="seat-box seat-free" title="Clicca per acquistare questo posto">
                                <span style="display: block; font-size: 11px; color: #b3b3b3; margin-bottom: 2px;"><?php echo htmlspecialchars($p['settore']); ?></span>
                                <span style="font-size: 15px; color: #1db954;"><?php echo htmlspecialchars($p['numero_posto']); ?></span>
                                <span style="display: block; font-size: 11px; color: #ffffff; margin-top: 4px;">€ <?php echo number_format($p['prezzo'], 2, ',', '.'); ?></span>
                            </button>
                        </form>
                    <?php else: ?>
                        <!-- Posto Occupato (Non cliccabile) -->
                        <div class="seat-box seat-busy">
                            <span style="display: block; font-size: 11px; color: #777777; margin-bottom: 2px;"><?php echo htmlspecialchars($p['settore']); ?></span>
                            <span style="font-size: 15px; color: #888888;"><?php echo htmlspecialchars($p['numero_posto']); ?></span>
                            <span style="display: block; font-size: 11px; color: #666666; margin-top: 4px;">Occupato</span>
                        </div>
                    <?php endif; ?>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #b3b3b3; font-size: 14px;">Nessun posto disponibile per questo evento.</p>
            <?php endif; ?>
        </div>

        <div style="margin-top: 30px;">
            <a href="eventi.php" style="color: #1db954; text-decoration: none; font-size: 13px; font-weight: bold;">← Torna all'elenco eventi</a>
        </div>

    </div>

</body>
</html>
<?php $conn->close(); ?>