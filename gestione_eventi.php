<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connection.php';

// Controllo di accesso: solo gli admin possono accedere
if (!isset($_SESSION['user']) || !isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'admin') {
    header('Location: homepage.php');
    exit();
}

$messaggio = '';
$errore = '';

// 1. GESTIONE AGGIUNTA DI UN NUOVO EVENTO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione']) && $_POST['azione'] === 'aggiungi') {
    $giorno = trim($_POST['giorno']);
    $mese = trim($_POST['mese']);
    $titolo = trim($_POST['titolo']);
    $luogo = trim($_POST['luogo']);

    if (!empty($giorno) && !empty($mese) && !empty($titolo)) {
        $stmt_ins = $conn->prepare("INSERT INTO `" . TAB_EVENTS . "` (giorno, mese, titolo, luogo) VALUES (?, ?, ?, ?)");
        if ($stmt_ins) {
            $stmt_ins->bind_param("ssss", $giorno, $mese, $titolo, $luogo);
            if ($stmt_ins->execute()) {
                $messaggio = "Evento aggiunto con successo!";
            } else {
                $errore = "Errore durante l'inserimento dell'evento.";
            }
            $stmt_ins->close();
        }
    } else {
        $errore = "Compila almeno il giorno, il mese e il titolo dell'evento.";
    }
}

// 2. GESTIONE ELIMINAZIONE DI UN EVENTO
if (isset($_GET['elimina'])) {
    $id_elimina = (int)$_GET['elimina'];
    $stmt_del = $conn->prepare("DELETE FROM `" . TAB_EVENTS . "` WHERE id = ?");
    if ($stmt_del) {
        $stmt_del->bind_param("i", $id_elimina);
        if ($stmt_del->execute()) {
            $messaggio = "Evento eliminato con successo!";
        } else {
            $errore = "Errore durante l'eliminazione dell'evento.";
        }
        $stmt_del->close();
    }
}

// 3. GESTIONE MODIFICA DI UN EVENTO ESISTENTE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione']) && $_POST['azione'] === 'modifica') {
    $id_mod = (int)$_POST['evento_id'];
    $giorno = trim($_POST['giorno']);
    $mese = trim($_POST['mese']);
    $titolo = trim($_POST['titolo']);
    $luogo = trim($_POST['luogo']);

    if (!empty($giorno) && !empty($mese) && !empty($titolo)) {
        $stmt_upd = $conn->prepare("UPDATE `" . TAB_EVENTS . "` SET giorno = ?, mese = ?, titolo = ?, luogo = ? WHERE id = ?");
        if ($stmt_upd) {
            $stmt_upd->bind_param("ssssi", $giorno, $mese, $titolo, $luogo, $id_mod);
            if ($stmt_upd->execute()) {
                $messaggio = "Evento aggiornato con successo!";
            } else {
                $errore = "Errore durante l'aggiornamento dell'evento.";
            }
            $stmt_upd->close();
        }
    } else {
        $errore = "I campi obbligatori non possono essere vuoti.";
    }
}

// Recupero di tutti gli eventi dal database
$res_eventi = $conn->query("SELECT * FROM `" . TAB_EVENTS . "` ORDER BY id ASC");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify - Gestione Eventi</title>
    <style type="text/css">
        .form-input {
            background-color: #181818;
            color: #ffffff;
            border: 1px solid #444;
            padding: 8px;
            border-radius: 4px;
            font-size: 12px;
            box-sizing: border-box;
        }
        .btn-green {
            background-color: #1db954;
            color: #000000;
            border: none;
            padding: 9px 18px;
            border-radius: 500px;
            font-weight: bold;
            font-size: 12px;
            cursor: pointer;
        }
        .btn-green:hover {
            background-color: #1ed760;
        }
        .btn-red {
            background-color: #e22134;
            color: #ffffff;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 11px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-red:hover {
            background-color: #ff334b;
        }
    </style>
