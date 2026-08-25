<?php
session_start();
require_once 'connection.php';

$is_admin = (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin');
$cerca = isset($_GET['cerca']) ? trim($_GET['cerca']) : '';

// Gestione eliminazione album (lato admin)
if ($is_admin && isset($_GET['del_album'])) {
    $id_del = (int)$_GET['del_album'];
    $stmt_del = $conn->prepare("DELETE FROM `" . TAB_ALBUMS . "` WHERE id = ?");
    $stmt_del->bind_param('i', $id_del);
    $stmt_del->execute();
    $stmt_del->close();
    header('Location: discografia.php');
    exit();
}

// Gestione inserimento nuovo album (lato admin)
$msg_err = '';
if ($is_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aggiungi_album'])) {
    $artista_id = (int)$_POST['artista_id'];
    $titolo = trim($_POST['titolo']);
    $anno = (int)$_POST['anno'];
    $copertina = trim($_POST['copertina']);

    if (!empty($titolo) && $artista_id > 0 && $anno > 0) {
        if (empty($copertina)) { $copertina = 'album.png'; }
        $stmt_ins = $conn->prepare("INSERT INTO `" . TAB_ALBUMS . "` (artista_id, titolo, anno, copertina) VALUES (?, ?, ?, ?)");
        $stmt_ins->bind_param('isis', $artista_id, $titolo, $anno, $copertina);
        $stmt_ins->execute();
        $stmt_ins->close();
        header('Location: discografia.php');
        exit();
    } else {
        $msg_err = "Compila tutti i campi obbligatori per aggiungere l'album.";
    }
}

// Query di visualizzazione album
if (!empty($cerca)) {
    $stmt = $conn->prepare("SELECT alb.id AS album_id, alb.titolo AS album, alb.anno, alb.copertina, art.id AS artista_id, art.nome AS artista,
                            (SELECT COUNT(*) FROM `" . TAB_TRACKS . "` WHERE album_id = alb.id) AS tot_tracce
                            FROM `" . TAB_ALBUMS . "` alb 
                            JOIN `" . TAB_ARTISTS . "` art ON alb.artista_id = art.id 
                            WHERE alb.titolo LIKE ? OR art.nome LIKE ?
                            ORDER BY alb.anno DESC");
    $param = '%' . $cerca . '%';
    $stmt->bind_param('ss', $param, $param);
    $stmt->execute();
    $res_albums = $stmt->get_result();
} else {
    $sql = "SELECT alb.id AS album_id, alb.titolo AS album, alb.anno, alb.copertina, art.id AS artista_id, art.nome AS artista,
            (SELECT COUNT(*) FROM `" . TAB_TRACKS . "` WHERE album_id = alb.id) AS tot_tracce
            FROM `" . TAB_ALBUMS . "` alb 
            JOIN `" . TAB_ARTISTS . "` art ON alb.artista_id = art.id 
            ORDER BY alb.anno DESC";
    $res_albums = $conn->query($sql);
}

$res_art_list = $conn->query("SELECT id, nome FROM `" . TAB_ARTISTS . "` ORDER BY nome ASC");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Discografia Completa - Spotify</title>
    <style type="text/css">
        .spotify-album-card {
            transition: transform 0.25s ease, background-color 0.25s ease, box-shadow 0.25s ease;
            cursor: default;
        }
        .spotify-album-card:hover {
            transform: translateY(-6px);
            background-color: #282828 !important;
            box-shadow: 0 14px 28px rgba(0, 0, 0, 0.7);
        }
        .artist-tag-link {
            color: #b3b3b3;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .artist-tag-link:hover {
            color: #1db954;
            text-decoration: underline;
        }
    </style>
</head>
<body style="background-color: #121212; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0;">

    <?php include 'menu.php'; ?>

    <div style="margin-left: 230px; padding: 32px; max-width: 1200px;">
        
        <!-- Intestazione & Azioni Admin -->
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
            <div>
                <h1 style="font-size: 32px; font-weight: 900; margin: 0 0 8px 0; letter-spacing: -1px;">Discografia Completa</h1>
                <p style="color: #b3b3b3; font-size: 14px; margin: 0;">Esplora tutte le uscite ufficiali e gli album memorizzati nel database.</p>
            </div>

            <!-- Form di Ricerca -->
            <form action="discografia.php" method="get" style="display: flex; gap: 8px;">
                <input type="text" name="cerca" value="<?php echo htmlspecialchars($cerca); ?>" placeholder="Cerca album o artista..." style="padding: 10px 16px; border-radius: 500px; background-color: #242424; border: 1px solid #3e3e3e; color: #ffffff; font-size: 13px; outline: none; width: 200px;" />
                <input type="submit" value="Cerca" style="background-color: #1db954; color: #000000; border: none; padding: 10px 18px; border-radius: 500px; font-weight: bold; font-size: 12px; cursor: pointer; text-transform: uppercase;" />
                <?php if (!empty($cerca)): ?>
                    <a href="discografia.php" style="background-color: #333333; color: #ffffff; padding: 10px 14px; border-radius: 500px; text-decoration: none; font-size: 12px; font-weight: bold; display: flex; align-items: center;">Azzera</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Pannello Aggiunta Album (Visibile solo ad Admin) -->
        <?php if ($is_admin): ?>
            <div style="background-color: #181818; border: 1px solid #282828; border-radius: 8px; padding: 20px; margin-bottom: 32px;">
                <h3 style="font-size: 16px; font-weight: bold; margin: 0 0 14px 0; color: #1db954;">+ Aggiungi Nuovo Album (Admin)</h3>
                <?php if (!empty($msg_err)): ?>
                    <p style="color: #e22134; font-size: 13px; margin-bottom: 10px;"><?php echo $msg_err; ?></p>
                <?php endif; ?>
                <form action="discografia.php" method="post" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
                    <div>
                        <label style="display: block; font-size: 11px; color: #b3b3b3; margin-bottom: 4px;">Artista:</label>
                        <select name="artista_id" style="padding: 8px 12px; background-color: #242424; color: #ffffff; border: 1px solid #3e3e3e; border-radius: 4px; font-size: 13px;">
                            <?php while ($ar = $res_art_list->fetch_assoc()): ?>
                                <option value="<?php echo $ar['id']; ?>"><?php echo htmlspecialchars($ar['nome']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; color: #b3b3b3; margin-bottom: 4px;">Titolo Album:</label>
                        <input type="text" name="titolo" required style="padding: 8px 12px; background-color: #242424; color: #ffffff; border: 1px solid #3e3e3e; border-radius: 4px; font-size: 13px;" />
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; color: #b3b3b3; margin-bottom: 4px;">Anno:</label>
                        <input type="number" name="anno" value="2026" required style="padding: 8px 12px; background-color: #242424; color: #ffffff; border: 1px solid #3e3e3e; border-radius: 4px; font-size: 13px; width: 80px;" />
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; color: #b3b3b3; margin-bottom: 4px;">Nome file immagine (es: album.png):</label>
                        <input type="text" name="copertina" value="album.png" style="padding: 8px 12px; background-color: #242424; color: #ffffff; border: 1px solid #3e3e3e; border-radius: 4px; font-size: 13px;" />
                    </div>
                    <div>
                        <input type="submit" name="aggiungi_album" value="Salva Album" style="background-color: #1db954; color: #000000; border: none; padding: 9px 20px; border-radius: 500px; font-weight: bold; font-size: 12px; cursor: pointer; text-transform: uppercase;" />
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Griglia Album -->
        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <?php if ($res_albums && $res_albums->num_rows > 0): ?>
                <?php while ($alb = $res_albums->fetch_assoc()): ?>
                    <div class="spotify-album-card" style="background-color: #181818; padding: 16px; border-radius: 8px; width: 180px; box-sizing: border-box; display: flex; flex-direction: column; position: relative;">
                        <img src="img/<?php echo htmlspecialchars($alb['copertina']); ?>" alt="<?php echo htmlspecialchars($alb['album']); ?>" style="width: 148px; height: 148px; border-radius: 6px; object-fit: cover; margin-bottom: 14px; box-shadow: 0 6px 16px rgba(0,0,0,0.5);" />
                        
                        <p style="font-size: 15px; font-weight: bold; margin: 0 0 6px 0; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?php echo htmlspecialchars($alb['album']); ?>
                        </p>
                        
                        <p style="font-size: 13px; margin: 0 0 6px 0;">
                            <a href="artista.php?id=<?php echo $alb['artista_id']; ?>" class="artist-tag-link">
                                <?php echo htmlspecialchars($alb['artista']); ?>
                            </a>
                        </p>
                        
                        <p style="color: #727272; font-size: 12px; margin: auto 0 0 0; font-weight: 500;">
                            <?php echo htmlspecialchars($alb['anno']); ?> • <?php echo $alb['tot_tracce']; ?> brani
                        </p>

                        <!-- Tasto Elimina Album (Visibile solo ad Admin) -->
                        <?php if ($is_admin): ?>
                            <div style="margin-top: 10px; border-top: 1px solid #282828; padding-top: 8px; text-align: right;">
                                <a href="discografia.php?del_album=<?php echo $alb['album_id']; ?>" onclick="return confirm('Vuoi davvero eliminare questo album?');" style="color: #e22134; font-size: 11px; font-weight: bold; text-decoration: none;">Elimina</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="background-color: #181818; padding: 24px; border-radius: 8px; width: 100%;">
                    <p style="color: #b3b3b3; margin: 0;">Nessun album trovato.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>
<?php 
if (isset($stmt)) { $stmt->close(); }
$conn->close(); 
?>