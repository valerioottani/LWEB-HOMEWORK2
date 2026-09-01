<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connection.php';

$is_logged = isset($_SESSION['user']);
$ruolo = isset($_SESSION['ruolo']) ? $_SESSION['ruolo'] : '';

$id_playlist = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// Recuperiamo i dati della playlist specifica dal database
$res_pl = $conn->query("SELECT * FROM `playlist` WHERE id = $id_playlist");
if ($res_pl && $res_pl->num_rows > 0) {
    $playlist_corrente = $res_pl->fetch_assoc();
} else {
    $playlist_corrente = [
        'titolo' => 'Playlist non trovata',
        'descrizione' => 'Nessuna informazione disponibile per questa playlist.',
        'immagine' => 'album.png',
        'sfondo' => 'linear-gradient(135deg, #282828 0%, #000000 100%)'
    ];
}

// Recuperiamo i brani associati a questa playlist tramite la tabella ponte
$res_brani = $conn->query("SELECT t.titolo AS brano, t.durata, t.immagine_brano, a.titolo AS album, art.nome AS artista 
                           FROM `playlist_tracce` pt
                           JOIN `" . TAB_TRACKS . "` t ON pt.traccia_id = t.id
                           JOIN `" . TAB_ALBUMS . "` a ON t.album_id = a.id
                           JOIN `" . TAB_ARTISTS . "` art ON a.artista_id = art.id
                           WHERE pt.playlist_id = $id_playlist");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify - <?php echo htmlspecialchars($playlist_corrente['titolo']); ?></title>
    <style type="text/css">
        .spotify-row-item {
            background-color: #181818;
            transition: background-color 0.25s ease, transform 0.25s ease, box-shadow 0.25s ease;
            cursor: default;
        }
        .spotify-row-item:hover {
            background-color: #2a2a2a !important;
            transform: scale(1.008);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
        }
        .spotify-play-btn {
            background-color: #1db954;
            color: #000000;
            border: none;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: default;
            box-shadow: 0 8px 16px rgba(0,0,0,0.3);
            transition: transform 0.2s ease, background-color 0.2s ease;
        }
        .spotify-play-btn:hover {
            transform: scale(1.06);
            background-color: #1ed760;
        }
        .spotify-action-icon {
            background: transparent;
            border: none;
            color: #b3b3b3;
            font-size: 24px;
            cursor: default;
            transition: color 0.2s ease, transform 0.2s ease;
        }
        .spotify-action-icon:hover {
            color: #ffffff;
            transform: scale(1.1);
        }
        .admin-link-btn {
            background-color: #1db954;
            color: #000000;
            padding: 8px 16px;
            border-radius: 500px;
            font-weight: bold;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.2s ease;
        }
        .admin-link-btn:hover {
            background-color: #1ed760;
        }
    </style>
</head>
<body style="background: linear-gradient(180deg, #1f1f1f 0%, #121212 40%, #0a0a0a 100%); background-attachment: fixed; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0; min-height: 100vh;">

    <?php include 'menu.php'; ?>

    <div style="margin-left: 230px; padding: 32px; max-width: 1200px;">
        
        <!-- Header Playlist Dinamico -->
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 24px; margin-bottom: 28px; background: <?php echo $playlist_corrente['sfondo']; ?>; padding: 32px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.5);">
            <div style="display: flex; align-items: center; gap: 24px;">
                <img src="img/<?php echo htmlspecialchars($playlist_corrente['immagine']); ?>" alt="" style="width: 160px; height: 160px; border-radius: 6px; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.6);" />
                <div>
                    <span style="font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #1db954;">Playlist Pubblica</span>
                    <h1 style="font-size: 38px; font-weight: 900; margin: 8px 0;"><?php echo htmlspecialchars($playlist_corrente['titolo']); ?></h1>
                    <p style="color: #b3b3b3; font-size: 14px; margin: 0;"><?php echo htmlspecialchars($playlist_corrente['descrizione']); ?></p>
                </div>
            </div>

            <?php if ($is_logged && $ruolo === 'admin'): ?>
                <div>
                    <a href="gestione_brani.php?id=<?php echo $id_playlist; ?>" class="admin-link-btn">⚙️ Gestisci Brani</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- BARRA COMANDI -->
        <div style="display: flex; align-items: center; gap: 24px; margin-bottom: 32px;">
            <button class="spotify-play-btn" title="Riproduci">▶</button>
            <button class="spotify-action-icon" title="Attiva sequenza casuale">🔀</button>
            <button class="spotify-action-icon" title="Salva nella libreria">♡</button>
        </div>

        <!-- Elenco Brani -->
        <h2 style="font-size: 22px; font-weight: bold; margin-bottom: 20px;">Brani inclusi</h2>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <?php if ($res_brani && $res_brani->num_rows > 0): ?>
                <?php while ($traccia = $res_brani->fetch_assoc()): ?>
                    <div class="spotify-row-item" style="display: flex; align-items: center; justify-content: space-between; padding: 12px 18px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.03);">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <img src="img/<?php echo htmlspecialchars($traccia['immagine_brano']); ?>" alt="" style="width: 45px; height: 45px; border-radius: 4px; object-fit: cover;" />
                            <div>
                                <p style="font-size: 14px; font-weight: bold; margin: 0 0 4px 0;"><?php echo htmlspecialchars($traccia['brano']); ?></p>
                                <p style="color: #b3b3b3; font-size: 12px; margin: 0;"><?php echo htmlspecialchars($traccia['artista']); ?> • <?php echo htmlspecialchars($traccia['album']); ?></p>
                            </div>
                        </div>
                        <div style="color: #b3b3b3; font-size: 13px;">
                            <?php echo htmlspecialchars($traccia['durata']); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #b3b3b3; font-size: 14px;">Nessun brano trovato in questa playlist.</p>
            <?php endif; ?>
        </div>

        <div style="margin-top: 30px;">
            <a href="homepage.php" style="color: #1db954; text-decoration: none; font-size: 13px; font-weight: bold; cursor: pointer;">← Torna alla Homepage</a>
        </div>

    </div>

</body>
</html>
<?php $conn->close(); ?>