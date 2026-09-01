<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connection.php';

$is_logged = isset($_SESSION['user']);
$ruolo = isset($_SESSION['ruolo']) ? $_SESSION['ruolo'] : '';

$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'tutti';

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
        .disabled-btn {
            background-color: #333333;
            color: #888888;
            padding: 10px 22px;
            border-radius: 500px;
            font-weight: bold;
            font-size: 13px;
            display: inline-block;
            cursor: default;
            user-select: none;
            text-align: center;
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
        .filter-btn {
            background-color: #282828;
            color: #ffffff;
            padding: 8px 16px;
            border-radius: 500px;
            font-size: 12px;
            font-weight: bold;
            text-decoration: none;
            border: 1px solid rgba(255,255,255,0.1);
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }
        .filter-btn:hover, .filter-btn.active {
            background-color: #1db954;
            color: #000000;
            border-color: #1db954;
        }
    </style>
</head>
<body style="background: linear-gradient(180deg, #1f1f1f 0%, #121212 40%, #0a0a0a 100%); background-attachment: fixed; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0; min-height: 100vh;">

    <?php include 'menu.php'; ?>

    <!-- Contenitore principale con compensazione menu laterale e centratura interna -->
    <div style="margin-left: 230px; padding: 40px 24px; box-sizing: border-box;">
        
        <div style="max-width: 850px; margin: 0 auto;">
            
            <!-- Intestazione con titolo e pulsante admin per la gestione eventi -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div>
                    <h1 style="font-size: 32px; font-weight: 900; margin: 0 0 8px 0; letter-spacing: -1px;">Eventi & Tour Live</h1>
                    <p style="color: #b3b3b3; margin: 0; font-size: 14px;">Scopri i concerti in programma, seleziona il tuo posto preferito e acquista il biglietto.</p>
                </div>

                <?php if ($is_logged && $ruolo === 'admin'): ?>
                    <div>
                        <a href="gestione_eventi.php" class="admin-link-btn">⚙️ Gestisci Eventi</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- SEZIONE FILTRI -->
            <div style="display: flex; gap: 10px; margin-bottom: 28px; align-items: center; background-color: #181818; padding: 14px 20px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05);">
                <span style="font-size: 12px; font-weight: bold; color: #b3b3b3; text-transform: uppercase; margin-right: 10px;">Filtra Eventi:</span>
                <a href="eventi.php?filtro=tutti" class="filter-btn <?php echo ($filtro === 'tutti') ? 'active' : ''; ?>">Tutti</a>
                <a href="eventi.php?filtro=programma" class="filter-btn <?php echo ($filtro === 'programma') ? 'active' : ''; ?>">In Programma</a>
                <a href="eventi.php?filtro=futuri" class="filter-btn <?php echo ($filtro === 'futuri') ? 'active' : ''; ?>">Futuri</a>
                <a href="eventi.php?filtro=passati" class="filter-btn <?php echo ($filtro === 'passati') ? 'active' : ''; ?>">Passati</a>
            </div>

            <div style="display: flex; flex-direction: column; gap: 16px;">
                <?php 
                $Trovati = 0;
                if ($res_eventi && $res_eventi->num_rows > 0): 
                    while ($ev = $res_eventi->fetch_assoc()):
                        // Determinazione dello stato dell'evento in base all'ID (per scopi dimostrativi dei filtri)
                        $is_passato = ($ev['id'] <= 3);
                        $is_futuro = ($ev['id'] > 7);
                        $is_programma = (!$is_passato && !$is_futuro);

                        $mostra = true;
                        if ($filtro === 'passati' && !$is_passato) { $mostra = false; }
                        if ($filtro === 'futuri' && !$is_futuro) { $mostra = false; }
                        if ($filtro === 'programma' && !$is_programma) { $mostra = false; }
                        
                        if ($mostra):
                            $Trovati++;
                ?>
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
                                <?php if ($is_passato): ?>
                                    <div class="disabled-btn">Evento Concluso</div>
                                <?php elseif ($is_futuro): ?>
                                    <div class="disabled-btn" style="background-color: #222; color: #1db954; border: 1px dashed #1db954;">Disponibilità in Arrivo</div>
                                <?php else: ?>
                                    <a href="prenota_evento.php?id=<?php echo $ev['id']; ?>" class="book-btn">Scegli Posto & Acquista</a>
                                <?php endif; ?>
                            </div>
                        </div>
                <?php 
                        endif;
                    endwhile; 
                endif; 
                
                if ($Trovati === 0):
                ?>
                    <div style="background-color: #181818; padding: 24px; border-radius: 8px; text-align: center;">
                        <p style="color: #b3b3b3; font-size: 14px; margin: 0;">Nessun evento disponibile per questa categoria.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </div>

</body>
</html>
<?php $conn->close(); ?>