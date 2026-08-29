<?php
session_start();
require_once 'connection.php';

$is_logged = isset($_SESSION['user']);
$username_corrente = $is_logged ? $_SESSION['user'] : 'Ospite';

$gruppo = isset($_GET['gruppo']) ? $_GET['gruppo'] : 'Fan Club Generale';

// Gestione invio nuovo messaggio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['testo_messaggio'])) {
    if ($is_logged) {
        $testo = trim($_POST['testo_messaggio']);
        if (!empty($testo)) {
            $stmt = $conn->prepare("INSERT INTO `messaggi_community` (artista_gruppo, username, messaggio) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $gruppo, $username_corrente, $testo);
            $stmt->execute();
            $stmt->close();
        }
    }
    header("Location: community.php?gruppo=" . urlencode($gruppo));
    exit();
}

// Recuperiamo TUTTI i messaggi salvati nel DB per questo gruppo (default + nuovi inviati)
$stmt_msg = $conn->prepare("SELECT username, messaggio, data_invio FROM `messaggi_community` WHERE artista_gruppo = ? ORDER BY id DESC LIMIT 50");
$stmt_msg->bind_param("s", $gruppo);
$stmt_msg->execute();
$res_messaggi = $stmt_msg->get_result();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify - Community Chat: <?php echo htmlspecialchars($gruppo); ?></title>
    <style type="text/css">
        .chat-input {
            width: 100%;
            background-color: #202020;
            border: 1px solid rgba(255,255,255,0.08);
            color: #ffffff;
            padding: 14px 18px;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 14px;
            outline: none;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .chat-input:focus {
            border-color: #1db954;
            background-color: #252525;
        }
        .chat-submit-btn {
            background-color: #1db954;
            color: #000000;
            border: none;
            padding: 12px 28px;
            border-radius: 500px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }
        .chat-submit-btn:hover {
            background-color: #1ed760;
            transform: scale(1.02);
        }
        .chat-message-card {
            background-color: #181818;
            padding: 16px 20px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.04);
            transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.15s ease;
        }
        .chat-message-card:hover {
            background-color: #1f1f1f;
            border-color: rgba(29, 185, 84, 0.25);
            transform: translateY(-1px);
        }
        .badge-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1db954 0%, #116b31 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 13px;
            color: #000000;
            box-shadow: 0 4px 10px rgba(0,0,0,0.4);
        }
    </style>
</head>
<body style="background: linear-gradient(180deg, #1f1f1f 0%, #121212 40%, #0a0a0a 100%); background-attachment: fixed; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0; min-height: 100vh;">

    <?php include 'menu.php'; ?>

    <div style="margin-left: 230px; padding: 32px; max-width: 900px;">
        
        <!-- Header della Community -->
        <div style="background-color: #181818; padding: 24px 28px; border-radius: 12px; margin-bottom: 24px; border: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: center; box-shadow: 0 8px 24px rgba(0,0,0,0.5);">
            <div>
                <span style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: #1db954; letter-spacing: 1px;">Community Ufficiale & Chat</span>
                <h1 style="font-size: 28px; font-weight: 900; margin: 6px 0 0 0; color: #ffffff;"><?php echo htmlspecialchars($gruppo); ?></h1>
            </div>
            <a href="homepage.php" style="color: #b3b3b3; text-decoration: none; font-size: 13px; font-weight: bold; cursor: pointer; transition: color 0.2s ease;">← Torna alla Home</a>
        </div>

        <!-- Sezione Scrivi Messaggio -->
        <?php if ($is_logged): ?>
            <div style="background-color: #181818; padding: 24px; border-radius: 12px; margin-bottom: 28px; border: 1px solid rgba(255,255,255,0.05); box-shadow: 0 4px 16px rgba(0,0,0,0.4);">
                <form action="community.php?gruppo=<?php echo urlencode($gruppo); ?>" method="POST">
                    <p style="font-size: 13px; color: #b3b3b3; margin: 0 0 12px 0;">Stai scrivendo nella chat come <strong style="color: #ffffff;"><?php echo htmlspecialchars($username_corrente); ?></strong></p>
                    <div style="display: flex; gap: 12px;">
                        <input type="text" name="testo_messaggio" placeholder="Scrivi un messaggio alla community..." autocomplete="off" required="required" class="chat-input" />
                        <button type="submit" class="chat-submit-btn">Invia</button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div style="background-color: #181818; padding: 18px; border-radius: 8px; margin-bottom: 28px; text-align: center; border: 1px solid rgba(255,255,255,0.05);">
                <p style="font-size: 13px; color: #b3b3b3; margin: 0;">Devi <a href="login.php" style="color: #1db954; font-weight: bold; text-decoration: none;">effettuare il login</a> per partecipare alla chat e scrivere messaggi.</p>
            </div>
        <?php endif; ?>

        <!-- Storico Messaggi -->
        <h2 style="font-size: 20px; font-weight: bold; margin-bottom: 16px; color: #ffffff;">Messaggi recenti</h2>
        <div style="display: flex; flex-direction: column; gap: 14px;">
            <?php if ($res_messaggi && $res_messaggi->num_rows > 0): ?>
                <?php while ($msg = $res_messaggi->fetch_assoc()): ?>
                    <div class="chat-message-card">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                            <div class="badge-avatar"><?php echo strtoupper(substr($msg['username'], 0, 1)); ?></div>
                            <div style="flex: 1; display: flex; justify-content: space-between; align-items: baseline;">
                                <span style="font-size: 14px; font-weight: 700; color: #1db954; letter-spacing: -0.2px;"><?php echo htmlspecialchars($msg['username']); ?></span>
                                <span style="font-size: 11px; color: #888888; font-weight: 500;"><?php echo htmlspecialchars($msg['data_invio']); ?></span>
                            </div>
                        </div>
                        <p style="font-size: 14px; color: #e1e1e1; margin: 0 0 0 44px; line-height: 1.5; word-break: break-word; font-weight: 400;"><?php echo htmlspecialchars($msg['messaggio']); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="background-color: #181818; padding: 24px; border-radius: 8px; text-align: center; border: 1px solid rgba(255,255,255,0.03);">
                    <p style="font-size: 14px; color: #b3b3b3; margin: 0;">Ancora nessun messaggio in questa community. Sii il primo a scriverne uno!</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>
<?php 
if (isset($stmt_msg)) {
    $stmt_msg->close();
}
$conn->close(); 
?>