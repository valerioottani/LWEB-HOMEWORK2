<?php
session_start();
require_once 'connection.php';

$id_artista = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$is_admin = (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin');

// Gestione eliminazione brano (lato admin)
if ($is_admin && isset($_GET['del_brano'])) {
    $id_brano = (int)$_GET['del_brano'];
    $stmt_del = $conn->prepare("DELETE FROM `" . TAB_TRACKS . "` WHERE id = ?");
    $stmt_del->bind_param('i', $id_brano);
    $stmt_del->execute();
    $stmt_del->close();
    header("Location: artista.php?id=$id_artista");
    exit();
}

// Gestione inserimento nuovo brano (lato admin)
if ($is_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aggiungi_brano'])) {
    $titolo_brano = trim($_POST['titolo']);
    $durata = trim($_POST['durata']);
    $immagine_brano = trim($_POST['immagine_brano']);
    
    // Trova o crea un album di default per l'artista
    $res_alb = $conn->query("SELECT id FROM `" . TAB_ALBUMS . "` WHERE artista_id = $id_artista LIMIT 1");
    if ($res_alb && $row_a = $res_alb->fetch_assoc()) {
        $album_id = $row_a['id'];
    } else {
        $conn->query("INSERT INTO `" . TAB_ALBUMS . "` (artista_id, titolo, anno, copertina) VALUES ($id_artista, 'Raccolta Ufficiale', 2026, 'album.png')");
        $album_id = $conn->insert_id;
    }

    if (!empty($titolo_brano)) {
        if (empty($durata)) { $durata = '3:00'; }
        if (empty($immagine_brano)) { $immagine_brano = 'album.png'; }
        
        $stmt_t = $conn->prepare("INSERT INTO `" . TAB_TRACKS . "` (album_id, titolo, durata, immagine_brano) VALUES (?, ?, ?, ?)");
        $stmt_t->bind_param('isss', $album_id, $titolo_brano, $durata, $immagine_brano);
        $stmt_t->execute();
        $stmt_t->close();
        header("Location: artista.php?id=$id_artista");
        exit();
    }
}

// Recupero dati artista
$stmt = $conn->prepare("SELECT * FROM `" . TAB_ARTISTS . "` WHERE id = ?");
$stmt->bind_param('i', $id_artista);
$stmt->execute();
$res_artista = $stmt->get_result();

if ($res_artista->num_rows === 0) {
    header('Location: artisti.php');
    exit();
}
$artista = $res_artista->fetch_assoc();
$stmt->close();

// Recupero brani
$sql_brani = "SELECT t.id AS track_id, t.titolo, t.durata, t.immagine_brano, a.titolo AS album_nome 
              FROM `" . TAB_TRACKS . "` t 
              JOIN `" . TAB_ALBUMS . "` a ON t.album_id = a.id 
              WHERE a.artista_id = ? 
              ORDER BY t.id ASC";

$stmt_b = $conn->prepare($sql_brani);
$stmt_b->bind_param('i', $id_artista);
$stmt_b->execute();
$res_brani = $stmt_b->get_result();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?php echo htmlspecialchars($artista['nome']); ?> - Spotify</title>
    <style type="text/css">
        .track-row {
            transition: background-color 0.25s ease, transform 0.25s ease;
            cursor: default;
        }
        .track-row:hover {
            background-color: #282828 !important;
            transform: translateY(-2px);
        }
    </style>
</head>
<body style="background-color: #121212; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0;">

    <?php include 'menu.php'; ?>

    <div style="margin-left: 230px; max-width: 1200px;">
        
        <!-- Hero Header Artista -->
        <div style="background: linear-gradient(180deg, #404040 0%, #181818 100%); padding: 48px 32px 32px 32px; display: flex; align-items: flex-end; gap: 32px;">
            <img src="img/<?php echo htmlspecialchars($artista['immagine']); ?>" alt="<?php echo htmlspecialchars($artista['nome']); ?>" style="width: 180px; height: 180px; border-radius: 50%; object-fit: cover; box-shadow: 0 8px 32px rgba(0,0,0,0.6);" />
            <div>
                <span style="font-size: 12px; font-weight: bold; text-transform: uppercase; color: #1db954; letter-spacing: 1px;">Artista Verificato</span>
                <h1 style="font-size: 56px; font-weight: 900; margin: 8px 0; letter-spacing: -2px;"><?php echo htmlspecialchars($artista['nome']); ?></h1>
                <p style="color: #b3b3b3; font-size: 14px; margin: 0; max-width: 700px; line-height: 1.5;"><?php echo htmlspecialchars($artista['biografia']); ?></p>
            </div>
        </div>

        <!-- Controlli -->
        <div style="padding: 24px 32px; display: flex; align-items: center; gap: 24px;">
            <div style="width: 56px; height: 56px; background-color: #1db954; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 16px rgba(0,0,0,0.4); cursor: pointer;">
                <span style="color: #000000; font-size: 24px; margin-left: 4px;">▶</span>
            </div>
            <a href="artisti.php" style="background-color: transparent; border: 1px solid #727272; color: #ffffff; padding: 8px 24px; border-radius: 500px; text-decoration: none; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">Torna agli Artisti</a>
        </div>

        <!-- Pannello Aggiunta Brano (Visibile solo ad Admin) -->
        <?php if ($is_admin): ?>
            <div style="margin: 0 32px 24px 32px; background-color: #181818; border: 1px solid #282828; border-radius: 8px; padding: 20px;">
                <h3 style="font-size: 16px; font-weight: bold; margin: 0 0 14px 0; color: #1db954;">+ Aggiungi Brano a <?php echo htmlspecialchars($artista['nome']); ?> (Admin)</h3>
                <form action="artista.php?id=<?php echo $id_artista; ?>" method="post" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
                    <div>
                        <label style="display: block; font-size: 11px; color: #b3b3b3; margin-bottom: 4px;">Titolo Brano:</label>
                        <input type="text" name="titolo" required style="padding: 8px 12px; background-color: #242424; color: #ffffff; border: 1px solid #3e3e3e; border-radius: 4px; font-size: 13px;" />
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; color: #b3b3b3; margin-bottom: 4px;">Durata (es: 3:30):</label>
                        <input type="text" name="durata" value="3:15" style="padding: 8px 12px; background-color: #242424; color: #ffffff; border: 1px solid #3e3e3e; border-radius: 4px; font-size: 13px; width: 80px;" />
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; color: #b3b3b3; margin-bottom: 4px;">Immagine (es: album.png):</label>
                        <input type="text" name="immagine_brano" value="<?php echo htmlspecialchars($artista['immagine']); ?>" style="padding: 8px 12px; background-color: #242424; color: #ffffff; border: 1px solid #3e3e3e; border-radius: 4px; font-size: 13px;" />
                    </div>
                    <div>
                        <input type="submit" name="aggiungi_brano" value="Salva Brano" style="background-color: #1db954; color: #000000; border: none; padding: 9px 20px; border-radius: 500px; font-weight: bold; font-size: 12px; cursor: pointer; text-transform: uppercase;" />
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Sezione Brani -->
        <div style="padding: 0 32px 48px 32px;">
            <h2 style="font-size: 24px; font-weight: bold; margin: 0 0 20px 0;">Brani più popolari</h2>

            <div style="background-color: #181818; border-radius: 8px; padding: 16px;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 1px solid #282828; color: #b3b3b3; font-size: 12px; text-transform: uppercase;">
                            <th style="padding: 12px 16px; width: 40px;">#</th>
                            <th style="padding: 12px 16px;">Titolo e Copertina</th>
                            <th style="padding: 12px 16px;">Album</th>
                            <th style="padding: 12px 16px; text-align: right; width: 80px;">Durata</th>
                            <?php if ($is_admin): ?>
                                <th style="padding: 12px 16px; text-align: right; width: 80px;">Azione</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $num = 1;
                        while ($tr = $res_brani->fetch_assoc()): 
                        ?>
                            <tr class="track-row" style="border-bottom: 1px solid #222222; font-size: 14px;">
                                <td style="padding: 14px 16px; color: #b3b3b3; font-weight: bold;"><?php echo $num++; ?></td>
                                <td style="padding: 14px 16px; display: flex; align-items: center; gap: 14px;">
                                    <img src="img/<?php echo htmlspecialchars($tr['immagine_brano']); ?>" alt="Cover" style="width: 44px; height: 44px; border-radius: 4px; object-fit: cover;" />
                                    <span style="font-weight: bold; color: #ffffff;"><?php echo htmlspecialchars($tr['titolo']); ?></span>
                                </td>
                                <td style="padding: 14px 16px; color: #b3b3b3;"><?php echo htmlspecialchars($tr['album_nome']); ?></td>
                                <td style="padding: 14px 16px; text-align: right; color: #b3b3b3;"><?php echo htmlspecialchars($tr['durata']); ?></td>
                                <?php if ($is_admin): ?>
                                    <td style="padding: 14px 16px; text-align: right;">
                                        <a href="artista.php?id=<?php echo $id_artista; ?>&del_brano=<?php echo $tr['track_id']; ?>" onclick="return confirm('Eliminare questo brano?');" style="color: #e22134; font-size: 11px; font-weight: bold; text-decoration: none;">Elimina</a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>
<?php 
$stmt_b->close();
$conn->close(); 
?>