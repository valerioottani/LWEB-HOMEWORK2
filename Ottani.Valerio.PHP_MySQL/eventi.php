<?php
session_start();
require_once 'connection.php';

$is_admin = (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin');

// Gestione eliminazione evento (lato admin)
if ($is_admin && isset($_GET['del_evento'])) {
    $id_del = (int)$_GET['del_evento'];
    $stmt_del = $conn->prepare("DELETE FROM `" . TAB_EVENTS . "` WHERE id = ?");
    $stmt_del->bind_param('i', $id_del);
    $stmt_del->execute();
    $stmt_del->close();
    header('Location: eventi.php');
    exit();
}

// Gestione inserimento nuovo evento (lato admin)
if ($is_admin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aggiungi_evento'])) {
    $giorno = (int)$_POST['giorno'];
    $mese = trim($_POST['mese']);
    $titolo = trim($_POST['titolo']);
    $luogo = trim($_POST['luogo']);

    if (!empty($titolo) && !empty($mese) && $giorno > 0) {
        $stmt_ins = $conn->prepare("INSERT INTO `" . TAB_EVENTS . "` (giorno, mese, titolo, luogo, link_biglietti) VALUES (?, ?, ?, ?, '#')");
        $stmt_ins->bind_param('isss', $giorno, $mese, $titolo, $luogo);
        $stmt_ins->execute();
        $stmt_ins->close();
        header('Location: eventi.php');
        exit();
    }
}

$res = $conn->query("SELECT * FROM `" . TAB_EVENTS . "` ORDER BY id ASC");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Eventi Live - Spotify</title>
    <style type="text/css">
        .event-row {
            transition: background-color 0.2s ease, transform 0.2s ease;
        }
        .event-row:hover {
            background-color: #242424 !important;
        }
    </style>
</head>
<body style="background-color: #121212; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0;">

    <?php include 'menu.php'; ?>

    <div style="margin-left: 230px; padding: 40px; display: flex; flex-direction: column; align-items: center;">
        
        <div style="width: 100%; max-width: 900px; text-align: left; margin-bottom: 24px;">
            <h1 style="font-size: 32px; font-weight: 900; margin: 0 0 8px 0; letter-spacing: -1px;">Eventi dal Vivo & Tour</h1>
            <p style="color: #b3b3b3; font-size: 14px; margin: 0;">Calendario dei prossimi concerti e live show degli artisti.</p>
        </div>

        <!-- Pannello Aggiunta Evento (Visibile solo ad Admin) -->
        <?php if ($is_admin): ?>
            <div style="background-color: #181818; border: 1px solid #282828; border-radius: 8px; padding: 20px; margin-bottom: 24px; width: 100%; max-width: 900px; box-sizing: border-box;">
                <h3 style="font-size: 16px; font-weight: bold; margin: 0 0 14px 0; color: #1db954;">+ Aggiungi Nuovo Concerto (Admin)</h3>
                <form action="eventi.php" method="post" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
                    <div>
                        <label style="display: block; font-size: 11px; color: #b3b3b3; margin-bottom: 4px;">Giorno:</label>
                        <input type="number" name="giorno" min="1" max="31" value="15" required style="padding: 8px 12px; background-color: #242424; color: #ffffff; border: 1px solid #3e3e3e; border-radius: 4px; font-size: 13px; width: 70px;" />
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; color: #b3b3b3; margin-bottom: 4px;">Mese (es: LUG):</label>
                        <input type="text" name="mese" maxlength="3" value="LUG" required style="padding: 8px 12px; background-color: #242424; color: #ffffff; border: 1px solid #3e3e3e; border-radius: 4px; font-size: 13px; width: 80px;" />
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; color: #b3b3b3; margin-bottom: 4px;">Titolo Tour / Evento:</label>
                        <input type="text" name="titolo" required style="padding: 8px 12px; background-color: #242424; color: #ffffff; border: 1px solid #3e3e3e; border-radius: 4px; font-size: 13px; width: 220px;" />
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; color: #b3b3b3; margin-bottom: 4px;">Location / Città:</label>
                        <input type="text" name="luogo" required style="padding: 8px 12px; background-color: #242424; color: #ffffff; border: 1px solid #3e3e3e; border-radius: 4px; font-size: 13px; width: 200px;" />
                    </div>
                    <div>
                        <input type="submit" name="aggiungi_evento" value="Salva Concerto" style="background-color: #1db954; color: #000000; border: none; padding: 9px 20px; border-radius: 500px; font-weight: bold; font-size: 12px; cursor: pointer; text-transform: uppercase;" />
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <div style="background-color: #181818; border-radius: 8px; padding: 20px; box-shadow: 0 8px 24px rgba(0,0,0,0.5); width: 100%; max-width: 900px; box-sizing: border-box;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 1px solid #282828; color: #b3b3b3; font-size: 12px; text-transform: uppercase;">
                        <th style="padding: 14px 16px; width: 18%;">Data</th>
                        <th style="padding: 14px 16px; width: 42%;">Tour / Evento</th>
                        <th style="padding: 14px 16px; width: 28%;">Location</th>
                        <?php if ($is_admin): ?>
                            <th style="padding: 14px 16px; text-align: right; width: 12%;">Azione</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($ev = $res->fetch_assoc()): ?>
                        <tr class="event-row" style="border-bottom: 1px solid #222222; font-size: 14px;">
                            <td style="padding: 16px;">
                                <div style="display: flex; align-items: baseline; gap: 6px;">
                                    <span style="font-size: 20px; font-weight: 900; color: #1db954;"><?php echo htmlspecialchars($ev['giorno']); ?></span>
                                    <span style="font-size: 12px; font-weight: bold; color: #ffffff; text-transform: uppercase;"><?php echo htmlspecialchars($ev['mese']); ?></span>
                                </div>
                            </td>
                            <td style="padding: 16px; font-weight: bold; color: #ffffff;">
                                <?php echo htmlspecialchars($ev['titolo']); ?>
                            </td>
                            <td style="padding: 16px; color: #b3b3b3; font-size: 13px;">
                                <?php echo htmlspecialchars($ev['luogo']); ?>
                            </td>
                            <?php if ($is_admin): ?>
                                <td style="padding: 16px; text-align: right;">
                                    <a href="eventi.php?del_evento=<?php echo $ev['id']; ?>" onclick="return confirm('Eliminare questo evento?');" style="color: #e22134; font-size: 11px; font-weight: bold; text-decoration: none;">Elimina</a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>
<?php $conn->close(); ?>