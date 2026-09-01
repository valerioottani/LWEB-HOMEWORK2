<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connection.php';

if (!isset($_SESSION['user']) || $_SESSION['ruolo'] !== 'admin') {
    header('Location: homepage.php');
    exit();
}

$messaggio = '';
$errore = '';

// 1. Gestione AGGIUNTA di un nuovo utente dal pannello admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione']) && $_POST['azione'] === 'aggiungi_utente') {
    $new_user = trim($_POST['new_username']);
    $new_nome = trim($_POST['new_nome_completo']);
    $new_eta = isset($_POST['new_eta']) && $_POST['new_eta'] !== '' ? (int)$_POST['new_eta'] : null;
    $new_data = isset($_POST['new_data_nascita']) && $_POST['new_data_nascita'] !== '' ? trim($_POST['new_data_nascita']) : null;
    $new_indirizzo = trim($_POST['new_indirizzo']);
    $new_ruolo = trim($_POST['new_ruolo']);
    $new_pass = trim($_POST['new_password']);

    if (!empty($new_user) && !empty($new_pass)) {
        $chk = $conn->prepare("SELECT id FROM `" . TAB_USERS . "` WHERE username = ?");
        $chk->bind_param("s", $new_user);
        $chk->execute();
        $chk->store_result();

        if ($chk->num_rows > 0) {
            $errore = "Errore: Lo username '$new_user' esiste già!";
            $chk->close();
        } else {
            $chk->close();
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt_ins = $conn->prepare("INSERT INTO `" . TAB_USERS . "` (username, password, ruolo, nome_completo, eta, data_nascita, indirizzo) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt_ins) {
                $stmt_ins->bind_param("ssssiss", $new_user, $hashed, $new_ruolo, $new_nome, $new_eta, $new_data, $new_indirizzo);
                if ($stmt_ins->execute()) {
                    $messaggio = "Nuovo utente aggiunto con successo!";
                } else {
                    $errore = "Errore durante l'inserimento del nuovo utente.";
                }
                $stmt_ins->close();
            }
        }
    } else {
        $errore = "Username e Password sono obbligatori per il nuovo utente.";
    }
}

// 2. Gestione MODIFICA di un utente esistente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $id_mod = (int)$_POST['user_id'];
    $nuovo_username = trim($_POST['username']);
    $nuovo_ruolo = trim($_POST['ruolo']);
    $nuovo_nome = trim($_POST['nome_completo']);
    $nuova_eta = isset($_POST['eta']) && $_POST['eta'] !== '' ? (int)$_POST['eta'] : null;
    $nuova_data = isset($_POST['data_nascita']) && $_POST['data_nascita'] !== '' ? trim($_POST['data_nascita']) : null;
    $nuovo_indirizzo = trim($_POST['indirizzo']);
    $nuova_pass = trim($_POST['password']);

    if (!empty($nuova_pass)) {
        $hashed = password_hash($nuova_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE `" . TAB_USERS . "` SET username = ?, ruolo = ?, nome_completo = ?, eta = ?, data_nascita = ?, indirizzo = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssisssi", $nuovo_username, $nuovo_ruolo, $nuovo_nome, $nuova_eta, $nuova_data, $nuovo_indirizzo, $hashed, $id_mod);
    } else {
        $stmt = $conn->prepare("UPDATE `" . TAB_USERS . "` SET username = ?, ruolo = ?, nome_completo = ?, eta = ?, data_nascita = ?, indirizzo = ? WHERE id = ?");
        $stmt->bind_param("sssissi", $nuovo_username, $nuovo_ruolo, $nuovo_nome, $nuova_eta, $nuova_data, $nuovo_indirizzo, $id_mod);
    }
    
    if ($stmt && $stmt->execute()) {
        $messaggio = "Account aggiornato con successo!";
    } else {
        $errore = "Errore durante l'aggiornamento (forse lo username è già in uso).";
    }
    if ($stmt) {
        $stmt->close();
    }
}

