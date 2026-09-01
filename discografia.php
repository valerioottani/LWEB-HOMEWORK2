<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connection.php';

$is_logged = isset($_SESSION['user']);
$ruolo = isset($_SESSION['ruolo']) ? $_SESSION['ruolo'] : '';

// Recupero di tutti gli album dal database con il nome dell'artista associato
$res_albums = $conn->query("SELECT a.id, a.titolo AS album, a.anno, a.copertina, art.nome AS artista 
                           FROM `" . TAB_ALBUMS . "` a 
                           JOIN `" . TAB_ARTISTS . "` art ON a.artista_id = art.id 
                           ORDER BY a.anno DESC, a.id DESC");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify - Discografia</title>
    <style type="text/css">
        .spotify-card {
            background-color: #181818;
            border-radius: 8px;
            padding: 16px;
            width: 180px;
            text-decoration: none;
            color: #ffffff;
            transition: background-color 0.3s ease, transform 0.2s ease;
            display: inline-block;
            vertical-align: top;
            box-sizing: border-box;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .spotify-card:hover {
            background-color: #282828;
            transform: translateY(-4px);
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

    <div style="margin-left: 230px; padding: 32px; max-width: 1200px; box-sizing: border-box;">
        
        <!-- Intestazione con titolo e pulsante admin per la gestione discografia -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <div>
                <h1 style="font-size: 32px; font-weight: 900; margin: 0 0 6px 0; letter-spacing: -1px;">Discografia Completa</h1>
                <p style="color: #b3b3b3; margin: 0; font-size: 14px;">Tutti gli album e le pubblicazioni ufficiali disponibili nel catalogo.</p>
            </div>

            <?php if ($is_logged && $ruolo === 'admin'): ?>
                <div>
                    <a href="gestione_discografia.php" class="admin-link-btn">⚙️ Gestisci Discografia</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Griglia Album -->
        <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 40px;">
            <?php if ($res_albums && $res_albums->num_rows > 0): ?>
                <?php while ($alb = $res_albums->fetch_assoc()): ?>
                    <a href="dettaglio_album.php?id=<?php echo $alb['id']; ?>" class="spotify-card">
                        <img src="img/<?php echo htmlspecialchars($alb['copertina']); ?>" alt="" style="width: 100%; height: 148px; border-radius: 6px; object-fit: cover; margin-bottom: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.5);" />
                        <p style="font-size: 14px; font-weight: bold; margin: 0 0 4px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($alb['album']); ?></p>
                        <p style="font-size: 12px; color: #b3b3b3; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($alb['anno']) . ' • ' . htmlspecialchars($alb['artista']); ?></p>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #888; font-size: 14px;">Nessun album trovato nel catalogo.</p>
            <?php endif; ?>
        </div>

        <div>
            <a href="homepage.php" style="color: #1db954; text-decoration: none; font-size: 13px; font-weight: bold;">← Torna alla Homepage</a>
        </div>

    </div>

</body>
</html>
<?php 
if (isset($conn) && !$conn->connect_error) {
    $conn->close(); 
}
?>