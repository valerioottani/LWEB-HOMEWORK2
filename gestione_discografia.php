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

// Controllo e aggiunta automatica della colonna per le note di produzione se non esiste
$conn->query("ALTER TABLE `" . TAB_ALBUMS . "` ADD COLUMN IF NOT EXISTS `curiosita` TEXT DEFAULT NULL");

// 1. GESTIONE AGGIUNTA DI UN NUOVO ALBUM
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione']) && $_POST['azione'] === 'aggiungi') {
    $titolo = trim($_POST['titolo']);
    $artista_id = (int)$_POST['artista_id'];
    $anno = (int)$_POST['anno'];
    $curiosita = trim($_POST['curiosita']);
    $copertina = !empty(trim($_POST['copertina'])) ? trim($_POST['copertina']) : 'album.png';

    if (!empty($titolo) && $artista_id > 0 && $anno > 0) {
        $stmt_ins = $conn->prepare("INSERT INTO `" . TAB_ALBUMS . "` (artista_id, titolo, anno, curiosita, copertina) VALUES (?, ?, ?, ?, ?)");
        if ($stmt_ins) {
            $stmt_ins->bind_param("isiss", $artista_id, $titolo, $anno, $curiosita, $copertina);
            if ($stmt_ins->execute()) {
                $messaggio = "Album aggiunto con successo!";
            } else {
                $errore = "Errore durante l'inserimento dell'album.";
            }
            $stmt_ins->close();
        }
    } else {
        $errore = "Compila tutti i campi obbligatori correttamente.";
    }
}

// 2. GESTIONE ELIMINAZIONE DI UN ALBUM
if (isset($_GET['elimina'])) {
    $id_elimina = (int)$_GET['elimina'];
    $stmt_del = $conn->prepare("DELETE FROM `" . TAB_ALBUMS . "` WHERE id = ?");
    if ($stmt_del) {
        $stmt_del->bind_param("i", $id_elimina);
        if ($stmt_del->execute()) {
            $messaggio = "Album eliminato con successo!";
        } else {
            $errore = "Errore durante l'eliminazione dell'album.";
        }
        $stmt_del->close();
    }
}

// 3. GESTIONE MODIFICA DI UN ALBUM ESISTENTE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione']) && $_POST['azione'] === 'modifica') {
    $id_mod = (int)$_POST['album_id'];
    $titolo = trim($_POST['titolo']);
    $artista_id = (int)$_POST['artista_id'];
    $anno = (int)$_POST['anno'];
    $curiosita = trim($_POST['curiosita']);
    $copertina = trim($_POST['copertina']);

    if (!empty($titolo) && $artista_id > 0 && $anno > 0) {
        $stmt_upd = $conn->prepare("UPDATE `" . TAB_ALBUMS . "` SET artista_id = ?, titolo = ?, anno = ?, curiosita = ?, copertina = ? WHERE id = ?");
        if ($stmt_upd) {
            $stmt_upd->bind_param("isissi", $artista_id, $titolo, $anno, $curiosita, $copertina, $id_mod);
            if ($stmt_upd->execute()) {
                $messaggio = "Album aggiornato con successo!";
            } else {
                $errore = "Errore durante l'aggiornamento dell'album.";
            }
            $stmt_upd->close();
        }
    } else {
        $errore = "I campi obbligatori non possono essere vuoti.";
    }
}

