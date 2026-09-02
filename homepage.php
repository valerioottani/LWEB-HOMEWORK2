<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connection.php';

$is_logged = isset($_SESSION['user']);
$ruolo = isset($_SESSION['ruolo']) ? $_SESSION['ruolo'] : '';
$username = $is_logged ? $_SESSION['user'] : '';

$res_artists = $conn->query("SELECT * FROM `" . TAB_ARTISTS . "` ORDER BY id ASC");

$res_albums = $conn->query("SELECT a.id, a.titolo AS album, a.anno, a.copertina, art.nome AS artista 
                           FROM `" . TAB_ALBUMS . "` a 
                           JOIN `" . TAB_ARTISTS . "` art ON a.artista_id = art.id 
                           ORDER BY a.id DESC LIMIT 4");

$res_playlist = $conn->query("SELECT * FROM `playlist` WHERE tipo = 'playlist'");

// Recupero di tutte le stazioni radio direttamente dal database
$res_stazioni_home = $conn->query("SELECT * FROM `stazioni_radio` ORDER BY id ASC");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify - Homepage</title>
    <style type="text/css">
        .spotify-card {
            background-color: #181818;
            transition: transform 0.3s ease, background-color 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }
        .spotify-card:hover {
            transform: translateY(-6px);
            background-color: #282828 !important;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.7);
        }
        .spotify-radio-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease, filter 0.3s ease;
            cursor: default;
        }
        .spotify-radio-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.8);
            filter: brightness(1.1);
        }
        .admin-link-btn {
            background-color: #1db954;
            color: #000000;
            padding: 6px 14px;
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

    <div style="margin-left: 230px; padding: 32px; box-sizing: border-box;">
        
        <div style="max-width: 1000px;">
            
            <div style="margin-bottom: 32px;">
                <h1 style="font-size: 32px; font-weight: 900; margin: 0 0 8px 0; letter-spacing: -1px;">
                    <?php if ($is_logged && $ruolo === 'admin'): ?>
                        Benvenuto admin
                    <?php elseif ($is_logged): ?>
                        Benvenuto <?php echo htmlspecialchars($username); ?>
                    <?php else: ?>
                        Benvenuto su Spotify
                    <?php endif; ?>
                </h1>
                <p style="color: #b3b3b3; margin: 0; font-size: 14px;">Esplora musica, album, playlist e stazioni radio.</p>
            </div>

            <!-- Sezione Playlist in evidenza -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h2 style="font-size: 22px; font-weight: bold; margin: 0;">Playlist in evidenza</h2>
                <?php if ($is_logged && $ruolo === 'admin'): ?>
                    <a href="gestione_playlist.php" class="admin-link-btn">⚙️ Gestisci Playlist</a>
                <?php endif; ?>
            </div>

            <div style="display: flex; gap: 18px; flex-wrap: wrap; margin-bottom: 40px;">
                <?php while ($pl = $res_playlist->fetch_assoc()): ?>
                    <a href="playlist.php?id=<?php echo $pl['id']; ?>" style="text-decoration: none; color: inherit;">
                        <div class="spotify-radio-card" style="background: <?php echo htmlspecialchars($pl['sfondo']); ?>; padding: 16px; border-radius: 8px; width: 175px; box-sizing: border-box; border: 1px solid rgba(255,255,255,0.05); cursor: pointer;">
                            <div style="display: flex; justify-content: center; margin-bottom: 12px;">
                                <img src="img/<?php echo htmlspecialchars($pl['immagine']); ?>" alt="" style="width: 120px; height: 120px; border-radius: 4px; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.5);" />
                            </div>
                            <p style="font-size: 15px; font-weight: bold; margin: 0 0 4px 0; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($pl['titolo']); ?></p>
                            <p style="color: #b3b3b3; font-size: 11px; line-height: 1.4; margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?php echo htmlspecialchars($pl['descrizione']); ?></p>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>

            <!-- Sezione Stazioni radio più popolari (Dinamica dal Database) -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h2 style="font-size: 22px; font-weight: bold; margin: 0;">Stazioni radio più popolari</h2>
                <?php if ($is_logged && $ruolo === 'admin'): ?>
                    <a href="gestione_stazioni.php" class="admin-link-btn">⚙️ Gestisci Stazioni</a>
                <?php endif; ?>
            </div>

            <div style="display: flex; gap: 18px; flex-wrap: wrap; margin-bottom: 40px;">
                <?php if ($res_stazioni_home && $res_stazioni_home->num_rows > 0): ?>
                    <?php while ($radio = $res_stazioni_home->fetch_assoc()): ?>
                        <div class="spotify-radio-card" style="background: <?php echo htmlspecialchars($radio['sfondo_css']); ?>; padding: 16px; border-radius: 8px; width: 175px; box-sizing: border-box; position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,0.05);">
                            <div style="display: flex; justify-content: flex-end; align-items: center; margin-bottom: 12px;">
                                <span style="background-color: rgba(0,0,0,0.4); color: #ffffff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; letter-spacing: 1px;">RADIO</span>
                            </div>
                            <div style="display: flex; justify-content: center; margin-bottom: 14px;">
                                <img src="img/<?php echo htmlspecialchars($radio['immagine']); ?>" alt="" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.5);" />
                            </div>
                            <p style="font-size: 15px; font-weight: bold; margin: 0 0 4px 0; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($radio['nome']); ?></p>
                            <p style="color: #b3b3b3; font-size: 11px; line-height: 1.4; margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?php echo htmlspecialchars($radio['artisti']); ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #b3b3b3; font-size: 14px;">Nessuna stazione radio disponibile.</p>
                <?php endif; ?>
            </div>

            <!-- Sezione Discografia in evidenza -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h2 style="font-size: 22px; font-weight: bold; margin: 0;">Discografia in evidenza</h2>
                <?php if ($is_logged && $ruolo === 'admin'): ?>
                    <a href="gestione_discografia.php" class="admin-link-btn">⚙️ Gestisci Discografia</a>
                <?php endif; ?>
            </div>

            <div style="display: flex; gap: 18px; flex-wrap: wrap; margin-bottom: 40px;">
                <?php while ($alb = $res_albums->fetch_assoc()): ?>
                    <a href="dettaglio_album.php?id=<?php echo $alb['id']; ?>" class="spotify-card" style="padding: 16px; border-radius: 8px; width: 175px; text-decoration: none; color: inherit; box-sizing: border-box; border: 1px solid rgba(255,255,255,0.05);">
                        <div style="display: flex; justify-content: center; margin-bottom: 12px;">
                            <img src="img/<?php echo htmlspecialchars($alb['copertina']); ?>" alt="" style="width: 143px; height: 143px; border-radius: 4px; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.4);" />
                        </div>
                        <p style="font-size: 14px; font-weight: bold; margin: 0 0 4px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #ffffff;"><?php echo htmlspecialchars($alb['album']); ?></p>
                        <p style="color: #b3b3b3; font-size: 12px; margin: 0;"><?php echo htmlspecialchars($alb['anno']) . ' • ' . htmlspecialchars($alb['artista']); ?></p>
                    </a>
                <?php endwhile; ?>
            </div>

            <h2 style="font-size: 22px; font-weight: bold; margin: 0 0 16px 0;">Artisti correlati</h2>
            <div style="display: flex; gap: 18px; flex-wrap: wrap;">
                <?php while ($art = $res_artists->fetch_assoc()): ?>
                    <a href="artista.php?id=<?php echo $art['id']; ?>" style="text-decoration: none; color: inherit;">
                        <div class="spotify-card" style="padding: 16px; border-radius: 8px; width: 155px; text-align: center; box-sizing: border-box; border: 1px solid rgba(255,255,255,0.05);">
                            <div style="display: flex; justify-content: center; margin-bottom: 12px;">
                                <img src="img/<?php echo htmlspecialchars($art['immagine']); ?>" alt="" style="width: 115px; height: 115px; border-radius: 50%; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.4);" />
                            </div>
                            <p style="font-size: 14px; font-weight: bold; margin: 0 0 4px 0; color: #ffffff;"><?php echo htmlspecialchars($art['nome']); ?></p>
                            <p style="color: #b3b3b3; font-size: 12px; margin: 0;">Artista</p>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>

        </div>

    </div>

</body>
</html>
<?php $conn->close(); ?>