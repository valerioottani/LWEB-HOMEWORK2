<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connection.php';

// Controllo di accesso: solo gli admin possono accedere
if (!isset($_SESSION['user']) || !isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'admin') {
    header('Location: homepage.php');
    exit();
}

$messaggio = '';
$errore = '';

// Assicuriamoci che la tabella esista
$conn->query("CREATE TABLE IF NOT EXISTS `articoli_blog` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `titolo` VARCHAR(255) NOT NULL UNIQUE,
    `contenuto` TEXT NOT NULL,
    `autore` VARCHAR(100) DEFAULT 'Redazione',
    `data` VARCHAR(50) DEFAULT 'Oggi'
)");

// Popolamento iniziale in sicurezza (evitando duplicati con INSERT IGNORE)
$res_count = $conn->query("SELECT COUNT(*) as cnt FROM `articoli_blog`");
if ($res_count) {
    $row = $res_count->fetch_assoc();
    if ($row['cnt'] == 0) {
        $conn->query("INSERT IGNORE INTO `articoli_blog` (titolo, contenuto, autore, data) VALUES ('Il ritorno di Luchè: anatomia di un successo che ridefinisce il rap d autore', 'Un analisi approfondita sull impatto discografico e sulla scrittura del celebre artista napoletano nella scena urban contemporanea.', 'Redazione Urban', '25 Agosto 2026')");
        $conn->query("INSERT IGNORE INTO `articoli_blog` (titolo, contenuto, autore, data) VALUES ('La nuova età dell oro del Rap Italiano: trionfi, stadi e la consacrazione dei live estivi', 'Come il genere si è evoluto conquistando i palazzetti di tutta Italia con produzioni faraoniche e record di vendite.', 'Pincopallino S.', '24 Agosto 2026')");
        $conn->query("INSERT IGNORE INTO `articoli_blog` (titolo, contenuto, autore, data) VALUES ('Dietro le Quinte del Tour: Come Nasce uno Show Live nei Palazzetti', 'Portare in giro per l’Italia un tour richiede mesi di preparazione tecnica, prove serrate e una cura maniacale per la produzione visiva.', 'Redazione Live', '20 Agosto 2026')");
        $conn->query("INSERT IGNORE INTO `articoli_blog` (titolo, contenuto, autore, data) VALUES ('Il Ritorno del Vinile e del Merchandise Fisico nell Era dello Streaming', 'Nell\'era digitale, i fan dimostrano un attaccamento viscerale per il supporto fisico e le edizioni limitate da collezione.', 'Marco V.', '15 Agosto 2026')");
    }
}

// 1. GESTIONE AGGIUNTA DI UN NUOVO ARTICOLO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione']) && $_POST['azione'] === 'aggiungi') {
    $titolo = trim($_POST['titolo']);
    $contenuto = trim($_POST['contenuto']);
    $autore = !empty(trim($_POST['autore'])) ? trim($_POST['autore']) : 'Redazione';
    $data_art = date('d M Y');

    if (!empty($titolo) && !empty($contenuto)) {
        $stmt_ins = $conn->prepare("INSERT INTO `articoli_blog` (titolo, contenuto, autore, data) VALUES (?, ?, ?, ?)");
        if ($stmt_ins) {
            $stmt_ins->bind_param("ssss", $titolo, $contenuto, $autore, $data_art);
            if ($stmt_ins->execute()) {
                $messaggio = "Articolo pubblicato con successo!";
            } else {
                $errore = "Errore: un articolo con questo titolo esiste già o si è verificato un problema.";
            }
            $stmt_ins->close();
        }
    } else {
        $errore = "Compila tutti i campi obbligatori.";
    }
}

// 2. GESTIONE ELIMINAZIONE DI UN ARTICOLO
if (isset($_GET['elimina'])) {
    $id_elimina = (int)$_GET['elimina'];
    $stmt_del = $conn->prepare("DELETE FROM `articoli_blog` WHERE id = ?");
    if ($stmt_del) {
        $stmt_del->bind_param("i", $id_elimina);
        if ($stmt_del->execute()) {
            $messaggio = "Articolo eliminato con successo!";
        } else {
            $errore = "Errore durante l'eliminazione.";
        }
        $stmt_del->close();
    }
}

// 3. GESTIONE MODIFICA DI UN ARTICOLO ESISTENTE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione']) && $_POST['azione'] === 'modifica') {
    $id_mod = (int)$_POST['articolo_id'];
    $titolo = trim($_POST['titolo']);
    $autore = trim($_POST['autore']);
    $contenuto = trim($_POST['contenuto']);

    if (!empty($titolo) && !empty($contenuto)) {
        $stmt_upd = $conn->prepare("UPDATE `articoli_blog` SET titolo = ?, autore = ?, contenuto = ? WHERE id = ?");
        if ($stmt_upd) {
            $stmt_upd->bind_param("sssi", $titolo, $autore, $contenuto, $id_mod);
            if ($stmt_upd->execute()) {
                $messaggio = "Articolo aggiornato con successo!";
            } else {
                $errore = "Errore durante l'aggiornamento.";
            }
            $stmt_upd->close();
        }
    } else {
        $errore = "I campi non possono essere vuoti.";
    }
}

