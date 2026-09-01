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

$playlist_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$messaggio = '';
$errore = '';

// Recupero dati della playlist
$res_pl = $conn->query("SELECT * FROM `playlist` WHERE id = $playlist_id");
if ($res_pl && $res_pl->num_rows > 0) {
    $playlist = $res_pl->fetch_assoc();
} else {
    die("Playlist non trovata.");
}

// 1. GESTIONE AGGIUNTA DI UN QUALSIASI NUOVO BRANO E COLLEGAMENTO ALLA PLAYLIST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione']) && $_POST['azione'] === 'aggiungi_nuovo_brano') {
    $titolo_brano = trim($_POST['titolo_brano']);
    $durata = trim($_POST['durata']);
    $album_id = (int)$_POST['album_id'];
    $immagine_brano = !empty(trim($_POST['immagine_brano'])) ? trim($_POST['immagine_brano']) : 'album.png';

    if (!empty($titolo_brano) && !empty($durata) && $album_id > 0) {
        // Inseriamo prima il nuovo brano nella tabella principale dei brani
        $stmt_ins_track = $conn->prepare("INSERT INTO `" . TAB_TRACKS . "` (album_id, titolo, durata, immagine_brano) VALUES (?, ?, ?, ?)");
        if ($stmt_ins_track) {
            $stmt_ins_track->bind_param("isss", $album_id, $titolo_brano, $durata, $immagine_brano);
            if ($stmt_ins_track->execute()) {
                $nuovo_traccia_id = $stmt_ins_track->insert_id;
                $stmt_ins_track->close();

                // Ora lo colleghiamo immediatamente alla playlist corrente tramite la tabella ponte
                $stmt_link = $conn->prepare("INSERT INTO `playlist_tracce` (playlist_id, traccia_id) VALUES (?, ?)");
                if ($stmt_link) {
                    $stmt_link->bind_param("ii", $playlist_id, $nuovo_traccia_id);
                    if ($stmt_link->execute()) {
                        $messaggio = "Nuovo brano creato e aggiunto alla playlist con successo!";
                    } else {
                        $errore = "Brano creato, ma c'è stato un errore nel collegarlo alla playlist.";
                    }
                    $stmt_link->close();
                }
            } else {
                $errore = "Errore durante la creazione del nuovo brano.";
                $stmt_ins_track->close();
            }
        }
    } else {
        $errore = "Compila tutti i campi obbligatori per aggiungere il brano.";
    }
}

// 2. GESTIONE RIMOZIONE BRANO DALLA PLAYLIST
if (isset($_GET['rimuovi'])) {
    $traccia_id = (int)$_GET['rimuovi'];
    $stmt_del = $conn->prepare("DELETE FROM `playlist_tracce` WHERE playlist_id = ? AND traccia_id = ?");
    if ($stmt_del) {
        $stmt_del->bind_param("ii", $playlist_id, $traccia_id);
        if ($stmt_del->execute()) {
            $messaggio = "Brano rimosso dalla playlist con successo!";
        } else {
            $errore = "Errore durante la rimozione del brano.";
        }
        $stmt_del->close();
    }
}

