<?php
session_start();
require_once 'connection.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = isset($_POST['username']) ? trim($_POST['username']) : '';
    $pass = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (!empty($user) && !empty($pass)) {
        $stmt = $conn->prepare("SELECT password, ruolo FROM `" . TAB_USERS . "` WHERE username = ?");
        
        if ($stmt) {
            $stmt->bind_param('s', $user);
            $stmt->execute();
            $stmt->bind_result($hashed_password, $ruolo);

            if ($stmt->fetch() && password_verify($pass, $hashed_password)) {
                $_SESSION['user'] = $user;
                $_SESSION['ruolo'] = $ruolo;
                $stmt->close();
                
                if ($ruolo === 'admin') {
                    header('Location: admin.php');
                } else {
                    header('Location: homepage.php');
                }
                exit();
            } else {
                $error = 'Credenziali non valide.';
            }
            $stmt->close();
        } else {
            $error = 'Errore database: esegui prima install.php per aggiornare la tabella utenti.';
        }
    } else {
        $error = 'Compila tutti i campi.';
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Accedi - Spotify</title>
</head>
<body style="background-color: #121212; color: #ffffff; font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0;">

    <div style="background-color: #000000; padding: 40px; border-radius: 8px; width: 320px; text-align: center;">
        <div style="color: #1db954; font-size: 24px; font-weight: bold; margin-bottom: 20px;">SPOTIFY</div>
        
        <h2 style="font-size: 18px; margin-bottom: 20px;">Accedi all'account</h2>

        <?php if (!empty($error)): ?>
            <div style="background-color: #e22134; color: #ffffff; padding: 8px; border-radius: 4px; font-size: 13px; margin-bottom: 16px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="post">
            <div style="margin-bottom: 14px; text-align: left;">
                <label for="username" style="font-size: 12px; color: #b3b3b3;">Username</label><br />
                <input type="text" id="username" name="username" style="width: 100%; padding: 10px; box-sizing: border-box; background-color: #181818; border: 1px solid #444444; color: #ffffff; border-radius: 4px; margin-top: 4px;" required="required" />
            </div>

            <div style="margin-bottom: 20px; text-align: left;">
                <label for="password" style="font-size: 12px; color: #b3b3b3;">Password</label><br />
                <input type="password" id="password" name="password" style="width: 100%; padding: 10px; box-sizing: border-box; background-color: #181818; border: 1px solid #444444; color: #ffffff; border-radius: 4px; margin-top: 4px;" required="required" />
            </div>

            <input type="submit" value="ACCEDI" style="width: 100%; padding: 12px; background-color: #1db954; border: none; border-radius: 20px; color: #ffffff; font-weight: bold; font-size: 13px; cursor: pointer;" />
        </form>

        <p style="margin-top: 20px; font-size: 12px;"><a href="homepage.php" style="color: #b3b3b3; text-decoration: none;">Torna alla Home</a></p>
    </div>

</body>
</html>
<?php 
if (isset($conn) && !$conn->connect_error) {
    $conn->close(); 
}
?>