<?php
session_start();
// Svuota il carrello completando l'ordine
unset($_SESSION['carrello']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify - Ordine Confermato</title>
</head>
<body style="background: linear-gradient(180deg, #1f1f1f 0%, #121212 40%, #0a0a0a 100%); background-attachment: fixed; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0; min-height: 100vh;">

    <?php include 'menu.php'; ?>

    <div style="margin-left: 230px; padding: 60px 32px; max-width: 600px; box-sizing: border-box; text-align: center; margin-right: auto;">
        <div style="background-color: #181818; padding: 40px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); box-shadow: 0 10px 30px rgba(0,0,0,0.6);">
            <div style="font-size: 48px; margin-bottom: 16px; color: #1db954;">✓</div>
            <h1 style="font-size: 28px; font-weight: 900; margin-bottom: 12px; color: #ffffff;">Ordine Confermato con Successo!</h1>
            <p style="color: #b3b3b3; font-size: 14px; line-height: 1.6; margin-bottom: 30px;">Grazie per il tuo acquisto. Il merchandise fisico selezionato è stato preso in carico e verrà spedito al più presto.</p>
            <a href="homepage.php" style="background-color: #1db954; color: #000000; padding: 12px 28px; border-radius: 500px; text-decoration: none; font-weight: bold; font-size: 13px; display: inline-block;">Torna alla Homepage</a>
        </div>
    </div>

</body>
</html>