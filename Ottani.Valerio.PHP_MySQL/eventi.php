<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connection.php';

$res_eventi = $conn->query("SELECT * FROM `" . TAB_EVENTS . "` ORDER BY id ASC");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify - Eventi & Tour Live</title>
    <style type="text/css">
        .event-card {
            background-color: #181818;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.25s ease, background-color 0.25s ease, box-shadow 0.25s ease;
        }
        .event-card:hover {
            background-color: #222222;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.5);
        }
        .book-btn {
            background-color: #1db954;
            color: #000000;
            border: none;
            padding: 10px 22px;
            border-radius: 500px;
            font-weight: bold;
            font-size: 13px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }
        .book-btn:hover {
            background-color: #1ed760;
            transform: scale(1.03);
        }
    </style>
</head>
<body style="background: linear-gradient(180deg, #1f1f1f 0%, #121212 40%, #0a0a0a 100%); background-attachment: fixed; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0; min-height: 100vh;">

    <?php include 'menu.php'; ?>

    <!-- Contenitore principale con compensazione menu laterale e centratura interna -->
    <div style="margin-left: 230px; padding: 40px 24px; box-sizing: border-box;">
        
        <div style="max-width: 850px; margin: 0 auto;">
            
            <div style="margin-bottom: 32px; text-align: center;">
                <h1 style="font-size: 32px; font-weight: 900; margin: 0 0 8px 0; letter-spacing: -1px;">Eventi & Tour Live</h1>
                <p style="color: #b3b3b3; margin: 0; font-size: 14px;">Scopri i concerti in programma, seleziona il tuo posto preferito e acquista il biglietto.</p>
            </div>

            <div style="display: flex; flex-direction: column; gap: 16px;">
                <?php if ($res_eventi && $res_eventi->num_rows > 0): ?>
                    <?php while ($ev = $res_eventi->fetch_assoc()): ?>
                        <div class="event-card">
                            <div style="display: flex; align-items: center; gap: 24px;">
                                <div style="background-color: #282828; padding: 12px 18px; border-radius: 6px; text-align: center; border: 1px solid rgba(255,255,255,0.05);">
                                    <span style="display: block; font-size: 20px; font-weight: 900; color: #1db954;"><?php echo htmlspecialchars($ev['giorno']); ?></span>
                                    <span style="display: block; font-size: 11px; text-transform: uppercase; color: #b3b3b3; font-weight: bold;"><?php echo htmlspecialchars($ev['mese']); ?></span>
                                </div>
                                <div>
                                    <h3 style="font-size: 16px; font-weight: bold; margin: 0 0 6px 0; color: #ffffff;"><?php echo htmlspecialchars($ev['titolo']); ?></h3>
                                    <p style="font-size: 13px; color: #b3b3b3; margin: 0;"><?php echo htmlspecialchars($ev['luogo']); ?></p>
                                </div>
                            </div>
                            <div>
                                <a href="prenota_evento.php?id=<?php echo $ev['id']; ?>" class="book-btn">Scegli Posto & Acquista</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="background-color: #181818; padding: 24px; border-radius: 8px; text-align: center;">
                        <p style="color: #b3b3b3; font-size: 14px; margin: 0;">Nessun evento disponibile al momento.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </div>

</body>
</html>
<?php $conn->close(); ?>