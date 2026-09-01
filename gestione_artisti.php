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

// 1. GESTIONE AGGIUNTA DI UN NUOVO ARTISTA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione']) && $_POST['azione'] === 'aggiungi') {
    $nome = trim($_POST['nome']);
    $biografia = trim($_POST['biografia']);
    $immagine = !empty(trim($_POST['immagine'])) ? trim($_POST['immagine']) : 'album.png';

    if (!empty($nome)) {
        $stmt_ins = $conn->prepare("INSERT INTO `" . TAB_ARTISTS . "` (nome, biografia, immagine) VALUES (?, ?, ?)");
        if ($stmt_ins) {
            $stmt_ins->bind_param("sss", $nome, $biografia, $immagine);
            if ($stmt_ins->execute()) {
                $messaggio = "Artista aggiunto con successo!";
            } else {
                $errore = "Errore durante l'inserimento dell'artista.";
            }
            $stmt_ins->close();
        }
    } else {
        $errore = "Il nome dell'artista è obbligatorio.";
    }
}

// 2. GESTIONE ELIMINAZIONE DI UN ARTISTA
if (isset($_GET['elimina'])) {
    $id_elimina = (int)$_GET['elimina'];
    $stmt_del = $conn->prepare("DELETE FROM `" . TAB_ARTISTS . "` WHERE id = ?");
    if ($stmt_del) {
        $stmt_del->bind_param("i", $id_elimina);
        if ($stmt_del->execute()) {
            $messaggio = "Artista eliminato con successo!";
        } else {
            $errore = "Errore durante l'eliminazione dell'artista (potrebbe avere album o brani collegati).";
        }
        $stmt_del->close();
    }
}

// 3. GESTIONE MODIFICA DI UN ARTISTA ESISTENTE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione']) && $_POST['azione'] === 'modifica') {
    $id_mod = (int)$_POST['artista_id'];
    $nome = trim($_POST['nome']);
    $biografia = trim($_POST['biografia']);
    $immagine = trim($_POST['immagine']);

    if (!empty($nome)) {
        $stmt_upd = $conn->prepare("UPDATE `" . TAB_ARTISTS . "` SET nome = ?, biografia = ?, immagine = ? WHERE id = ?");
        if ($stmt_upd) {
            $stmt_upd->bind_param("sssi", $nome, $biografia, $immagine, $id_mod);
            if ($stmt_upd->execute()) {
                $messaggio = "Artista aggiornato con successo!";
            } else {
                $errore = "Errore durante l'aggiornamento dell'artista.";
            }
            $stmt_upd->close();
        }
    } else {
        $errore = "Il nome non può essere vuoto.";
    }
}

// Recupero di tutti gli artisti dal database
$res_artisti = $conn->query("SELECT * FROM `" . TAB_ARTISTS . "` ORDER BY id ASC");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify - Gestione Artisti</title>
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

    <div style="margin-left: 230px; padding: 32px; max-width: 1300px; box-sizing: border-box;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h1 style="font-size: 24px; font-weight: bold; margin: 0;">Gestione Artisti Correlati</h1>
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

        <!-- FORM PER AGGIUNGERE UN NUOVO ARTISTA -->
        <div style="background-color: #181818; padding: 24px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05); margin-bottom: 30px;">
            <h3 style="margin-top: 0; font-size: 16px; color: #1db954; margin-bottom: 16px;">+ Aggiungi Nuovo Artista</h3>
            <form action="gestione_artisti.php" method="POST" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
                <input type="hidden" name="azione" value="aggiungi" />
                
                <div>
                    <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Nome Artista</label>
                    <input type="text" name="nome" placeholder="Nome" class="form-input" style="width: 180px;" required="required" />
                </div>
                
                <div style="flex-grow: 1;">
                    <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Biografia</label>
                    <input type="text" name="biografia" placeholder="Breve descrizione..." class="form-input" style="width: 100%;" />
                </div>

                <div>
                    <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Immagine (es. file.jpg)</label>
                    <input type="text" name="immagine" value="album.png" class="form-input" style="width: 140px;" />
                </div>

                <div>
                    <button type="submit" class="btn-green">Crea Artista</button>
                </div>
            </form>
        </div>

        <!-- TABELLA DI GESTIONE / MODIFICA ED ELIMINAZIONE -->
        <h3 style="font-size: 18px; margin-bottom: 16px;">Artisti Esistenti</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; background-color: #181818; border-radius: 8px; overflow: hidden;">
            <thead>
                <tr style="border-bottom: 1px solid #333; color: #b3b3b3; font-size: 11px; text-transform: uppercase;">
                    <th style="padding: 12px;">ID</th>
                    <th style="padding: 12px;">Nome</th>
                    <th style="padding: 12px;">Biografia</th>
                    <th style="padding: 12px;">Immagine</th>
                    <th style="padding: 12px; text-align: center;">Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($res_artisti && $res_artisti->num_rows > 0): ?>
                    <?php while ($art = $res_artisti->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #222;">
                            <form action="gestione_artisti.php" method="POST">
                                <input type="hidden" name="azione" value="modifica" />
                                <input type="hidden" name="artista_id" value="<?php echo $art['id']; ?>" />
                                
                                <td style="padding: 14px; color: #888;"><?php echo $art['id']; ?></td>
                                <td style="padding: 14px;">
                                    <input type="text" name="nome" value="<?php echo htmlspecialchars($art['nome']); ?>" class="form-input" style="width: 160px; font-weight: bold;" required="required" />
                                </td>
                                <td style="padding: 14px;">
                                    <input type="text" name="biografia" value="<?php echo htmlspecialchars(isset($art['biografia']) ? $art['biografia'] : ''); ?>" class="form-input" style="width: 300px;" />
                                </td>
                                <td style="padding: 14px;">
                                    <input type="text" name="immagine" value="<?php echo htmlspecialchars($art['immagine']); ?>" class="form-input" style="width: 120px;" />
                                </td>
                                <td style="padding: 14px; text-align: center; white-space: nowrap;">
                                    <button type="submit" class="btn-green" style="padding: 6px 14px; margin-right: 6px;">Salva</button>
                                    <a href="gestione_artisti.php?elimina=<?php echo $art['id']; ?>" class="btn-red" onclick="return confirm('Sei sicuro di voler eliminare questo artista?');">Elimina</a>
                                </td>
                            </form>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="padding: 20px; text-align: center; color: #888;">Nessun artista trovato.</td>
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