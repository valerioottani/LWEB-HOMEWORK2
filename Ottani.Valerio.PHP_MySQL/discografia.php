<?php
session_start();
require_once 'connection.php';

$is_logged = isset($_SESSION['user']);
$ruolo = isset($_SESSION['ruolo']) ? $_SESSION['ruolo'] : '';
$username = $is_logged ? $_SESSION['user'] : '';

// Recupero di tutti gli album e i rispettivi artisti dal database
$res_albums = $conn->query("SELECT a.id, a.titolo AS album, a.anno, a.copertina, art.nome AS artista 
                            FROM `" . TAB_ALBUMS . "` a 
                            JOIN `" . TAB_ARTISTS . "` art ON a.artista_id = art.id 
                            ORDER BY a.anno DESC");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify - Discografia</title>
    <style type="text/css">
        .spotify-card {
            background-color: #181818;
            padding: 16px;
            border-radius: 8px;
            width: 185px;
            box-sizing: border-box;
            border: 1px solid rgba(255,255,255,0.05);
            transition: transform 0.3s ease, background-color 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }
        .spotify-card:hover {
            transform: translateY(-6px);
            background-color: #282828 !important;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.7);
        }
    </style>
</head>
<body style="background: linear-gradient(180deg, #1f1f1f 0%, #121212 40%, #0a0a0a 100%); background-attachment: fixed; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0; min-height: 100vh;">

    <?php include 'menu.php'; ?>

    <!-- Contenitore principale della discografia -->
    <div style="margin-left: 230px; padding: 32px; box-sizing: border-box; max-width: 1200px;">
        
        <div style="margin-bottom: 32px;">
            <h1 style="font-size: 32px; font-weight: 900; margin: 0 0 8px 0; letter-spacing: -1px;">Discografia Completa & Shop</h1>
            <p style="color: #b3b3b3; margin: 0; font-size: 14px;">Esplora tutti gli album ufficiali, ascolta i brani e acquista il merchandise fisico (vinili, CD e maglie).</p>
        </div>

        <!-- Griglia Album -->
        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <?php if ($res_albums && $res_albums->num_rows > 0): ?>
                <?php while ($alb = $res_albums->fetch_assoc()): ?>
                    <a href="dettaglio_album.php?id=<?php echo $alb['id']; ?>" style="text-decoration: none; color: inherit;">
                        <div class="spotify-card">
                            <div style="display: flex; justify-content: center; margin-bottom: 12px;">
                                <img src="img/<?php echo htmlspecialchars($alb['copertina']); ?>" alt="" style="width: 153px; height: 153px; border-radius: 4px; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.4);" />
                            </div>
                            <p style="font-size: 14px; font-weight: bold; margin: 0 0 4px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #ffffff;"><?php echo htmlspecialchars($alb['album']); ?></p>
                            <p style="color: #b3b3b3; font-size: 12px; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($alb['anno']) . ' • ' . htmlspecialchars($alb['artista']); ?></p>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #b3b3b3; font-size: 14px;">Nessun album disponibile nella discografia al momento.</p>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>
<?php $conn->close(); ?>