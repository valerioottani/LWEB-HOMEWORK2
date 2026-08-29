<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connection.php';

// Controllo sicurezza: solo l'admin può accedere
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'admin') {
    die("<div style='background-color: #121212; color: #e91429; font-family: Arial; padding: 40px; text-align: center;'><h2>Accesso Negato</h2><p>Questa pagina è riservata esclusivamente agli amministratori.</p><a href='homepage.php' style='color: #1db954;'>Torna alla Home</a></div>");
}

$messaggio = "";

// Gestione Azioni del Database (Aggiunta Evento, Modifica Prezzi/Stato Merchandise)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $azione = isset($_POST['azione']) ? $_POST['azione'] : '';

    if ($azione === 'aggiungi_evento') {
        $titolo = $conn->real_escape_string($_POST['titolo']);
        $luogo = $conn->real_escape_string($_POST['luogo']);
        $giorno = (int)$_POST['giorno'];
        $mese = $conn->real_escape_string($_POST['mese']);
        
        $conn->query("INSERT INTO `" . TAB_EVENTS . "` (giorno, mese, titolo, luogo, link_biglietti) VALUES ($giorno, '$mese', '$titolo', '$luogo', '#')");
        $evento_id = $conn->insert_id;

        // Inserisce i posti di default per il nuovo evento
        $conn->query("INSERT INTO `posti_evento` (evento_id, settore, numero_posto, prezzo, occupato) VALUES 
            ($evento_id, 'Parterre Standing', 'PAR-1', 35.00, 0),
            ($evento_id, 'Tribuna VIP', 'VIP-1', 65.00, 0)");

        $messaggio = "Nuovo evento aggiunto correttamente al database!";
    }

    if ($azione === 'modifica_merch') {
        $merch_id = (int)$_POST['merch_id'];
        $prezzo = (float)$_POST['prezzo'];
        $disponibile = (int)$_POST['disponibile'];

        $conn->query("UPDATE `merchandise_album` SET prezzo = $prezzo, disponibile = $disponibile WHERE id = $merch_id");
        $messaggio = "Merchandise aggiornato con successo!";
    }
}