// Recupero di tutti gli articoli dal database per la tabella
$res_art = $conn->query("SELECT * FROM `articoli_blog` ORDER BY id DESC");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify - Gestione Articoli Blog</title>
    <style type="text/css">
        .form-input {
            background-color: #181818;
            color: #ffffff;
            border: 1px solid #444;
            padding: 8px;
            border-radius: 4px;
            font-size: 12px;
            box-sizing: border-box;
        }
        .btn-green {
            background-color: #1db954;
            color: #000000;
            border: none;
            padding: 9px 18px;
            border-radius: 500px;
            font-weight: bold;
            font-size: 12px;
            cursor: pointer;
        }
        .btn-green:hover {
            background-color: #1ed760;
        }
        .btn-red {
            background-color: #e22134;
            color: #ffffff;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 11px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-red:hover {
            background-color: #ff334b;
        }
    </style>
</head>
<body style="background: linear-gradient(180deg, #1f1f1f 0%, #121212 40%, #0a0a0a 100%); background-attachment: fixed; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0; min-height: 100vh;">

    <?php include 'menu.php'; ?>

    <div style="margin-left: 230px; padding: 32px; max-width: 1400px; box-sizing: border-box;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h1 style="font-size: 24px; font-weight: bold; margin: 0;">Gestione Articoli del Blog</h1>
            <a href="blog.php" style="color: #1db954; text-decoration: none; font-size: 13px; font-weight: bold;">← Torna al Blog</a>
        </div>

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

        <!-- FORM AGGIUNTA ARTICOLO -->
        <div style="background-color: #181818; padding: 24px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05); margin-bottom: 30px;">
            <h3 style="margin-top: 0; font-size: 16px; color: #1db954; margin-bottom: 16px;">+ Scrivi Nuovo Articolo</h3>
            <form action="gestione_articoli.php" method="POST" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
                <input type="hidden" name="azione" value="aggiungi" />
                
                <div style="flex-grow: 1; min-width: 250px;">
                    <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Titolo Articolo</label>
                    <input type="text" name="titolo" placeholder="Titolo accattivante..." class="form-input" style="width: 100%;" required="required" />
                </div>

                <div style="width: 180px;">
                    <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Autore</label>
                    <input type="text" name="autore" placeholder="Redazione Urban" class="form-input" style="width: 100%;" />
                </div>

                <div style="flex-basis: 100%;">
                    <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Contenuto</label>
                    <textarea name="contenuto" placeholder="Testo dell'articolo..." class="form-input" style="width: 100%; height: 90px; resize: vertical;" required="required"></textarea>
                </div>

                <div>
                    <button type="submit" class="btn-green">Pubblica Articolo</button>
                </div>
            </form>
        </div>

        <!-- TABELLA ARTICOLI ESISTENTI -->
        <h3 style="font-size: 18px; margin-bottom: 16px;">Articoli Inseriti e Pubblicati</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; background-color: #181818; border-radius: 8px; overflow: hidden;">
            <thead>
                <tr style="border-bottom: 1px solid #333; color: #b3b3b3; font-size: 11px; text-transform: uppercase;">
                    <th style="padding: 12px;">Titolo</th>
                    <th style="padding: 12px;">Autore</th>
                    <th style="padding: 12px;">Contenuto</th>
                    <th style="padding: 12px;">Data</th>
                    <th style="padding: 12px; text-align: center;">Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($res_art && $res_art->num_rows > 0): ?>
                    <?php while ($art = $res_art->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #222;">
                            <form action="gestione_articoli.php" method="POST">
                                <input type="hidden" name="azione" value="modifica" />
                                <input type="hidden" name="articolo_id" value="<?php echo $art['id']; ?>" />
                                
                                <td style="padding: 14px;">
                                    <input type="text" name="titolo" value="<?php echo htmlspecialchars($art['titolo']); ?>" class="form-input" style="width: 220px; font-weight: bold;" required="required" />
                                </td>
                                <td style="padding: 14px;">
                                    <input type="text" name="autore" value="<?php echo htmlspecialchars(isset($art['autore']) ? $art['autore'] : 'Redazione'); ?>" class="form-input" style="width: 130px;" />
                                </td>
                                <td style="padding: 14px;">
                                    <textarea name="contenuto" class="form-input" style="width: 320px; height: 50px; resize: vertical;" required="required"><?php echo htmlspecialchars(isset($art['contenuto']) ? $art['contenuto'] : ''); ?></textarea>
                                </td>
                                <td style="padding: 14px; color: #aaa; white-space: nowrap;"><?php echo htmlspecialchars($art['data']); ?></td>
                                <td style="padding: 14px; text-align: center; white-space: nowrap;">
                                    <button type="submit" class="btn-green" style="padding: 6px 14px; margin-right: 6px;">Salva</button>
                                    <a href="gestione_articoli.php?elimina=<?php echo $art['id']; ?>" class="btn-red" onclick="return confirm('Eliminare questo articolo?');">Elimina</a>
                                </td>
                            </form>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="padding: 20px; text-align: center; color: #888;">Nessun articolo trovato nel database.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>

</body>
</html>
<?php 
if (isset($conn) && !$conn->connect_error) {
    $conn->close(); 
}
?>