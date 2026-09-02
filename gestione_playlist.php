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

// 1. GESTIONE AGGIUNTA DI UNA NUOVA PLAYLIST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione']) && $_POST['azione'] === 'aggiungi') {
    $titolo = trim($_POST['titolo']);
    $descrizione = trim($_POST['descrizione']);
    $immagine = trim($_POST['immagine']);
    $sfondo = 'linear-gradient(135deg, #1e3264 0%, #000000 100%)'; // Sfondo predefinito di default
    $tipo = 'playlist'; // Forzato sempre a playlist

    if (!empty($titolo)) {
        $stmt_ins = $conn->prepare("INSERT INTO `playlist` (titolo, descrizione, immagine, sfondo, tipo) VALUES (?, ?, ?, ?, ?)");
        if ($stmt_ins) {
            $stmt_ins->bind_param("sssss", $titolo, $descrizione, $immagine, $sfondo, $tipo);
            if ($stmt_ins->execute()) {
                $messaggio = "Playlist aggiunta con successo!";
            } else {
                $errore = "Errore durante l'inserimento della playlist.";
            }
            $stmt_ins->close();
        }
    } else {
        $errore = "Il titolo della playlist è obbligatorio.";
    }
}

// 2. GESTIONE ELIMINAZIONE DI UNA PLAYLIST
if (isset($_GET['elimina'])) {
    $id_elimina = (int)$_GET['elimina'];
    $stmt_del = $conn->prepare("DELETE FROM `playlist` WHERE id = ?");
    if ($stmt_del) {
        $stmt_del->bind_param("i", $id_elimina);
        if ($stmt_del->execute()) {
            $messaggio = "Playlist eliminata con successo!";
        } else {
            $errore = "Errore durante l'eliminazione della playlist.";
        }
        $stmt_del->close();
    }
}

// 3. GESTIONE MODIFICA DI UNA PLAYLIST ESISTENTE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione']) && $_POST['azione'] === 'modifica') {
    $id_mod = (int)$_POST['playlist_id'];
    $titolo = trim($_POST['titolo']);
    $descrizione = trim($_POST['descrizione']);
    $immagine = trim($_POST['immagine']);
    $tipo = 'playlist'; // Forzato sempre a playlist

    if (!empty($titolo)) {
        $stmt_upd = $conn->prepare("UPDATE `playlist` SET titolo = ?, descrizione = ?, immagine = ?, tipo = ? WHERE id = ?");
        if ($stmt_upd) {
            $stmt_upd->bind_param("ssssi", $titolo, $descrizione, $immagine, $tipo, $id_mod);
            if ($stmt_upd->execute()) {
                $messaggio = "Playlist aggiornata con successo!";
            } else {
                $errore = "Errore durante l'aggiornamento della playlist.";
            }
            $stmt_upd->close();
        }
    } else {
        $errore = "Il titolo non può essere vuoto.";
    }
}

// Recupero di tutte le playlist dal database
$res_playlist = $conn->query("SELECT * FROM `playlist` ORDER BY id ASC");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify - Gestione Playlist</title>
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

    <div style="margin-left: 230px; padding: 32px; max-width: 1300px; box-sizing: border-box;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h1 style="font-size: 24px; font-weight: bold; margin: 0;">Gestione Playlist in Evidenza</h1>
            <a href="homepage.php" style="color: #1db954; text-decoration: none; font-size: 13px; font-weight: bold;">← Torna alla Homepage</a>
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

        <!-- FORM PER AGGIUNGERE UNA NUOVA PLAYLIST -->
        <div style="background-color: #181818; padding: 24px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05); margin-bottom: 30px;">
            <h3 style="margin-top: 0; font-size: 16px; color: #1db954; margin-bottom: 16px;">+ Aggiungi Nuova Playlist</h3>
            <form action="gestione_playlist.php" method="POST" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
                <input type="hidden" name="azione" value="aggiungi" />
                <div>
                    <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Titolo</label>
                    <input type="text" name="titolo" placeholder="Nome playlist" class="form-input" required="required" />
                </div>
                <div style="flex-grow: 1;">
                    <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Descrizione</label>
                    <input type="text" name="descrizione" placeholder="Breve descrizione..." class="form-input" style="width: 100%;" />
                </div>
                <div>
                    <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Immagine (es. file.png)</label>
                    <input type="text" name="immagine" value="primo_piano.png" class="form-input" />
                </div>
                <div>
                    <button type="submit" class="btn-green">Crea Playlist</button>
                </div>
            </form>
        </div>

        <!-- TABELLA DI GESTIONE / MODIFICA ED ELIMINAZIONE -->
        <h3 style="font-size: 18px; margin-bottom: 16px;">Playlist Esistenti</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; background-color: #181818; border-radius: 8px; overflow: hidden;">
            <thead>
                <tr style="border-bottom: 1px solid #333; color: #b3b3b3; font-size: 11px; text-transform: uppercase;">
                    <th style="padding: 12px;">Titolo</th>
                    <th style="padding: 12px;">Descrizione</th>
                    <th style="padding: 12px;">Immagine</th>
                    <th style="padding: 12px; text-align: center;">Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($res_playlist && $res_playlist->num_rows > 0): ?>
                    <?php while ($pl = $res_playlist->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #222;">
                            <form action="gestione_playlist.php" method="POST">
                                <input type="hidden" name="azione" value="modifica" />
                                <input type="hidden" name="playlist_id" value="<?php echo $pl['id']; ?>" />
                                
                                <td style="padding: 14px;">
                                    <input type="text" name="titolo" value="<?php echo htmlspecialchars($pl['titolo']); ?>" class="form-input" style="width: 180px; font-weight: bold;" required="required" />
                                </td>
                                <td style="padding: 14px;">
                                    <input type="text" name="descrizione" value="<?php echo htmlspecialchars($pl['descrizione']); ?>" class="form-input" style="width: 400px;" />
                                </td>
                                <td style="padding: 14px;">
                                    <input type="text" name="immagine" value="<?php echo htmlspecialchars($pl['immagine']); ?>" class="form-input" style="width: 150px;" />
                                </td>
                                <td style="padding: 14px; text-align: center; white-space: nowrap;">
                                    <button type="submit" class="btn-green" style="padding: 6px 14px; margin-right: 6px;">Salva</button>
                                    <a href="gestione_playlist.php?elimina=<?php echo $pl['id']; ?>" class="btn-red" onclick="return confirm('Sei sicuro di voler eliminare questa playlist?');">Elimina</a>
                                </td>
                            </form>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="padding: 20px; text-align: center; color: #888;">Nessuna playlist trovata.</td>
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