// Recupero di tutti gli album con i relativi artisti
$res_albums = $conn->query("SELECT a.*, art.nome AS artista FROM `" . TAB_ALBUMS . "` a JOIN `" . TAB_ARTISTS . "` art ON a.artista_id = art.id ORDER BY a.id ASC");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify - Gestione Discografia</title>
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

    <div style="margin-left: 230px; padding: 32px; max-width: 1450px; box-sizing: border-box;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h1 style="font-size: 24px; font-weight: bold; margin: 0;">Gestione Discografia (Album e Note di Produzione)</h1>
            <a href="homepage.php" style="color: #1db954; text-decoration: none; font-size: 13px; font-weight: bold;">← Torna alla Homepage</a>
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

        <!-- FORM PER AGGIUNGERE UN NUOVO ALBUM -->
        <div style="background-color: #181818; padding: 24px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05); margin-bottom: 30px;">
            <h3 style="margin-top: 0; font-size: 16px; color: #1db954; margin-bottom: 16px;">+ Aggiungi Nuovo Album</h3>
            <form action="gestione_discografia.php" method="POST" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
                <input type="hidden" name="azione" value="aggiungi" />
                
                <div>
                    <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Titolo Album</label>
                    <input type="text" name="titolo" placeholder="Nome album" class="form-input" style="width: 160px;" required="required" />
                </div>
                
                <div>
                    <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Artista</label>
                    <select name="artista_id" class="form-input" style="width: 140px;" required="required">
                        <option value="">-- Scegli --</option>
                        <?php 
                        $res_art_list = $conn->query("SELECT id, nome FROM `" . TAB_ARTISTS . "` ORDER BY nome ASC");
                        while ($art = $res_art_list->fetch_assoc()) {
                            echo '<option value="' . $art['id'] . '">' . htmlspecialchars($art['nome']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Anno</label>
                    <input type="number" name="anno" placeholder="2026" class="form-input" style="width: 75px;" required="required" />
                </div>

                <div style="flex-grow: 1; min-width: 280px;">
                    <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Curiosità & Note di Produzione</label>
                    <input type="text" name="curiosita" placeholder="Inserisci note di produzione..." class="form-input" style="width: 100%;" />
                </div>

                <div>
                    <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Copertina</label>
                    <input type="text" name="copertina" value="album.png" class="form-input" style="width: 110px;" />
                </div>

                <div>
                    <button type="submit" class="btn-green">Crea Album</button>
                </div>
            </form>
        </div>

        <!-- TABELLA DI GESTIONE / MODIFICA ED ELIMINAZIONE -->
        <h3 style="font-size: 18px; margin-bottom: 16px;">Album Esistenti nel Catalogo</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; background-color: #181818; border-radius: 8px; overflow: hidden;">
            <thead>
                <tr style="border-bottom: 1px solid #333; color: #b3b3b3; font-size: 11px; text-transform: uppercase;">
                    <th style="padding: 12px;">ID</th>
                    <th style="padding: 12px;">Titolo Album</th>
                    <th style="padding: 12px;">Artista</th>
                    <th style="padding: 12px;">Anno</th>
                    <th style="padding: 12px;">Curiosità & Note di Produzione</th>
                    <th style="padding: 12px;">Copertina</th>
                    <th style="padding: 12px; text-align: center;">Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($res_albums && $res_albums->num_rows > 0): ?>
                    <?php while ($alb = $res_albums->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #222;">
                            <form action="gestione_discografia.php" method="POST">
                                <input type="hidden" name="azione" value="modifica" />
                                <input type="hidden" name="album_id" value="<?php echo $alb['id']; ?>" />
                                
                                <td style="padding: 14px; color: #888;"><?php echo $alb['id']; ?></td>
                                <td style="padding: 14px;">
                                    <input type="text" name="titolo" value="<?php echo htmlspecialchars($alb['titolo']); ?>" class="form-input" style="width: 140px; font-weight: bold;" required="required" />
                                </td>
                                <td style="padding: 14px;">
                                    <select name="artista_id" class="form-input" style="width: 130px;" required="required">
                                        <?php 
                                        $res_art_loop = $conn->query("SELECT id, nome FROM `" . TAB_ARTISTS . "` ORDER BY nome ASC");
                                        while ($art_l = $res_art_loop->fetch_assoc()) {
                                            $selected = ($art_l['id'] == $alb['artista_id']) ? 'selected="selected"' : '';
                                            echo '<option value="' . $art_l['id'] . '" ' . $selected . '>' . htmlspecialchars($art_l['nome']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td style="padding: 14px;">
                                    <input type="number" name="anno" value="<?php echo htmlspecialchars($alb['anno']); ?>" class="form-input" style="width: 70px;" required="required" />
                                </td>
                                <td style="padding: 14px;">
                                    <input type="text" name="curiosita" value="<?php echo htmlspecialchars(isset($alb['curiosita']) ? $alb['curiosita'] : ''); ?>" class="form-input" style="width: 280px;" />
                                </td>
                                <td style="padding: 14px;">
                                    <input type="text" name="copertina" value="<?php echo htmlspecialchars($alb['copertina']); ?>" class="form-input" style="width: 100px;" />
                                </td>
                                <td style="padding: 14px; text-align: center; white-space: nowrap;">
                                    <button type="submit" class="btn-green" style="padding: 6px 14px; margin-right: 6px;">Salva</button>
                                    <a href="gestione_discografia.php?elimina=<?php echo $alb['id']; ?>" class="btn-red" onclick="return confirm('Sei sicuro di voler eliminare questo album?');">Elimina</a>
                                </td>
                            </form>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="padding: 20px; text-align: center; color: #888;">Nessun album trovato nella discografia.</td>
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