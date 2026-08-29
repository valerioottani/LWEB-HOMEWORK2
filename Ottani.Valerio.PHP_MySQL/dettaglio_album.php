<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connection.php';

$id_album = isset($_GET['id']) ? (int)$_GET['id'] : 1;

$res_alb = $conn->query("SELECT a.*, art.nome AS artista FROM `" . TAB_ALBUMS . "` a JOIN `" . TAB_ARTISTS . "` art ON a.artista_id = art.id WHERE a.id = $id_album");
if ($res_alb && $res_alb->num_rows > 0) {
    $album = $res_alb->fetch_assoc();
} else {
    die("Album non trovato.");
}

$res_merch = $conn->query("SELECT * FROM `merchandise_album` WHERE album_id = $id_album");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify - Shop & Album: <?php echo htmlspecialchars($album['titolo']); ?></title>
    <style type="text/css">
        .spotify-card {
            background-color: #181818;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.05);
            transition: transform 0.25s ease, background-color 0.25s ease, box-shadow 0.25s ease;
        }
        .spotify-card:hover {
            transform: translateY(-4px);
            background-color: #222222;
            box-shadow: 0 10px 20px rgba(0,0,0,0.6);
        }
        .buy-btn {
            background-color: #1db954;
            color: #000000;
            border: none;
            padding: 10px 20px;
            border-radius: 500px;
            font-weight: bold;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.2s ease, transform 0.2s ease;
            width: 100%;
            box-sizing: border-box;
        }
        .buy-btn:hover {
            background-color: #1ed760;
            transform: scale(1.03);
        }
        .soldout-btn {
            background-color: #333333;
            color: #888888;
            border: none;
            padding: 10px 20px;
            border-radius: 500px;
            font-weight: bold;
            font-size: 13px;
            cursor: not-allowed;
            text-decoration: none;
            display: inline-block;
            width: 100%;
            box-sizing: border-box;
        }
    </style>
