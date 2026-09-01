<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connection.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$username_corrente = $_SESSION['user'];
$messaggio = '';
$errore = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuovo_username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $nome_completo = isset($_POST['nome_completo']) ? trim($_POST['nome_completo']) : '';
    $eta = isset($_POST['eta']) && $_POST['eta'] !== '' ? (int)$_POST['eta'] : null;
    $data_nascita = isset($_POST['data_nascita']) && $_POST['data_nascita'] !== '' ? trim($_POST['data_nascita']) : null;
    $indirizzo = isset($_POST['indirizzo']) ? trim($_POST['indirizzo']) : '';
    $nuova_password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (!empty($nuovo_username)) {
        $stmt_chk = $conn->prepare("SELECT id FROM `" . TAB_USERS . "` WHERE username = ? AND username != ?");
        $stmt_chk->bind_param("ss", $nuovo_username, $username_corrente);
        $stmt_chk->execute();
        $stmt_chk->store_result();

        if ($stmt_chk->num_rows > 0) {
            $errore = "Questo username è già in uso da un altro utente.";
        }
        $stmt_chk->close();

        if (empty($errore)) {
            if (!empty($nuova_password)) {
                $hashed = password_hash($nuova_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE `" . TAB_USERS . "` SET username = ?, nome_completo = ?, eta = ?, data_nascita = ?, indirizzo = ?, password = ? WHERE username = ?");
                if ($stmt) {
                    $stmt->bind_param("ssissis", $nuovo_username, $nome_completo, $eta, $data_nascita, $indirizzo, $hashed, $username_corrente);
                }
            } else {
                $stmt = $conn->prepare("UPDATE `" . TAB_USERS . "` SET username = ?, nome_completo = ?, eta = ?, data_nascita = ?, indirizzo = ? WHERE username = ?");
                if ($stmt) {
                    $stmt->bind_param("ssiss s", $nuovo_username, $nome_completo, $eta, $data_nascita, $indirizzo, $username_corrente); // (Nota: stringa parametri corretta sotto)
                }
            }
            
            // Correzione bind_param stringa tipi (ssiss + s = 6 parametri stringa/int)
            if (empty($nuova_password)) {
                $stmt = $conn->prepare("UPDATE `" . TAB_USERS . "` SET username = ?, nome_completo = ?, eta = ?, data_nascita = ?, indirizzo = ? WHERE username = ?");
                $stmt->bind_param("ssissi", $nuovo_username, $nome_completo, $eta, $data_nascita, $indirizzo, $username_corrente); // s=username, s=nome, i=eta, s=data, s=indirizzo, s=username_corrente -> attento: eta è i, gli altri s. Quindi "ssissi"
            }

            if ($stmt && $stmt->execute()) {
                $_SESSION['user'] = $nuovo_username;
                $username_corrente = $nuovo_username;
                $messaggio = "Profilo e indirizzo di spedizione aggiornati con successo!";
            } else {
                $errore = "Errore durante l'aggiornamento del profilo.";
            }
            if ($stmt) {
                $stmt->close();
            }
        }
    } else {
        $errore = "Lo username non può essere vuoto.";
    }
}

$res_user = ['username' => $username_corrente, 'nome_completo' => '', 'eta' => '', 'data_nascita' => '', 'indirizzo' => ''];
$stmt_get = $conn->prepare("SELECT username, nome_completo, eta, data_nascita, indirizzo FROM `" . TAB_USERS . "` WHERE username = ?");
if ($stmt_get) {
    $stmt_get->bind_param("s", $username_corrente);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $res_user = $row;
    }
    $stmt_get->close();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify - Il Mio Profilo</title>
</head>
<body style="background: linear-gradient(180deg, #1f1f1f 0%, #121212 40%, #0a0a0a 100%); background-attachment: fixed; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0; min-height: 100vh;">

    <?php include 'menu.php'; ?>

    <div style="margin-left: 230px; padding: 32px; max-width: 600px;">
        <div style="background-color: #181818; padding: 30px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
            <h1 style="font-size: 24px; font-weight: bold; margin-top: 0; margin-bottom: 20px;">Il Mio Profilo</h1>

            <?php if (!empty($messaggio)): ?>
                <div style="background-color: rgba(29,185,84,0.15); border: 1px solid #1db954; color: #1db954; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 13px;">
                    <?php echo htmlspecialchars($messaggio); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errore)): ?>
                <div style="background-color: rgba(226,33,52,0.15); border: 1px solid #e22134; color: #e22134; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 13px;">
                    <?php echo htmlspecialchars($errore); ?>
                </div>
            <?php endif; ?>

            <form action="profilo.php" method="POST">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; color: #b3b3b3; margin-bottom: 6px;">Username</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($res_user['username'] ?? ''); ?>" style="width: 100%; padding: 12px; background-color: #202020; border: 1px solid #444; color: #fff; border-radius: 6px; box-sizing: border-box;" required="required" />
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; color: #b3b3b3; margin-bottom: 6px;">Nome Completo</label>
                    <input type="text" name="nome_completo" value="<?php echo htmlspecialchars($res_user['nome_completo'] ?? ''); ?>" placeholder="Il tuo nome e cognome" style="width: 100%; padding: 12px; background-color: #202020; border: 1px solid #444; color: #fff; border-radius: 6px; box-sizing: border-box;" />
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; color: #b3b3b3; margin-bottom: 6px;">Età</label>
                    <input type="number" name="eta" value="<?php echo htmlspecialchars($res_user['eta'] ?? ''); ?>" style="width: 100%; padding: 12px; background-color: #202020; border: 1px solid #444; color: #fff; border-radius: 6px; box-sizing: border-box;" min="1" max="120" />
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; color: #b3b3b3; margin-bottom: 6px;">Data di Nascita</label>
                    <input type="date" name="data_nascita" value="<?php echo htmlspecialchars($res_user['data_nascita'] ?? ''); ?>" style="width: 100%; padding: 12px; background-color: #202020; border: 1px solid #444; color: #fff; border-radius: 6px; box-sizing: border-box;" />
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; color: #b3b3b3; margin-bottom: 6px;">Indirizzo di Casa (per spedizione merchandise)</label>
                    <input type="text" name="indirizzo" value="<?php echo htmlspecialchars($res_user['indirizzo'] ?? ''); ?>" placeholder="Via, Città, CAP" style="width: 100%; padding: 12px; background-color: #202020; border: 1px solid #444; color: #fff; border-radius: 6px; box-sizing: border-box;" />
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 12px; color: #b3b3b3; margin-bottom: 6px;">Nuova Password (lascia vuoto per non modificarla)</label>
                    <input type="password" name="password" placeholder="••••••••" style="width: 100%; padding: 12px; background-color: #202020; border: 1px solid #444; color: #fff; border-radius: 6px; box-sizing: border-box;" />
                </div>

                <button type="submit" style="background-color: #1db954; color: #000; border: none; padding: 12px 24px; border-radius: 500px; font-weight: bold; font-size: 13px; cursor: pointer;">Salva Modifiche</button>
            </form>
        </div>
    </div>

</body>
</html>
<?php 
if (isset($conn) && !$conn->connect_error) {
    $conn->close(); 
}
?>