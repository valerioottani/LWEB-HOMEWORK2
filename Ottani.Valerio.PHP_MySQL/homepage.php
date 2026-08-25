<?php
session_start();
require_once 'connection.php';

$is_logged = isset($_SESSION['user']);
$ruolo = isset($_SESSION['ruolo']) ? $_SESSION['ruolo'] : '';
$username = $is_logged ? $_SESSION['user'] : '';

$res_artists = $conn->query("SELECT * FROM `" . TAB_ARTISTS . "` ORDER BY id ASC");
$res_albums = $conn->query("SELECT a.titolo AS album, a.anno, a.copertina, art.nome AS artista FROM `" . TAB_ALBUMS . "` a JOIN `" . TAB_ARTISTS . "` art ON a.artista_id = art.id LIMIT 4");

$stazioni_radio = [
    ['nome' => 'Luchè Radio', 'artisti' => 'Con Geolier, Guè, Marracash e molti altri', 'img' => 'primo_piano.png', 'bg' => 'linear-gradient(135deg, #1e3264 0%, #000000 100%)'],
    ['nome' => 'Geolier Radio', 'artisti' => 'Con Luchè, Lazza, Sfera Ebbasta e altri', 'img' => 'geolier.jpg', 'bg' => 'linear-gradient(135deg, #8d67ab 0%, #000000 100%)'],
    ['nome' => 'Marracash Radio', 'artisti' => 'Con Guè, Fabri Fibra, Salmo e altri', 'img' => 'marra.jpg', 'bg' => 'linear-gradient(135deg, #e8115b 0%, #000000 100%)'],
    ['nome' => 'Lazza Radio', 'artisti' => 'Con Sfera Ebbasta, Shiva, Tedua e altri', 'img' => 'lazza.jpg', 'bg' => 'linear-gradient(135deg, #148a08 0%, #000000 100%)'],
    ['nome' => 'Guè Radio', 'artisti' => 'Con Club Dogo, Marracash, Noyz Narcos e altri', 'img' => 'gue.jpg', 'bg' => 'linear-gradient(135deg, #e91429 0%, #000000 100%)']
];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify - Homepage</title>
    <style type="text/css">
        .spotify-card {
            transition: transform 0.25s ease, background-color 0.25s ease, box-shadow 0.25s ease;
            cursor: default;
        }
        .spotify-card:hover {
            transform: translateY(-6px);
            background-color: #282828 !important;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.6);
        }
        .spotify-radio-card {
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            cursor: default;
        }
        .spotify-radio-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.7);
        }
    </style>
</head>
<body style="background-color: #121212; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0;">

    <?php include 'menu.php'; ?>

    <div style="margin-left: 230px; padding: 32px; max-width: 1200px;">
        
        <!-- Header Benvenuto Dinamico -->
        <div style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 32px; font-weight: 900; margin: 0 0 8px 0; letter-spacing: -1px;">
                    <?php if ($is_logged && $ruolo === 'admin'): ?>
                        Benvenuto admin
                    <?php elseif ($is_logged): ?>
                        Benvenuto <?php echo htmlspecialchars($username); ?>
                    <?php else: ?>
                        Benvenuto su Spotify
                    <?php endif; ?>
                </h1>
                <p style="color: #b3b3b3; margin: 0; font-size: 14px;">Esplora musica, album, stazioni radio e i tuoi artisti preferiti.</p>
            </div>

            <!-- Pannello Azioni Database (Visibile solo se Admin) -->
            <?php if ($is_logged && $ruolo === 'admin'): ?>
                <div style="background-color: #282828; padding: 12px 20px; border-radius: 8px; border: 1px solid #1db954; display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 12px; font-weight: bold; color: #1db954; text-transform: uppercase;">Area Admin</span>
                    <a href="admin.php" style="background-color: #1db954; color: #000000; padding: 6px 14px; border-radius: 500px; text-decoration: none; font-size: 11px; font-weight: bold;">Gestisci DB</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- SEZIONE 1: Stazioni radio più popolari -->
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 16px;">
            <h2 style="font-size: 22px; font-weight: bold; margin: 0;">Stazioni radio più popolari</h2>
            <span style="color: #b3b3b3; font-size: 12px; font-weight: bold; text-transform: uppercase;">Radio</span>
        </div>

        <div style="display: flex; gap: 18px; flex-wrap: wrap; margin-bottom: 40px;">
            <?php foreach ($stazioni_radio as $radio): ?>
                <div class="spotify-radio-card" style="background: <?php echo $radio['bg']; ?>; padding: 16px; border-radius: 8px; width: 175px; box-sizing: border-box; position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,0.05);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span style="color: #1db954; font-size: 14px;">●</span>
                        <span style="background-color: rgba(0,0,0,0.4); color: #ffffff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; letter-spacing: 1px;">RADIO</span>
                    </div>
                    
                    <div style="text-align: center; margin-bottom: 14px;">
                        <img src="img/<?php echo htmlspecialchars($radio['img']); ?>" alt="<?php echo htmlspecialchars($radio['nome']); ?>" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.5);" />
                    </div>

                    <p style="font-size: 15px; font-weight: bold; margin: 0 0 4px 0; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($radio['nome']); ?></p>
                    <p style="color: #b3b3b3; font-size: 11px; line-height: 1.4; margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?php echo htmlspecialchars($radio['artisti']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- SEZIONE 2: Discografia in evidenza -->
        <h2 style="font-size: 22px; font-weight: bold; margin: 0 0 16px 0;">Discografia in evidenza</h2>
        <div style="display: flex; gap: 18px; flex-wrap: wrap; margin-bottom: 40px;">
            <?php while ($alb = $res_albums->fetch_assoc()): ?>
                <div class="spotify-card" style="background-color: #181818; padding: 16px; border-radius: 8px; width: 175px; box-sizing: border-box;">
                    <img src="img/<?php echo htmlspecialchars($alb['copertina']); ?>" alt="<?php echo htmlspecialchars($alb['album']); ?>" style="width: 143px; height: 143px; border-radius: 4px; object-fit: cover; margin-bottom: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.4);" />
                    <p style="font-size: 14px; font-weight: bold; margin: 0 0 4px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($alb['album']); ?></p>
                    <p style="color: #b3b3b3; font-size: 12px; margin: 0;"><?php echo htmlspecialchars($alb['anno']) . ' • ' . htmlspecialchars($alb['artista']); ?></p>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- SEZIONE 3: Artisti correlati -->
        <h2 style="font-size: 22px; font-weight: bold; margin: 0 0 16px 0;">Artisti correlati</h2>
        <div style="display: flex; gap: 18px; flex-wrap: wrap;">
            <?php while ($art = $res_artists->fetch_assoc()): ?>
                <div class="spotify-card" style="background-color: #181818; padding: 16px; border-radius: 8px; width: 155px; text-align: center; box-sizing: border-box;">
                    <img src="img/<?php echo htmlspecialchars($art['immagine']); ?>" alt="<?php echo htmlspecialchars($art['nome']); ?>" style="width: 115px; height: 115px; border-radius: 50%; object-fit: cover; margin-bottom: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.4);" />
                    <p style="font-size: 14px; font-weight: bold; margin: 0 0 4px 0;"><?php echo htmlspecialchars($art['nome']); ?></p>
                    <p style="color: #b3b3b3; font-size: 12px; margin: 0;">Artista</p>
                </div>
            <?php endwhile; ?>
        </div>

    </div>

</body>
</html>
<?php $conn->close(); ?>