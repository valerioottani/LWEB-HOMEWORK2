<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connection.php';

$is_logged = isset($_SESSION['user']);
$username_corrente = $is_logged ? $_SESSION['user'] : '';
$messaggio = '';

$album_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Gestione acquisto merchandise da questa pagina
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione']) && $_POST['azione'] === 'acquista_merch') {
    if (!$is_logged) {
        header('Location: login.php');
        exit();
    }
    
    $merchandise_id = (int)$_POST['merchandise_id'];
    
    $stmt_acq = $conn->prepare("INSERT INTO `acquisti_merch` (username, merchandise_id) VALUES (?, ?)");
    if ($stmt_acq) {
        $stmt_acq->bind_param("si", $username_corrente, $merchandise_id);
        if ($stmt_acq->execute()) {
            $messaggio = "Prodotto acquistato con successo e aggiunto al tuo storico!";
        } else {
            $messaggio = "Errore durante l'acquisto.";
        }
        $stmt_acq->close();
    }
}

// Recupero dati dell'album e dell'artista
$stmt_alb = $conn->prepare("SELECT a.id, a.titolo, a.anno, a.copertina, art.nome AS artista FROM `" . TAB_ALBUMS . "` a JOIN `" . TAB_ARTISTS . "` art ON a.artista_id = art.id WHERE a.id = ?");
$stmt_alb->bind_param("i", $album_id);
$stmt_alb->execute();
$album = $stmt_alb->get_result()->fetch_assoc();
$stmt_alb->close();

if (!$album) {
    echo "<p style='color: white; padding: 40px;'>Album non trovato.</p>";
    exit();
}

// Recupero tracce dell'album
$res_tracce = $conn->query("SELECT * FROM `" . TAB_TRACKS . "` WHERE album_id = $album_id");

// Recupero merchandise dell'album
$res_merch = $conn->query("SELECT * FROM `merchandise_album` WHERE album_id = $album_id");

