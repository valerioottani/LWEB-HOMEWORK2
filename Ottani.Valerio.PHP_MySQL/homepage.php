<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connection.php';

$is_logged = isset($_SESSION['user']);
$ruolo = isset($_SESSION['ruolo']) ? $_SESSION['ruolo'] : '';
$username = $is_logged ? $_SESSION['user'] : '';

$messaggio = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione']) && $_POST['azione'] === 'aggiungi_traccia_playlist') {
    if (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin') {
        $playlist_id = (int)$_POST['playlist_id'];
        $traccia_id = (int)$_POST['traccia_id'];
        
        $check = $conn->query("SELECT * FROM `playlist_tracce` WHERE playlist_id = $playlist_id AND traccia_id = $traccia_id");
        if ($check && $check->num_rows == 0) {
            $conn->query("INSERT INTO `playlist_tracce` (playlist_id, traccia_id) VALUES ($playlist_id, $traccia_id)");
            $messaggio = "Brano aggiunto con successo alla playlist!";
        } else {
            $messaggio = "Il brano è già presente in questa playlist.";
        }
    }
}

$res_artists = $conn->query("SELECT * FROM `" . TAB_ARTISTS . "` ORDER BY id ASC");
$res_albums = $conn->query("SELECT a.titolo AS album, a.anno, a.copertina, art.nome AS artista FROM `" . TAB_ALBUMS . "` a JOIN `" . TAB_ARTISTS . "` art ON a.artista_id = art.id LIMIT 4");

$res_playlist = $conn->query("SELECT * FROM `playlist` WHERE tipo = 'playlist'");
$res_classifiche = $conn->query("SELECT * FROM `playlist` WHERE tipo = 'classifica'");

$stazioni_radio = [
    ['nome' => 'Luchè Radio', 'artisti' => 'Con Geolier, Guè, Marracash e molti altri', 'img' => 'primo_piano.png', 'bg' => 'linear-gradient(135deg, #1e3264 0%, #000000 100%)'],
    ['nome' => 'Geolier Radio', 'artisti' => 'Con Luchè, Lazza, Sfera Ebbasta e altri', 'img' => 'geolier.jpg', 'bg' => 'linear-gradient(135deg, #8d67ab 0%, #000000 100%)'],
    ['nome' => 'Marracash Radio', 'artisti' => 'Con Guè, Fabri Fibra, Salmo e altri', 'img' => 'marra.jpg', 'bg' => 'linear-gradient(135deg, #e8115b 0%, #000000 100%)'],
    ['nome' => 'Lazza Radio', 'artisti' => 'Con Sfera Ebbasta, Shiva, Tedua e altri', 'img' => 'lazza.jpg', 'bg' => 'linear-gradient(135deg, #148a08 0%, #000000 100%)'],
    ['nome' => 'Guè Radio', 'artisti' => 'Con Club Dogo, Marracash, Noyz Narcos e altri', 'img' => 'gue.jpg', 'bg' => 'linear-gradient(135deg, #e91429 0%, #000000 100%)']
];

$articoli_blog = [
    ['id' => 1, 'titolo' => "Il ritorno di Luchè: anatomia di un successo che ridefinisce il rap d'autore", 'data' => '25 Agosto 2026', 'autore' => 'Redazione Urban'],
    ['id' => 2, 'titolo' => "La nuova età dell'oro del Rap Italiano: trionfi, stadi e la consacrazione dei live estivi", 'data' => '24 Agosto 2026', 'autore' => 'Pincopallino S.']
];

$community_artisti = [
    ['artista' => 'Luchè Fan Club', 'membri' => '12.4k membri', 'img' => 'primo_piano.png'],
    ['artista' => 'Geolier Community Ufficiale', 'membri' => '25.1k membri', 'img' => 'geolier.jpg'],
    ['artista' => 'Marracash Global Hub', 'membri' => '18.9k membri', 'img' => 'marra.jpg'],
    ['artista' => 'Lazza & Sirio Squad', 'membri' => '9.3k membri', 'img' => 'lazza.jpg']
];
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
        .spotify-static-box {
            background-color: #181818;
            transition: background-color 0.25s ease, transform 0.25s ease, box-shadow 0.25s ease;
            cursor: default;
        }
        .spotify-static-box:hover {
            background-color: #222222 !important;
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.6);
        }
        .blog-post-card {
            background-color: #202020;
            padding: 12px;
            border-radius: 6px;
            transition: background-color 0.2s ease, transform 0.2s ease;
            cursor: pointer;
            border: 1px solid rgba(255,255,255,0.03);
        }
        .blog-post-card:hover {
            background-color: #2a2a2a;
            transform: translateY(-2px);
        }
    </style>
