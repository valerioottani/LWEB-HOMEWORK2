<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connection.php';

$errore = '';
$successo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_completo = isset($_POST['nome_completo']) ? trim($_POST['nome_completo']) : '';
    $user = isset($_POST['username']) ? trim($_POST['username']) : '';
    $pass = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (!empty($nome_completo) && !empty($user) && !empty($pass)) {
        $stmt_check = $conn->prepare("SELECT id FROM `" . TAB_USERS . "` WHERE username = ?");
        if ($stmt_check) {
            $stmt_check->bind_param('s', $user);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                $errore = 'Questo username è già in uso. Scegline un altro.';
            } else {
                $stmt_check->close();
                $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
                $stmt_ins = $conn->prepare("INSERT INTO `" . TAB_USERS . "` (username, password, ruolo, nome_completo) VALUES (?, ?, 'user', ?)");
                if ($stmt_ins) {
                    $stmt_ins->bind_param('sss', $user, $hashed_password, $nome_completo);
                    if ($stmt_ins->execute()) {
                        $successo = 'Registrazione completata con successo! Ora puoi effettuare il login.';
                    } else {
                        $errore = 'Errore durante la registrazione.';
                    }
                    $stmt_ins->close();
                }
            }
            if ($stmt_check) {
                $stmt_check->close();
            }
        }
    } else {
        $errore = 'Compila tutti i campi obbligatori.';
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Registrati - Spotify</title>
</head>
<body style="background-color: #121212; color: #ffffff; font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0;">

    <div style="background-color: #000000; padding: 40px; border-radius: 8px; width: 340px; text-align: center;">
        <div style="color: #1db954; font-size: 24px; font-weight: bold; margin-bottom: 20px;">SPOTIFY</div>
        
        <h2 style="font-size: 18px; margin-bottom: 20px;">Crea il tuo account</h2>

        <?php if (!empty($errore)): ?>
            <div style="background-color: #e22134; color: #ffffff; padding: 8px; border-radius: 4px; font-size: 13px; margin-bottom: 16px;">
                <?php echo htmlspecialchars($errore); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($successo)): ?>
            <div style="background-color: #1db954; color: #000000; padding: 8px; border-radius: 4px; font-size: 13px; margin-bottom: 16px; font-weight: bold;">
                <?php echo htmlspecialchars($successo); ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="post">
            <div style="margin-bottom: 14px; text-align: left;">
                <label for="nome_completo" style="font-size: 12px; color: #b3b3b3;">Nome e Cognome</label><br />
                <input type="text" id="nome_completo" name="nome_completo" style="width: 100%; padding: 10px; box-sizing: border-box; background-color: #181818; border: 1px solid #444444; color: #ffffff; border-radius: 4px; margin-top: 4px;" required="required" />
            </div>

            <div style="margin-bottom: 14px; text-align: left;">
                <label for="username" style="font-size: 12px; color: #b3b3b3;">Username</label><br />
                <input type="text" id="username" name="username" style="width: 100%; padding: 10px; box-sizing: border-box; background-color: #181818; border: 1px solid #444444; color: #ffffff; border-radius: 4px; margin-top: 4px;" required="required" />
            </div>

            <div style="margin-bottom: 20px; text-align: left;">
                <label for="password" style="font-size: 12px; color: #b3b3b3;">Password</label><br />
                <input type="password" id="password" name="password" style="width: 100%; padding: 10px; box-sizing: border-box; background-color: #181818; border: 1px solid #444444; color: #ffffff; border-radius: 4px; margin-top: 4px;" required="required" />
            </div>

            <input type="submit" value="REGISTRATI" style="width: 100%; padding: 12px; background-color: #1db954; border: none; border-radius: 20px; color: #ffffff; font-weight: bold; font-size: 13px; cursor: pointer;" />
        </form>

        <p style="margin-top: 20px; font-size: 12px;"><a href="login.php" style="color: #b3b3b3; text-decoration: none;">Hai già un account? Accedi</a></p>
    </div>

</body>
</html>
<?php 
if (isset($conn) && !$conn->connect_error) {
    $conn->close(); 
}
?>