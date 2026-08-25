<?php
session_start();
require_once 'connection.php';

// Controllo ruoli
if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$msg = '';

// Inserimento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'insert') {
    $nome = trim($_POST['nome']);
    $biografia = trim($_POST['biografia']);
    $immagine = trim($_POST['immagine']);

    if (!empty($nome) && !empty($biografia)) {
        $stmt = $conn->prepare("INSERT INTO `" . TAB_ARTISTS . "` (nome, biografia, immagine) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $nome, $biografia, $immagine);
        if ($stmt->execute()) {
            $msg = 'Artista inserito con successo!';
        } else {
            $msg = 'Errore: nome artista già presente.';
        }
        $stmt->close();
    }
}

// Eliminazione
if (isset($_GET['delete'])) {
    $id_del = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM `" . TAB_ARTISTS . "` WHERE id = ?");
    $stmt->bind_param('i', $id_del);
    $stmt->execute();
    $stmt->close();
    header('Location: admin.php');
    exit();
}

$res_artists = $conn->query("SELECT * FROM `" . TAB_ARTISTS . "` ORDER BY id ASC");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Pannello Admin - Spotify</title>
</head>
<body style="background-color: #121212; color: #ffffff; font-family: Arial, sans-serif; margin: 0; padding: 0;">

    <?php include 'menu.php'; ?>

    <div style="margin-left: 230px; padding: 32px;">
        <h1 style="font-size: 32px; margin: 0 0 8px 0;">Pannello Amministrazione</h1>
        <p style="color: #b3b3b3; font-size: 14px; margin: 0 0 24px 0;">Gestione artisti riservata all'amministratore.</p>

        <?php if (!empty($msg)): ?>
            <div style="background-color: #1db954; color: #000000; padding: 10px; border-radius: 4px; font-weight: bold; margin-bottom: 20px;">
                <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

        <div style="background-color: #181818; padding: 24px; border-radius: 8px; margin-bottom: 32px; max-width: 600px;">
            <h2 style="font-size: 18px; margin: 0 0 16px 0;">Aggiungi Nuovo Artista</h2>
            <form action="admin.php" method="post">
                <input type="hidden" name="action" value="insert" />
                <div style="margin-bottom: 12px;">
                    <label for="nome" style="font-size: 12px; color: #b3b3b3;">Nome Artista</label><br />
                    <input type="text" id="nome" name="nome" style="width: 100%; padding: 8px; background-color: #282828; border: 1px solid #444; color: #fff; border-radius: 4px; margin-top: 4px;" required="required" />
                </div>
                <div style="margin-bottom: 12px;">
                    <label for="biografia" style="font-size: 12px; color: #b3b3b3;">Biografia</label><br />
                    <textarea id="biografia" name="biografia" rows="3" style="width: 100%; padding: 8px; background-color: #282828; border: 1px solid #444; color: #fff; border-radius: 4px; margin-top: 4px;" required="required"></textarea>
                </div>
                <div style="margin-bottom: 16px;">
                    <label for="immagine" style="font-size: 12px; color: #b3b3b3;">Nome File Immagine (in cartella img/)</label><br />
                    <input type="text" id="immagine" name="immagine" value="primo_piano.png" style="width: 100%; padding: 8px; background-color: #282828; border: 1px solid #444; color: #fff; border-radius: 4px; margin-top: 4px;" required="required" />
                </div>
                <input type="submit" value="Salva Artista" style="background-color: #1db954; color: #fff; border: none; padding: 10px 20px; border-radius: 20px; font-weight: bold; cursor: pointer;" />
            </form>
        </div>

        <h2 style="font-size: 20px; margin: 0 0 16px 0;">Artisti Esistenti</h2>
        <div style="background-color: #181818; border-radius: 8px; padding: 16px;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 1px solid #282828; color: #b3b3b3; font-size: 12px;">
                        <th style="padding: 10px;">ID</th>
                        <th style="padding: 10px;">NOME</th>
                        <th style="padding: 10px;">BIOGRAFIA</th>
                        <th style="padding: 10px; text-align: right;">AZIONI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($art = $res_artists->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #282828; font-size: 13px;">
                            <td style="padding: 12px; color: #b3b3b3;"><?php echo $art['id']; ?></td>
                            <td style="padding: 12px; font-weight: bold; color: #1db954;"><?php echo htmlspecialchars($art['nome']); ?></td>
                            <td style="padding: 12px; color: #b3b3b3; max-width: 300px;"><?php echo htmlspecialchars($art['biografia']); ?></td>
                            <td style="padding: 12px; text-align: right;">
                                <a href="admin.php?delete=<?php echo $art['id']; ?>" onclick="return confirm('Sicuro di voler eliminare questo artista?');" style="color: #e22134; text-decoration: none; font-weight: bold;">Elimina</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
<?php $conn->close(); ?>