</head>
<body style="background: linear-gradient(180deg, #1f1f1f 0%, #121212 40%, #0a0a0a 100%); background-attachment: fixed; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0; min-height: 100vh;">

    <?php include 'menu.php'; ?>

    <div style="margin-left: 230px; padding: 32px; max-width: 1400px; box-sizing: border-box;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h1 style="font-size: 24px; font-weight: bold; margin: 0;">Gestione Eventi & Tour Live</h1>
            <a href="eventi.php" style="color: #1db954; text-decoration: none; font-size: 13px; font-weight: bold;">← Torna agli Eventi</a>
        </div>

        <?php if (!empty($messaggio)): ?>
            <div style="background-color: rgba(29,185,84,0.15); border: 1px solid #1db954; color: #1db954; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 13px;">
                <?php echo htmlspecialchars($messaggio); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errore)): ?>
            <div style="background-color: rgba(226,33,52,0.15); border: 1px solid #e22134; color: #e22134; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 13px;">
                <?php echo htmlspecialchars($errore); ?>
            </div>
        <?php endif; ?>

        <!-- FORM PER AGGIUNGERE UN NUOVO EVENTO -->
        <div style="background-color: #181818; padding: 24px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05); margin-bottom: 30px;">
            <h3 style="margin-top: 0; font-size: 16px; color: #1db954; margin-bottom: 16px;">+ Aggiungi Nuovo Evento Live</h3>
            <form action="gestione_eventi.php" method="POST" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
                <input type="hidden" name="azione" value="aggiungi" />
                
                <div>
                    <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Giorno (es. 15)</label>
                    <input type="text" name="giorno" placeholder="15" class="form-input" style="width: 70px;" required="required" />
                </div>

                <div>
                    <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Mese (es. OTT)</label>
                    <input type="text" name="mese" placeholder="OTT" class="form-input" style="width: 80px;" required="required" />
                </div>

                <div style="flex-grow: 1; min-width: 200px;">
                    <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Titolo / Artista</label>
                    <input type="text" name="titolo" placeholder="Es. Luchè Live Tour" class="form-input" style="width: 100%;" required="required" />
                </div>

                <div style="flex-grow: 1; min-width: 220px;">
                    <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Luogo / Città / Arena</label>
                    <input type="text" name="luogo" placeholder="Palapartenope, Napoli" class="form-input" style="width: 100%;" />
                </div>

                <div>
                    <button type="submit" class="btn-green">Crea Evento</button>
                </div>
            </form>
        </div>

        <!-- TABELLA DI GESTIONE / MODIFICA ED ELIMINAZIONE -->
        <h3 style="font-size: 18px; margin-bottom: 16px;">Eventi Programmati in Lista</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; background-color: #181818; border-radius: 8px; overflow: hidden;">
            <thead>
                <tr style="border-bottom: 1px solid #333; color: #b3b3b3; font-size: 11px; text-transform: uppercase;">
                    <th style="padding: 12px;">ID</th>
                    <th style="padding: 12px;">Giorno</th>
                    <th style="padding: 12px;">Mese</th>
                    <th style="padding: 12px;">Titolo / Artista</th>
                    <th style="padding: 12px;">Luogo</th>
                    <th style="padding: 12px; text-align: center;">Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($res_eventi && $res_eventi->num_rows > 0): ?>
                    <?php while ($ev = $res_eventi->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #222;">
                            <form action="gestione_eventi.php" method="POST">
                                <input type="hidden" name="azione" value="modifica" />
                                <input type="hidden" name="evento_id" value="<?php echo $ev['id']; ?>" />
                                
                                <td style="padding: 14px; color: #888;"><?php echo $ev['id']; ?></td>
                                <td style="padding: 14px;">
                                    <input type="text" name="giorno" value="<?php echo htmlspecialchars($ev['giorno']); ?>" class="form-input" style="width: 60px; font-weight: bold; text-align: center;" required="required" />
                                </td>
                                <td style="padding: 14px;">
                                    <input type="text" name="mese" value="<?php echo htmlspecialchars($ev['mese']); ?>" class="form-input" style="width: 70px; text-transform: uppercase; text-align: center;" required="required" />
                                </td>
                                <td style="padding: 14px;">
                                    <input type="text" name="titolo" value="<?php echo htmlspecialchars($ev['titolo']); ?>" class="form-input" style="width: 220px; font-weight: bold;" required="required" />
                                </td>
                                <td style="padding: 14px;">
                                    <input type="text" name="luogo" value="<?php echo htmlspecialchars($ev['luogo']); ?>" class="form-input" style="width: 280px;" />
                                </td>
                                <td style="padding: 14px; text-align: center; white-space: nowrap;">
                                    <button type="submit" class="btn-green" style="padding: 6px 14px; margin-right: 6px;">Salva</button>
                                    <a href="gestione_eventi.php?elimina=<?php echo $ev['id']; ?>" class="btn-red" onclick="return confirm('Sei sicuro di voler eliminare questo evento?');">Elimina</a>
                                </td>
                            </form>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="padding: 20px; text-align: center; color: #888;">Nessun evento trovato nel database.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>

</body>
</html>
<?php 
if (isset($conn) && !$conn->connect_error) {
    $conn->close(); 
}
?>