</head>
<body style="background: linear-gradient(180deg, #1f1f1f 0%, #121212 40%, #0a0a0a 100%); background-attachment: fixed; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0; min-height: 100vh;">

    <?php include 'menu.php'; ?>

    <div style="margin-left: 230px; padding: 32px; display: flex; justify-content: space-between; align-items: flex-start; box-sizing: border-box;">
        
        <div style="flex: 1; max-width: 820px;">
            
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
                <p style="color: #b3b3b3; margin: 0; font-size: 14px;">Esplora musica, album, playlist, classifiche e stazioni radio.</p>
            </div>

            <?php if (!empty($messaggio)): ?>
                <div style="background-color: rgba(29,185,84,0.15); border: 1px solid #1db954; color: #1db954; padding: 12px 16px; border-radius: 6px; margin-bottom: 24px; font-size: 13px;">
                    <?php echo htmlspecialchars($messaggio); ?>
                </div>
            <?php endif; ?>

            <?php if ($is_logged && $ruolo === 'admin'): ?>
                <div style="background-color: #242424; border: 1px solid #1db954; padding: 20px; border-radius: 8px; margin-bottom: 35px;">
                    <h3 style="color: #1db954; margin-top: 0; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">Pannello Admin - Gestione Brani in Playlist</h3>
                    <form action="homepage.php" method="POST" style="display: flex; gap: 16px; flex-wrap: wrap; align-items: flex-end;">
                        <input type="hidden" name="azione" value="aggiungi_traccia_playlist" />
                        <div style="flex: 2;">
                            <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Seleziona Playlist</label>
                            <select name="playlist_id" style="background: #121212; color: #fff; padding: 8px; border: 1px solid #444; border-radius: 4px; width: 100%;">
                                <?php 
                                $res_pl_list = $conn->query("SELECT id, titolo FROM `playlist`");
                                while ($pl_item = $res_pl_list->fetch_assoc()) {
                                    echo '<option value="' . $pl_item['id'] . '">' . htmlspecialchars($pl_item['titolo']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div style="flex: 2;">
                            <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Seleziona Brano</label>
                            <select name="traccia_id" style="background: #121212; color: #fff; padding: 8px; border: 1px solid #444; border-radius: 4px; width: 100%;">
                                <?php 
                                $res_tr_list = $conn->query("SELECT id, titolo FROM `" . TAB_TRACKS . "`");
                                while ($tr = $res_tr_list->fetch_assoc()) {
                                    echo '<option value="' . $tr['id'] . '">' . htmlspecialchars($tr['titolo']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <button type="submit" style="background-color: #1db954; color: #000; border: none; padding: 9px 20px; border-radius: 500px; font-weight: bold; font-size: 12px; cursor: pointer;">+ Inserisci</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <h2 style="font-size: 22px; font-weight: bold; margin: 0 0 16px 0;">Playlist in evidenza</h2>
            <div style="display: flex; gap: 18px; flex-wrap: wrap; margin-bottom: 40px;">
                <?php while ($pl = $res_playlist->fetch_assoc()): ?>
                    <a href="playlist.php?id=<?php echo $pl['id']; ?>" style="text-decoration: none; color: inherit;">
                        <div class="spotify-radio-card" style="background: <?php echo $pl['sfondo']; ?>; padding: 16px; border-radius: 8px; width: 175px; box-sizing: border-box; border: 1px solid rgba(255,255,255,0.05); cursor: pointer;">
                            <div style="display: flex; justify-content: center; margin-bottom: 12px;">
                                <img src="img/<?php echo htmlspecialchars($pl['immagine']); ?>" alt="" style="width: 120px; height: 120px; border-radius: 4px; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.5);" />
                            </div>
                            <p style="font-size: 15px; font-weight: bold; margin: 0 0 4px 0; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($pl['titolo']); ?></p>
                            <p style="color: #b3b3b3; font-size: 11px; line-height: 1.4; margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?php echo htmlspecialchars($pl['descrizione']); ?></p>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>

            <h2 style="font-size: 22px; font-weight: bold; margin: 0 0 16px 0;">Classifiche Globali e Italiane</h2>
            <div style="display: flex; gap: 18px; flex-wrap: wrap; margin-bottom: 40px;">
                <?php while ($chart = $res_classifiche->fetch_assoc()): ?>
                    <a href="playlist.php?id=<?php echo $chart['id']; ?>" style="text-decoration: none; color: inherit;">
                        <div class="spotify-radio-card" style="background: <?php echo $chart['sfondo']; ?>; padding: 16px; border-radius: 8px; width: 175px; box-sizing: border-box; border: 1px solid rgba(255,255,255,0.05); cursor: pointer;">
                            <div style="display: flex; justify-content: center; margin-bottom: 12px;">
                                <img src="img/<?php echo htmlspecialchars($chart['immagine']); ?>" alt="" style="width: 120px; height: 120px; border-radius: 4px; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.5);" />
                            </div>
                            <p style="font-size: 15px; font-weight: bold; margin: 0 0 4px 0; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($chart['titolo']); ?></p>
                            <p style="color: #b3b3b3; font-size: 11px; line-height: 1.4; margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?php echo htmlspecialchars($chart['descrizione']); ?></p>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>

            <h2 style="font-size: 22px; font-weight: bold; margin: 0 0 16px 0;">Stazioni radio più popolari</h2>
            <div style="display: flex; gap: 18px; flex-wrap: wrap; margin-bottom: 40px;">
                <?php foreach ($stazioni_radio as $radio): ?>
                    <div class="spotify-radio-card" style="background: <?php echo $radio['bg']; ?>; padding: 16px; border-radius: 8px; width: 175px; box-sizing: border-box; position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,0.05);">
                        <div style="display: flex; justify-content: flex-end; align-items: center; margin-bottom: 12px;">
                            <span style="background-color: rgba(0,0,0,0.4); color: #ffffff; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; letter-spacing: 1px;">RADIO</span>
                        </div>
                        <div style="display: flex; justify-content: center; margin-bottom: 14px;">
                            <img src="img/<?php echo htmlspecialchars($radio['img']); ?>" alt="" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.5);" />
                        </div>
                        <p style="font-size: 15px; font-weight: bold; margin: 0 0 4px 0; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($radio['nome']); ?></p>
                        <p style="color: #b3b3b3; font-size: 11px; line-height: 1.4; margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?php echo htmlspecialchars($radio['artisti']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <h2 style="font-size: 22px; font-weight: bold; margin: 0 0 16px 0;">Discografia in evidenza</h2>
            <div style="display: flex; gap: 18px; flex-wrap: wrap; margin-bottom: 40px;">
                <?php while ($alb = $res_albums->fetch_assoc()): ?>
                    <div class="spotify-radio-card" style="background-color: #181818; padding: 16px; border-radius: 8px; width: 175px; box-sizing: border-box; border: 1px solid rgba(255,255,255,0.05);">
                        <div style="display: flex; justify-content: center; margin-bottom: 12px;">
                            <img src="img/<?php echo htmlspecialchars($alb['copertina']); ?>" alt="" style="width: 143px; height: 143px; border-radius: 4px; object-fit: cover; box-shadow: 0 4px 12px rgba(0,0,0,0.4);" />
                        </div>
                        <p style="font-size: 14px; font-weight: bold; margin: 0 0 4px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #ffffff;"><?php echo htmlspecialchars($alb['album']); ?></p>
                        <p style="color: #b3b3b3; font-size: 12px; margin: 0;"><?php echo htmlspecialchars($alb['anno']) . ' • ' . htmlspecialchars($alb['artista']); ?></p>
                    </div>
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

        <div style="width: 320px; box-sizing: border-box; display: flex; flex-direction: column; gap: 28px; padding-right: 12px;">
            <div class="spotify-static-box" style="padding: 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <h3 style="font-size: 16px; font-weight: bold; margin: 0; color: #ffffff;">📰 Spotify Blog & News</h3>
                    <span style="font-size: 10px; color: #1db954; font-weight: bold; text-transform: uppercase;">Aggiornato</span>
                </div>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <?php foreach ($articoli_blog as $post): ?>
                        <a href="articolo.php?id=<?php echo $post['id']; ?>" style="text-decoration: none; color: inherit;">
                            <div class="blog-post-card">
                                <p style="font-size: 13px; font-weight: bold; margin: 0 0 6px 0; color: #ffffff; line-height: 1.3;"><?php echo htmlspecialchars($post['titolo']); ?></p>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 11px; color: #b3b3b3;"><?php echo htmlspecialchars($post['autore']); ?></span>
                                    <span style="font-size: 10px; color: #888888;"><?php echo htmlspecialchars($post['data']); ?></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="spotify-static-box" style="padding: 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <h3 style="font-size: 16px; font-weight: bold; margin: 0; color: #ffffff;">👥 Community Fan Club</h3>
                    <span style="font-size: 10px; color: #1db954; font-weight: bold; text-transform: uppercase;">Gruppi</span>
                </div>
                <p style="font-size: 12px; color: #b3b3b3; margin: 0 0 16px 0; line-height: 1.4;">Unisciti ai gruppi esclusivi dei tuoi artisti preferiti e chatta con gli altri fan.</p>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php foreach ($community_artisti as $comm): ?>
                        <div class="spotify-static-box" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 10px; border-radius: 6px; background-color: #202020;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="img/<?php echo htmlspecialchars($comm['img']); ?>" alt="" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;" />
                                <div>
                                    <p style="font-size: 12px; font-weight: bold; margin: 0 0 2px 0; color: #ffffff;"><?php echo htmlspecialchars($comm['artista']); ?></p>
                                    <p style="font-size: 10px; color: #b3b3b3; margin: 0;"><?php echo htmlspecialchars($comm['membri']); ?></p>
                                </div>
                            </div>
                            <a href="community.php?gruppo=<?php echo urlencode($comm['artista']); ?>" style="background-color: #1db954; color: #000000; padding: 6px 14px; border-radius: 500px; font-size: 11px; font-weight: bold; text-decoration: none; cursor: pointer;">Entra</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
<?php $conn->close(); ?>