</head>
<body style="background: linear-gradient(180deg, #1f1f1f 0%, #121212 40%, #0a0a0a 100%); background-attachment: fixed; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0; min-height: 100vh;">

    <?php include 'menu.php'; ?>

    <div style="margin-left: 230px; padding: 32px; max-width: 1100px; box-sizing: border-box;">
        
        <div style="display: flex; align-items: center; gap: 32px; background-color: #181818; padding: 32px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); margin-bottom: 36px; box-shadow: 0 8px 24px rgba(0,0,0,0.5);">
            <img src="img/<?php echo htmlspecialchars($album['copertina']); ?>" alt="" style="width: 180px; height: 180px; border-radius: 8px; object-fit: cover; box-shadow: 0 6px 16px rgba(0,0,0,0.6);" />
            <div>
                <span style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: #1db954; letter-spacing: 1px;">Album Ufficiale & Store</span>
                <h1 style="font-size: 36px; font-weight: 900; margin: 8px 0; color: #ffffff;"><?php echo htmlspecialchars($album['titolo']); ?></h1>
                <p style="font-size: 16px; color: #b3b3b3; margin: 0 0 8px 0; font-weight: bold;"><?php echo htmlspecialchars($album['artista']); ?></p>
                <p style="font-size: 13px; color: #888888; margin: 0;">Anno di pubblicazione: <?php echo htmlspecialchars($album['anno']); ?></p>
            </div>
        </div>

        <h2 style="font-size: 22px; font-weight: bold; margin-bottom: 20px; color: #ffffff;">Merchandise Ufficiale, Vinili & CD</h2>
        <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 40px;">
            <?php if ($res_merch && $res_merch->num_rows > 0): ?>
                <?php while ($prod = $res_merch->fetch_assoc()): ?>
                    <?php 
                        $is_soldout = (isset($prod['disponibile']) && $prod['disponibile'] == 0);
                        $opacity_style = $is_soldout ? 'opacity: 0.7;' : '';
                    ?>
                    <div class="spotify-card" style="width: 230px; padding: 20px; text-align: center; display: flex; flex-direction: column; justify-content: space-between; position: relative; <?php echo $opacity_style; ?>">
                        
                        <div>
                            <div style="display: flex; justify-content: center; align-items: center; height: 140px; margin-bottom: 14px;">
                                <?php 
                                    $img_filename = isset($prod['immagine_prodotto']) ? $prod['immagine_prodotto'] : '';
                                    $img_path = 'img/' . $img_filename;
                                    
                                    if (!$is_soldout && !empty($img_filename) && file_exists($img_path)): 
                                ?>
                                    <img src="img/<?php echo htmlspecialchars($img_filename); ?>" alt="" style="max-width: 130px; max-height: 130px; object-fit: contain; filter: drop-shadow(0 8px 12px rgba(0,0,0,0.6));" />
                                <?php else: ?>
                                    <div style="width: 100%; height: 130px; background-color: #202020; border-radius: 6px; display: flex; align-items: center; justify-content: center; border: 1px dashed #444444;">
                                        <span style="color: #e91429; font-weight: 900; font-size: 14px; letter-spacing: 2px; text-transform: uppercase;">SOLD OUT</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <p style="font-size: 14px; font-weight: bold; margin: 0 0 8px 0; color: #ffffff;"><?php echo htmlspecialchars($prod['tipo_prodotto']); ?></p>
                            <p style="font-size: 16px; font-weight: 900; color: #1db954; margin: 0 0 16px 0;">€ <?php echo number_format($prod['prezzo'], 2, ',', '.'); ?></p>
                        </div>

                        <?php if (!$is_soldout): ?>
                            <form action="aggiungi_carrello.php" method="POST" style="margin: 0;">
                                <input type="hidden" name="merch_id" value="<?php echo (int)$prod['id']; ?>" />
                                <button type="submit" class="buy-btn">Acquista Ora</button>
                            </form>
                        <?php else: ?>
                            <span class="soldout-btn">Esaurito</span>
                        <?php endif; ?>

                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="spotify-card" style="padding: 24px; width: 100%; box-sizing: border-box;">
                    <p style="color: #b3b3b3; font-size: 14px; margin: 0; text-align: center;">Nessun articolo fisico disponibile per questo album al momento.</p>
                </div>
            <?php endif; ?>
        </div>

        <h2 style="font-size: 22px; font-weight: bold; margin-bottom: 20px; color: #ffffff;">Curiosità & Note di Produzione</h2>
        <div style="background-color: #181818; padding: 24px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); margin-bottom: 30px; color: #b3b3b3; font-size: 14px; line-height: 1.6;">
            <p style="margin-top: 0; color: #ffffff; font-weight: bold; font-size: 15px;">Il Concept e la Realizzazione</p>
            <p style="margin-bottom: 16px;">Questo progetto discografico rappresenta una pietra miliare nel percorso artistico di <strong><?php echo htmlspecialchars($album['artista']); ?></strong>. Registrato tra Milano e Napoli, l'album esplora sonorità urban all'avanguardia unite a metriche di forte impatto emotivo e sociale.</p>
            
            <p style="margin: 0 0 6px 0; color: #ffffff; font-weight: bold; font-size: 15px;">Impatto e Certificazioni</p>
            <p style="margin: 0;">Fin dalla sua pubblicazione nell'anno <?php echo htmlspecialchars($album['anno']); ?>, il disco ha dominato le classifiche FIMI conquistando rapidamente i record di riproduzioni in streaming e anticipando i trend musicali dell'intera scena nazionale.</p>
        </div>

        <div>
            <a href="discografia.php" style="color: #1db954; text-decoration: none; font-size: 13px; font-weight: bold; cursor: pointer;">← Torna alla Discografia</a>
        </div>

    </div>

</body>
</html>
<?php $conn->close(); ?>