// Recupero storico acquisti utente per la colonna di destra
$storico_acquisti = [];
if ($is_logged) {
    $stmt_st = $conn->prepare("
        SELECT m.tipo_prodotto, m.prezzo, m.immagine_prodotto, a.acq_data 
        FROM (
            SELECT merchandise_id, data_acquisto AS acq_data FROM `acquisti_merch` WHERE username = ?
        ) a 
        JOIN `merchandise_album` m ON a.merchandise_id = m.id 
        ORDER BY a.acq_data DESC
    ");
    if ($stmt_st) {
        $stmt_st->bind_param("s", $username_corrente);
        $stmt_st->execute();
        $res_st = $stmt_st->get_result();
        while ($row = $res_st->fetch_assoc()) {
            $storico_acquisti[] = $row;
        }
        $stmt_st->close();
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify - <?php echo htmlspecialchars($album['titolo']); ?></title>
    <style type="text/css">
        .merch-card {
            background-color: #181818;
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 8px;
            padding: 14px;
            width: 170px;
            box-sizing: border-box;
            text-align: center;
        }
        .storico-item {
            background-color: #181818;
            border: 1px solid rgba(255,255,255,0.04);
            border-radius: 8px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body style="background: linear-gradient(180deg, #1f1f1f 0%, #121212 40%, #0a0a0a 100%); background-attachment: fixed; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0; min-height: 100vh;">

    <?php include 'menu.php'; ?>

    <div style="margin-left: 230px; padding: 32px; display: flex; gap: 30px; box-sizing: border-box;">
        
        <!-- COLONNA SINISTRA: Dettaglio Album, Tracce e Merch -->
        <div style="flex: 1; max-width: 900px;">
            <a href="discografia.php" style="color: #b3b3b3; text-decoration: none; font-size: 13px; font-weight: bold; display: inline-block; margin-bottom: 20px;">← Torna alla Discografia</a>

            <?php if (!empty($messaggio)): ?>
                <div style="background-color: rgba(29,185,84,0.15); border: 1px solid #1db954; color: #1db954; padding: 12px 16px; border-radius: 6px; margin-bottom: 24px; font-size: 13px;">
                    <?php echo htmlspecialchars($messaggio); ?>
                </div>
            <?php endif; ?>

            <!-- Intestazione Album -->
            <div style="display: flex; gap: 24px; align-items: flex-end; margin-bottom: 35px;">
                <img src="img/<?php echo htmlspecialchars($album['copertina']); ?>" alt="" style="width: 180px; height: 180px; border-radius: 8px; object-fit: cover; box-shadow: 0 8px 24px rgba(0,0,0,0.6);" />
                <div>
                    <span style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: #1db954; letter-spacing: 1px;">Album</span>
                    <h1 style="font-size: 36px; font-weight: 900; margin: 8px 0; color: #fff;"><?php echo htmlspecialchars($album['titolo']); ?></h1>
                    <p style="font-size: 15px; font-weight: bold; color: #fff; margin: 0 0 6px 0;"><?php echo htmlspecialchars($album['artista']); ?> • <span style="color: #b3b3b3; font-weight: normal;"><?php echo htmlspecialchars($album['anno']); ?></span></p>
                </div>
            </div>

            <!-- Lista Tracce -->
            <h2 style="font-size: 20px; font-weight: bold; margin-bottom: 16px;">Tracce dell'Album</h2>
            <div style="background-color: #181818; border-radius: 8px; padding: 16px; margin-bottom: 40px; border: 1px solid rgba(255,255,255,0.04);">
                <?php while ($tr = $res_tracce->fetch_assoc()): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; border-bottom: 1px solid rgba(255,255,255,0.03);">
                        <span style="font-size: 14px; color: #fff; font-weight: 500;"><?php echo htmlspecialchars($tr['titolo']); ?></span>
                        <span style="font-size: 12px; color: #b3b3b3;"><?php echo htmlspecialchars($tr['durata']); ?></span>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Merchandise dell'Album -->
            <h2 style="font-size: 20px; font-weight: bold; margin-bottom: 16px;">Merchandise Ufficiale</h2>
            <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                <?php if ($res_merch && $res_merch->num_rows > 0): ?>
                    <?php while ($m = $res_merch->fetch_assoc()): ?>
                        <div class="merch-card">
                            <img src="img/<?php echo htmlspecialchars($m['immagine_prodotto']); ?>" alt="" style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px; margin-bottom: 8px;" />
                            <p style="font-size: 12px; font-weight: bold; margin: 0 0 4px 0; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($m['tipo_prodotto']); ?></p>
                            <p style="font-size: 13px; color: #1db954; font-weight: bold; margin: 0 0 10px 0;">€ <?php echo number_format($m['prezzo'], 2); ?></p>
                            
                            <?php if ($is_logged): ?>
                                <form action="album.php?id=<?php echo $album_id; ?>" method="POST">
                                    <input type="hidden" name="azione" value="acquista_merch" />
                                    <input type="hidden" name="merchandise_id" value="<?php echo $m['id']; ?>" />
                                    <button type="submit" style="background-color: #1db954; color: #000; border: none; padding: 6px 14px; border-radius: 500px; font-size: 11px; font-weight: bold; cursor: pointer; width: 100%;">Acquista</button>
                                </form>
                            <?php else: ?>
                                <a href="login.php" style="display: block; background-color: #333; color: #fff; text-decoration: none; padding: 6px 0; border-radius: 500px; font-size: 11px; font-weight: bold;">Accedi</a>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="font-size: 13px; color: #b3b3b3;">Nessun prodotto disponibile per questo album.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- COLONNA DESTRA: Storico Acquisti Merchandise -->
        <div style="width: 320px; box-sizing: border-box;">
            <div style="background-color: #161616; padding: 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); position: sticky; top: 32px;">
                <h3 style="font-size: 16px; font-weight: bold; margin-top: 0; margin-bottom: 6px; color: #fff;">📦 Storico Acquisti</h3>
                <p style="font-size: 12px; color: #b3b3b3; margin-bottom: 16px;">I prodotti di merchandise acquistati dal tuo account.</p>

                <?php if (!$is_logged): ?>
                    <div style="text-align: center; padding: 20px 0;">
                        <p style="font-size: 12px; color: #888; margin-bottom: 12px;">Accedi per visualizzare il tuo storico acquisti.</p>
                        <a href="login.php" style="background-color: #1db954; color: #000; padding: 8px 18px; border-radius: 500px; font-size: 12px; font-weight: bold; text-decoration: none;">Accedi</a>
                    </div>
                <?php elseif (empty($storico_acquisti)): ?>
                    <p style="font-size: 13px; color: #777; text-align: center; padding: 20px 0;">Non hai ancora acquistato alcun prodotto.</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column;">
                        <?php foreach ($storico_acquisti as $item): ?>
                            <div class="storico-item">
                                <img src="img/<?php echo htmlspecialchars($item['immagine_prodotto']); ?>" alt="" style="width: 45px; height: 45px; border-radius: 4px; object-fit: cover;" />
                                <div style="flex: 1; overflow: hidden;">
                                    <p style="font-size: 12px; font-weight: bold; margin: 0 0 2px 0; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($item['tipo_prodotto']); ?></p>
                                    <p style="font-size: 11px; color: #1db954; font-weight: bold; margin: 0 0 2px 0;">€ <?php echo number_format($item['prezzo'], 2); ?></p>
                                    <p style="font-size: 10px; color: #888; margin: 0;"><?php echo htmlspecialchars($item['acq_data']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</body>
</html>
<?php 
if (isset($conn) && !$conn->connect_error) {
    $conn->close(); 
}
?>