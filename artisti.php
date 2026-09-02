<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connection.php';

$is_logged = isset($_SESSION['user']);
$ruolo = isset($_SESSION['ruolo']) ? $_SESSION['ruolo'] : '';

// Esegue una query per prelevare l'elenco completo di tutti gli artisti ordinati per ID
$res_artists = $conn->query("SELECT * FROM `" . TAB_ARTISTS . "` ORDER BY id ASC");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify - Artisti</title>
    <style type="text/css">
        .spotify-card {
            background-color: #181818;
            transition: transform 0.3s ease, background-color 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            border-radius: 8px;
            padding: 16px;
            width: 170px;
            text-align: center;
            box-sizing: border-box;
            border: 1px solid rgba(255,255,255,0.05);
            text-decoration: none;
            display: inline-block;
        }
        .spotify-card:hover {
            transform: translateY(-6px);
            background-color: #282828 !important;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.7);
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

    <div style="margin-left: 230px; padding: 32px; box-sizing: border-box; max-width: 1200px;">
        
        <!-- Intestazione con titolo e pulsante admin per la gestione -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <div>
                <h1 style="font-size: 32px; font-weight: 900; margin: 0 0 6px 0; letter-spacing: -1px;">Tutti gli Artisti</h1>
                <p style="color: #b3b3b3; margin: 0; font-size: 14px;">Esplora i profili e le discografie degli artisti della scena.</p>
            </div>

            <?php if ($is_logged && $ruolo === 'admin'): ?>
                <div>
                    <a href="gestione_artisti.php" class="admin-link-btn">⚙️ Gestisci Artisti</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Griglia Artisti -->
        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <?php if ($res_artists && $res_artists->num_rows > 0): ?>
                <?php while ($art = $res_artists->fetch_assoc()): ?>
                    <a href="artista.php?id=<?php echo $art['id']; ?>" class="spotify-card">
                        <div style="display: flex; justify-content: center; margin-bottom: 12px;">
                            <img src="img/<?php echo htmlspecialchars($art['immagine']); ?>" alt="" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.4);" />
                        </div>
                        <p style="font-size: 14px; font-weight: bold; margin: 0 0 4px 0; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($art['nome']); ?></p>
                        <p style="color: #b3b3b3; font-size: 12px; margin: 0;">Artista</p>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #b3b3b3; font-size: 14px;">Nessun artista trovato nel database.</p>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>
<?php 
if (isset($conn) && !$conn->connect_error) {
    $conn->close(); 
}
?>