<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connection.php';

$is_logged = isset($_SESSION['user']);
$username_corrente = $is_logged ? $_SESSION['user'] : '';
$ruolo = isset($_SESSION['ruolo']) ? $_SESSION['ruolo'] : '';

// 1. Recupero di tutti gli album dal database
$res_albums = $conn->query("SELECT a.id, a.titolo AS album, a.anno, a.copertina, art.nome AS artista 
                            FROM `" . TAB_ALBUMS . "` a 
                            JOIN `" . TAB_ARTISTS . "` art ON a.artista_id = art.id 
                            ORDER BY a.anno DESC, a.id DESC");

// 2. Recupero dello storico acquisti merchandise dell'utente loggato
$res_acquisti = null;
if ($is_logged) {
    $stmt_acq = $conn->prepare("SELECT acq.data_acquisto, m.tipo_prodotto, m.prezzo, m.immagine_prodotto, alb.titolo AS album_titolo 
                                FROM `acquisti_merch` acq 
                                JOIN `merchandise_album` m ON acq.merchandise_id = m.id 
                                JOIN `" . TAB_ALBUMS . "` alb ON m.album_id = alb.id 
                                WHERE acq.username = ? 
                                ORDER BY acq.data_acquisto DESC");
    if ($stmt_acq) {
        $stmt_acq->bind_param("s", $username_corrente);
        $stmt_acq->execute();
        $res_acquisti = $stmt_acq->get_result();
        $stmt_acq->close();
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify - Discografia & Storico Acquisti</title>
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
        /* Effetto in rilievo al passaggio del cursore sulle righe dello storico */
        .acq-row {
            transition: background-color 0.25s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .acq-row:hover {
            background-color: #2a2a2a !important;
            transform: scale(1.01);
            box-shadow: 0 4px 16px rgba(0,0,0,0.6);
            position: relative;
            z-index: 2;
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

        <!-- SEZIONE STORICO ACQUISTI MERCHANDISE -->
        <?php if ($is_logged): ?>
            <div style="margin-top: 50px; margin-bottom: 40px;">
                <h2 style="font-size: 22px; font-weight: bold; margin-bottom: 16px; color: #ffffff;"> Storico Acquisti Merchandise</h2>
                
                <div style="background-color: #181818; border-radius: 8px; padding: 20px; border: 1px solid rgba(255,255,255,0.05);">
                    <?php if ($res_acquisti && $res_acquisti->num_rows > 0): ?>
                        <table style="width: 100%; border-collapse: separate; border-spacing: 0 6px; text-align: left; font-size: 14px;">
                            <thead>
                                <tr style="color: #b3b3b3; font-size: 11px; text-transform: uppercase;">
                                    <th style="padding: 10px; width: 60px;">Prodotto</th>
                                    <th style="padding: 10px;">Articolo / Tipologia</th>
                                    <th style="padding: 10px;">Album Collegato</th>
                                    <th style="padding: 10px;">Data Ordine</th>
                                    <th style="padding: 10px; text-align: right;">Prezzo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($acq = $res_acquisti->fetch_assoc()): ?>
                                    <tr class="acq-row" style="background-color: #202020; border-radius: 6px;">
                                        <td style="padding: 12px 10px; border-top-left-radius: 6px; border-bottom-left-radius: 6px;">
                                            <img src="img/<?php echo htmlspecialchars($acq['immagine_prodotto']); ?>" alt="" style="width: 40px; height: 40px; border-radius: 4px; object-fit: contain; background-color: #222;" />
                                        </td>
                                        <td style="padding: 12px 10px; font-weight: bold; color: #ffffff;"><?php echo htmlspecialchars($acq['tipo_prodotto']); ?></td>
                                        <td style="padding: 12px 10px; color: #b3b3b3;"><?php echo htmlspecialchars($acq['album_titolo']); ?></td>
                                        <td style="padding: 12px 10px; color: #888888; font-size: 13px;"><?php echo htmlspecialchars($acq['data_acquisto']); ?></td>
                                        <td style="padding: 12px 10px; text-align: right; font-weight: bold; color: #1db954; border-top-right-radius: 6px; border-bottom-right-radius: 6px;">€ <?php echo number_format($acq['prezzo'], 2, ',', '.'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="color: #b3b3b3; font-size: 14px; margin: 0; text-align: center; padding: 10px;">Non hai ancora effettuato alcun acquisto di merchandise. Visita la pagina di un album per comprare vinili, CD o felpe!</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

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