<?php
session_start();
require_once 'connection.php';

$res = $conn->query("SELECT * FROM `" . TAB_ARTISTS . "` ORDER BY id ASC");
$is_admin = (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Artisti - Spotify</title>
    <style type="text/css">
        .spotify-artist-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .spotify-artist-card {
            transition: transform 0.25s ease, background-color 0.25s ease, box-shadow 0.25s ease;
            cursor: pointer;
        }
        .spotify-artist-card:hover {
            transform: translateY(-8px);
            background-color: #282828 !important;
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.7);
        }
    </style>
</head>
<body style="background-color: #121212; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0;">

    <?php include 'menu.php'; ?>

    <div style="margin-left: 230px; padding: 32px; max-width: 1200px;">
        
        <!-- Header pagina con pulsante admin condizionale -->
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
            <div>
                <h1 style="font-size: 32px; font-weight: 900; margin: 0 0 8px 0; letter-spacing: -1px;">Tutti gli Artisti</h1>
                <p style="color: #b3b3b3; font-size: 14px; margin: 0;">Clicca su un artista per visualizzare la biografia e ascoltare i suoi brani più famosi.</p>
            </div>

            <?php if ($is_admin): ?>
                <div>
                    <a href="admin.php" style="background-color: #1db954; color: #000000; padding: 10px 20px; border-radius: 500px; text-decoration: none; font-size: 12px; font-weight: bold; text-transform: uppercase; display: inline-block;">+ Modifica Database (Admin)</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Griglia Card Cliccabili con effetto Rilievo -->
        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <?php while ($a = $res->fetch_assoc()): ?>
                <a href="artista.php?id=<?php echo $a['id']; ?>" class="spotify-artist-link">
                    <div class="spotify-artist-card" style="background-color: #181818; padding: 20px 16px; border-radius: 8px; width: 175px; text-align: center; box-sizing: border-box; display: flex; flex-direction: column; align-items: center;">
                        <img src="img/<?php echo htmlspecialchars($a['immagine']); ?>" alt="<?php echo htmlspecialchars($a['nome']); ?>" style="width: 130px; height: 130px; border-radius: 50%; object-fit: cover; margin-bottom: 14px; box-shadow: 0 6px 16px rgba(0,0,0,0.5);" />
                        
                        <p style="font-size: 15px; font-weight: bold; margin: 0 0 4px 0; color: #ffffff; width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?php echo htmlspecialchars($a['nome']); ?>
                        </p>
                        
                        <span style="font-size: 11px; font-weight: bold; color: #1db954; text-transform: uppercase; margin-bottom: 8px;">Artista</span>
                        
                        <p style="color: #b3b3b3; font-size: 12px; line-height: 1.4; margin: 0; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; text-align: center;">
                            <?php echo htmlspecialchars($a['biografia']); ?>
                        </p>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    </div>

</body>
</html>
<?php $conn->close(); ?>