// Recupero brani associati attualmente alla playlist
$res_brani = $conn->query("SELECT t.id, t.titolo, t.durata, a.titolo AS album, art.nome AS artista 
                           FROM `playlist_tracce` pt 
                           JOIN `" . TAB_TRACKS . "` t ON pt.traccia_id = t.id 
                           JOIN `" . TAB_ALBUMS . "` a ON t.album_id = a.id 
                           JOIN `" . TAB_ARTISTS . "` art ON a.artista_id = art.id 
                           WHERE pt.playlist_id = $playlist_id");

// Recupero tutti gli album disponibili per associare il brano
$res_albums = $conn->query("SELECT a.id, a.titolo AS album, art.nome AS artista FROM `" . TAB_ALBUMS . "` a JOIN `" . TAB_ARTISTS . "` art ON a.artista_id = art.id ORDER BY a.titolo ASC");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify - Gestione Brani: <?php echo htmlspecialchars($playlist['titolo']); ?></title>
    <style type="text/css">
        .form-input {
            background-color: #181818;
            color: #ffffff;
            border: 1px solid #444;
            padding: 9px;
            border-radius: 4px;
            font-size: 13px;
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

    <div style="margin-left: 230px; padding: 32px; max-width: 1200px; box-sizing: border-box;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <div>
                <span style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: #1db954; letter-spacing: 1px;">Pannello Amministrazione</span>
                <h1 style="font-size: 26px; font-weight: bold; margin: 4px 0 0 0;">Gestione Brani: <?php echo htmlspecialchars($playlist['titolo']); ?></h1>
            </div>
            <a href="playlist.php?id=<?php echo $playlist_id; ?>" style="color: #1db954; text-decoration: none; font-size: 13px; font-weight: bold;">← Torna alla Playlist</a>
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

        <!-- FORM PER AGGIUNGERE UN NUOVO BRANO PERSONALIZZATO -->
        <div style="background-color: #181818; padding: 24px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05); margin-bottom: 30px;">
            <h3 style="margin-top: 0; font-size: 16px; color: #1db954; margin-bottom: 16px;">+ Aggiungi un Nuovo Brano Personalizzato</h3>
            <form action="gestione_brani.php?id=<?php echo $playlist_id; ?>" method="POST" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
                <input type="hidden" name="azione" value="aggiungi_nuovo_brano" />
                
                <div>
                    <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Titolo Brano</label>
                    <input type="text" name="titolo_brano" placeholder="Nome del brano" class="form-input" style="width: 200px;" required="required" />
                </div>
                
                <div>
                    <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Durata (es. 3:45)</label>
                    <input type="text" name="durata" placeholder="0:00" class="form-input" style="width: 90px;" required="required" />
                </div>

                <div style="flex-grow: 1; min-width: 220px;">
                    <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Album di Riferimento</label>
                    <select name="album_id" class="form-input" style="width: 100%;" required="required">
                        <option value="">-- Seleziona album --</option>
                        <?php while ($alb = $res_albums->fetch_assoc()): ?>
                            <option value="<?php echo $alb['id']; ?>"><?php echo htmlspecialchars($alb['album'] . ' (' . $alb['artista'] . ')'); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Immagine (es. album.png)</label>
                    <input type="text" name="immagine_brano" value="album.png" class="form-input" style="width: 140px;" />
                </div>

                <div>
                    <button type="submit" class="btn-green">Crea e Aggiungi</button>
                </div>
            </form>
        </div>

        <!-- TABELLA BRANI PRESENTI E RIMOZIONE -->
        <h3 style="font-size: 18px; margin-bottom: 16px;">Brani Attualmente in Playlist</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; background-color: #181818; border-radius: 8px; overflow: hidden;">
            <thead>
                <tr style="border-bottom: 1px solid #333; color: #b3b3b3; font-size: 11px; text-transform: uppercase;">
                    <th style="padding: 12px;">Titolo Brano</th>
                    <th style="padding: 12px;">Artista</th>
                    <th style="padding: 12px;">Album</th>
                    <th style="padding: 12px;">Durata</th>
                    <th style="padding: 12px; text-align: center;">Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($res_brani && $res_brani->num_rows > 0): ?>
                    <?php while ($b = $res_brani->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #222;">
                            <td style="padding: 14px; font-weight: bold; color: #ffffff;"><?php echo htmlspecialchars($b['titolo']); ?></td>
                            <td style="padding: 14px; color: #b3b3b3;"><?php echo htmlspecialchars($b['artista']); ?></td>
                            <td style="padding: 14px; color: #b3b3b3;"><?php echo htmlspecialchars($b['album']); ?></td>
                            <td style="padding: 14px; color: #b3b3b3;"><?php echo htmlspecialchars($b['durata']); ?></td>
                            <td style="padding: 14px; text-align: center;">
                                <a href="gestione_brani.php?id=<?php echo $playlist_id; ?>&rimuovi=<?php echo $b['id']; ?>" class="btn-red" onclick="return confirm('Sei sicuro di voler rimuovere questo brano dalla playlist?');">Rimuovi</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="padding: 20px; text-align: center; color: #888;">Nessun brano presente in questa playlist.</td>
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