// 3. Recupero aggiornato di tutti gli utenti
$res_utenti = $conn->query("SELECT id, username, ruolo, nome_completo, eta, data_nascita, indirizzo FROM `" . TAB_USERS . "` ORDER BY id ASC");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify - Gestione Account</title>
</head>
<body style="background: linear-gradient(180deg, #1f1f1f 0%, #121212 40%, #0a0a0a 100%); background-attachment: fixed; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0; min-height: 100vh;">

    <?php include 'menu.php'; ?>

    <div style="margin-left: 230px; padding: 32px; max-width: 1400px; box-sizing: border-box;">
        <div style="background-color: #181818; padding: 30px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); margin-bottom: 30px;">
            <h1 style="font-size: 24px; font-weight: bold; margin-top: 0; margin-bottom: 20px;">Gestione Account (Admin)</h1>

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

            <!-- FORM PER AGGIUNGERE UN NUOVO UTENTE -->
            <div style="background-color: #222222; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #333;">
                <h3 style="margin-top: 0; font-size: 15px; color: #1db954; margin-bottom: 15px;">+ Aggiungi Nuovo Utente / Admin</h3>
                <form action="gestione_utenti.php" method="POST" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;">
                    <input type="hidden" name="azione" value="aggiungi_utente" />
                    <div>
                        <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Username</label>
                        <input type="text" name="new_username" placeholder="Username" style="background: #181818; color: #fff; padding: 8px; border: 1px solid #444; border-radius: 4px; font-size: 12px;" required="required" />
                    </div>
                    <div>
                        <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Nome e Cognome</label>
                        <input type="text" name="new_nome_completo" placeholder="Nome Completo" style="background: #181818; color: #fff; padding: 8px; border: 1px solid #444; border-radius: 4px; font-size: 12px;" />
                    </div>
                    <div>
                        <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Età</label>
                        <input type="number" name="new_eta" placeholder="Età" style="background: #181818; color: #fff; padding: 8px; border: 1px solid #444; border-radius: 4px; width: 60px; font-size: 12px;" min="1" max="120" />
                    </div>
                    <div>
                        <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Data di Nascita</label>
                        <input type="date" name="new_data_nascita" style="background: #181818; color: #fff; padding: 8px; border: 1px solid #444; border-radius: 4px; font-size: 12px;" />
                    </div>
                    <div>
                        <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Indirizzo</label>
                        <input type="text" name="new_indirizzo" placeholder="Indirizzo spedizione" style="background: #181818; color: #fff; padding: 8px; border: 1px solid #444; border-radius: 4px; font-size: 12px;" />
                    </div>
                    <div>
                        <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Ruolo</label>
                        <select name="new_ruolo" style="background: #181818; color: #fff; padding: 8px; border: 1px solid #444; border-radius: 4px; font-size: 12px;">
                            <option value="user">Utente</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Password</label>
                        <input type="password" name="new_password" placeholder="Password" style="background: #181818; color: #fff; padding: 8px; border: 1px solid #444; border-radius: 4px; font-size: 12px;" required="required" />
                    </div>
                    <div>
                        <button type="submit" style="background-color: #1db954; color: #000; border: none; padding: 9px 18px; border-radius: 500px; font-weight: bold; font-size: 12px; cursor: pointer;">Crea Account</button>
                    </div>
                </form>
            </div>

            <!-- TABELLA LISTA UTENTI ESISTENTI -->
            <h3 style="font-size: 16px; margin-bottom: 15px;">Lista Utenti Registrati</h3>
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 1px solid #333; color: #b3b3b3; font-size: 11px; text-transform: uppercase;">
                        <th style="padding: 10px;">ID</th>
                        <th style="padding: 10px;">Username</th>
                        <th style="padding: 10px;">Ruolo</th>
                        <th style="padding: 10px;">Nome Completo</th>
                        <th style="padding: 10px;">Età</th>
                        <th style="padding: 10px;">Data di Nascita</th>
                        <th style="padding: 10px;">Indirizzo</th>
                        <th style="padding: 10px;">Nuova Password</th>
                        <th style="padding: 10px; text-align: center;">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($res_utenti && $res_utenti->num_rows > 0): ?>
                        <?php while ($u = $res_utenti->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid #222;">
                                <form action="gestione_utenti.php" method="POST">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>" />
                                    <td style="padding: 14px; color: #888;"><?php echo $u['id']; ?></td>
                                    <td style="padding: 14px;">
                                        <input type="text" name="username" value="<?php echo htmlspecialchars($u['username']); ?>" style="background: #202020; color: #fff; padding: 6px; border: 1px solid #444; border-radius: 4px; width: 100px; font-size: 12px; font-weight: bold;" required="required" />
                                    </td>
                                    <td style="padding: 14px;">
                                        <select name="ruolo" style="background: #202020; color: #fff; padding: 6px; border: 1px solid #444; border-radius: 4px; font-size: 12px;">
                                            <option value="user" <?php if($u['ruolo']=='user') echo 'selected="selected"'; ?>>Utente</option>
                                            <option value="admin" <?php if($u['ruolo']=='admin') echo 'selected="selected"'; ?>>Admin</option>
                                        </select>
                                    </td>
                                    <td style="padding: 14px;">
                                        <input type="text" name="nome_completo" value="<?php echo htmlspecialchars($u['nome_completo'] ?? ''); ?>" placeholder="Nome" style="background: #202020; color: #fff; padding: 6px; border: 1px solid #444; border-radius: 4px; width: 110px; font-size: 12px;" />
                                    </td>
                                    <td style="padding: 14px;">
                                        <input type="number" name="eta" value="<?php echo htmlspecialchars($u['eta'] ?? ''); ?>" placeholder="Età" style="background: #202020; color: #fff; padding: 6px; border: 1px solid #444; border-radius: 4px; width: 50px; font-size: 12px;" min="1" max="120" />
                                    </td>
                                    <td style="padding: 14px;">
                                        <input type="date" name="data_nascita" value="<?php echo htmlspecialchars($u['data_nascita'] ?? ''); ?>" style="background: #202020; color: #fff; padding: 6px; border: 1px solid #444; border-radius: 4px; font-size: 12px;" />
                                    </td>
                                    <td style="padding: 14px;">
                                        <input type="text" name="indirizzo" value="<?php echo htmlspecialchars($u['indirizzo'] ?? ''); ?>" placeholder="Indirizzo" style="background: #202020; color: #fff; padding: 6px; border: 1px solid #444; border-radius: 4px; width: 130px; font-size: 12px;" />
                                    </td>
                                    <td style="padding: 14px;">
                                        <input type="password" name="password" placeholder="Lascia vuoto" style="background: #202020; color: #fff; padding: 6px; border: 1px solid #444; border-radius: 4px; width: 85px; font-size: 12px;" />
                                    </td>
                                    <td style="padding: 14px; text-align: center;">
                                        <button type="submit" style="background-color: #1db954; color: #000; border: none; padding: 7px 16px; border-radius: 500px; font-weight: bold; font-size: 12px; cursor: pointer;">Salva</button>
                                    </td>
                                </form>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="padding: 20px; text-align: center; color: #888;">Nessun utente trovato.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
<?php 
if (isset($conn) && !$conn->connect_error) {
    $conn->close(); 
}
?>