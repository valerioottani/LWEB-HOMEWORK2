<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connection.php';

$titolo_articolo = isset($_GET['titolo']) ? trim($_GET['titolo']) : '';

// Recupero dell'articolo dal database tramite il titolo esatto
$stmt = $conn->prepare("SELECT * FROM `articoli_blog` WHERE titolo = ?");
$stmt->bind_param("s", $titolo_articolo);
$stmt->execute();
$result = $stmt->get_result();
$articolo = ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
$stmt->close();

// Se l'articolo ha un'immagine associata la usiamo, altrimenti fallback su un'immagine di default
$immagine_articolo = 'primo_piano.png';
if ($articolo) {
    // Se nel db è presente un'immagine specifica o mappiamo in base al titolo/id
    $t_lower = strtolower($articolo['titolo']);
    if (strpos($t_lower, 'marra') !== false || strpos($t_lower, 'stadi') !== false) {
        $immagine_articolo = 'marra.jpg';
    } elseif (strpos($t_lower, 'luchè') !== false || strpos($t_lower, 'luche') !== false) {
        $immagine_articolo = 'primo_piano.png';
    } elseif (strpos($t_lower, 'tour') !== false) {
        $immagine_articolo = 'geolier.jpg';
    } elseif (strpos($t_lower, 'vinile') !== false) {
        $immagine_articolo = 'lazza.jpg';
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify Blog - <?php echo $articolo ? htmlspecialchars($articolo['titolo']) : 'Articolo non trovato'; ?></title>
</head>
<body style="background: linear-gradient(180deg, #1f1f1f 0%, #121212 40%, #0a0a0a 100%); background-attachment: fixed; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0; min-height: 100vh;">

    <?php include 'menu.php'; ?>

    <div style="margin-left: 230px; padding: 40px; display: flex; justify-content: center; box-sizing: border-box;">
        
        <!-- Contenitore Articolo Stile Giornale Centrato -->
        <div style="background-color: #181818; padding: 48px 56px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); max-width: 800px; width: 100%; box-sizing: border-box;">
            
            <?php if ($articolo): ?>
                <!-- Intestazione Centrata -->
                <div style="text-align: center; margin-bottom: 32px;">
                    <span style="font-size: 11px; font-weight: bold; color: #1db954; text-transform: uppercase; letter-spacing: 2px;">Spotify News & Editoriali</span>
                    <h1 style="font-size: 36px; font-weight: 900; margin: 16px 0 20px 0; line-height: 1.25; color: #ffffff;"><?php echo htmlspecialchars($articolo['titolo']); ?></h1>
                    
                    <div style="display: flex; justify-content: center; align-items: center; gap: 12px; color: #b3b3b3; font-size: 13px;">
                        <span>Articolo a cura di <strong><?php echo htmlspecialchars(isset($articolo['autore']) ? $articolo['autore'] : 'Redazione'); ?></strong></span>
                        <span>•</span>
                        <span><?php echo htmlspecialchars(isset($articolo['data']) ? $articolo['data'] : 'Oggi'); ?></span>
                    </div>
                </div>

                <!-- Immagine Principale -->
                <div style="text-align: center; margin-bottom: 36px;">
                    <img src="img/<?php echo $immagine_articolo; ?>" alt="" style="width: 100%; max-height: 450px; object-fit: cover; object-position: 50% 25%; border-radius: 8px; box-shadow: 0 12px 32px rgba(0,0,0,0.7);" />
                </div>

                <!-- Corpo dell'Articolo (Diviso in paragrafi per una lettura fluida) -->
                <div style="display: flex; flex-direction: column; gap: 24px; margin-bottom: 40px; text-align: justify;">
                    <?php 
                    $testo_completo = isset($articolo['contenuto']) ? $articolo['contenuto'] : '';
                    // Suddividiamo il testo in paragrafi se lungo, oppure mostriamo l'intero blocco
                    $paragrafi = explode("\n", $testo_completo);
                    foreach ($paragrafi as $paragrafo): 
                        if (trim($paragrafo) !== ''):
                    ?>
                        <p style="font-size: 16px; line-height: 1.85; color: #d8d8d8; margin: 0; text-indent: 20px;"><?php echo htmlspecialchars(trim($paragrafo)); ?></p>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>

            <?php else: ?>
                <div style="text-align: center; padding: 40px 0;">
                    <h2 style="color: #e22134; margin-top: 0;">Articolo non trovato</h2>
                    <p style="color: #b3b3b3; font-size: 14px;">L'articolo richiesto non è presente nel database o è stato rimosso.</p>
                </div>
            <?php endif; ?>

            <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.1); margin-bottom: 28px;" />

            <!-- Link di ritorno -->
            <div style="text-align: left;">
                <a href="blog.php" style="color: #1db954; text-decoration: none; font-size: 13px; font-weight: bold; cursor: pointer;">← Torna al Blog</a>
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