// Recupera i dati aggiornati dal database
$res_merch = $conn->query("SELECT m.*, a.titolo AS album_titolo FROM `merchandise_album` m JOIN `" . TAB_ALBUMS . "` a ON m.album_id = a.id");
$res_eventi = $conn->query("SELECT * FROM `" . TAB_EVENTS . "`");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify - Pannello Amministrazione DB</title>
    <style type="text/css">
        .admin-box {
            background-color: #181818;
            padding: 24px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.05);
            margin-bottom: 30px;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }
        .admin-table th, .admin-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            font-size: 13px;
        }
        .admin-table th {
            background-color: #222222;
            color: #b3b3b3;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 1px;
        }
        .form-control {
            background-color: #2a2a2a;
            border: 1px solid #444;
            color: #fff;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 13px;
            width: 100%;
            box-sizing: border-box;
            margin-bottom: 12px;
        }
        .btn-green {
            background-color: #1db954;
            color: #000;
            border: none;
            padding: 10px 20px;
            border-radius: 500px;
            font-weight: bold;
            font-size: 13px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        .btn-green:hover {
            background-color: #1ed760;
        }
    </style>
</head>
<body style="background: linear-gradient(180deg, #1f1f1f 0%, #121212 40%, #0a0a0a 100%); background-attachment: fixed; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0; min-height: 100vh;">

    <?php include 'menu.php'; ?>

    <div style="margin-left: 230px; padding: 32px; max-width: 1000px; box-sizing: border-box;">
        
        <h1 style="font-size: 32px; font-weight: 900; margin-bottom: 8px;">Pannello di Controllo Database</h1>
        <p style="color: #b3b3b3; margin-bottom: 28px; font-size: 14px;">Da questa sezione puoi amministrare i contenuti, inserire nuovi eventi live e modificare i listini del merchandise.</p>

        <?php if (!empty($messaggio)): ?>
            <div style="background-color: rgba(29,185,84,0.15); border: 1px solid #1db954; color: #1db954; padding: 12px 16px; border-radius: 6px; margin-bottom: 24px; font-size: 13px;">
                <?php echo htmlspecialchars($messaggio); ?>
            </div>
        <?php endif; ?>

        <!-- SEZIONE 1: Aggiunta Eventi -->
        <div class="admin-box">
            <h2 style="font-size: 18px; font-weight: bold; margin-top: 0; color: #ffffff; margin-bottom: 16px;">Aggiungi Nuovo Evento Live</h2>
            <form action="admin.php" method="POST">
                <input type="hidden" name="azione" value="aggiungi_evento" />
                <div style="display: flex; gap: 16px;">
                    <div style="flex: 2;">
                        <label style="font-size: 12px; color: #b3b3b3; display: block; margin-bottom: 4px;">Titolo Concerto / Tour</label>
                        <input type="text" name="titolo" class="form-control" required="required" placeholder="Es. Luchè Live" />
                    </div>
                    <div style="flex: 2;">
                        <label style="font-size: 12px; color: #b3b3b3; display: block; margin-bottom: 4px;">Luogo / Città</label>
                        <input type="text" name="luogo" class="form-control" required="required" placeholder="Es. Maradona, Napoli" />
                    </div>
                    <div style="flex: 1;">
                        <label style="font-size: 12px; color: #b3b3b3; display: block; margin-bottom: 4px;">Giorno</label>
                        <input type="number" name="giorno" class="form-control" required="required" min="1" max="31" placeholder="20" />
                    </div>
                    <div style="flex: 1;">
                        <label style="font-size: 12px; color: #b3b3b3; display: block; margin-bottom: 4px;">Mese</label>
                        <input type="text" name="mese" class="form-control" required="required" placeholder="AGO" />
                    </div>
                </div>
                <button type="submit" class="btn-green">Salva Nuovo Evento</button>
            </form>
        </div>

        <!-- SEZIONE 2: Modifica Merchandise -->
        <div class="admin-box">
            <h2 style="font-size: 18px; font-weight: bold; margin-top: 0; color: #ffffff; margin-bottom: 16px;">Modifica Prezzi & Disponibilità Merchandise</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Prodotto</th>
                        <th>Album Collegato</th>
                        <th>Prezzo (€)</th>
                        <th>Stato Magazzino</th>
                        <th>Salva</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($m = $res_merch->fetch_assoc()): ?>
                        <tr>
                            <form action="admin.php" method="POST">
                                <input type="hidden" name="azione" value="modifica_merch" />
                                <input type="hidden" name="merch_id" value="<?php echo (int)$m['id']; ?>" />
                                <td><strong><?php echo htmlspecialchars($m['tipo_prodotto']); ?></strong></td>
                                <td style="color: #b3b3b3;"><?php echo htmlspecialchars($m['album_titolo']); ?></td>
                                <td>
                                    <input type="text" name="prezzo" value="<?php echo htmlspecialchars($m['prezzo']); ?>" style="width: 80px; background: #2a2a2a; border: 1px solid #444; color: #fff; padding: 6px; border-radius: 4px;" />
                                </td>
                                <td>
                                    <select name="disponibile" style="background: #2a2a2a; border: 1px solid #444; color: #fff; padding: 6px; border-radius: 4px;">
                                        <option value="1" <?php echo ($m['disponibile'] == 1) ? 'selected="selected"' : ''; ?>>Disponibile</option>
                                        <option value="0" <?php echo ($m['disponibile'] == 0) ? 'selected="selected"' : ''; ?>>Sold Out (Esaurito)</option>
                                    </select>
                                </td>
                                <td>
                                    <button type="submit" style="background-color: #1db954; color: #000; border: none; padding: 8px 14px; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 11px;">Aggiorna</button>
                                </td>
                            </form>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>
<?php $conn->close(); ?>