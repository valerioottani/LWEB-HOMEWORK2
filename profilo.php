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
    $numero_carta = isset($_POST['numero_carta']) ? trim($_POST['numero_carta']) : '';
    $scadenza_carta = isset($_POST['scadenza_carta']) ? trim($_POST['scadenza_carta']) : '';
    $cvv = isset($_POST['cvv']) ? trim($_POST['cvv']) : '';
    $nuova_password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $ricarica_buono = isset($_POST['ricarica_buono']) && $_POST['ricarica_buono'] !== '' ? (float)$_POST['ricarica_buono'] : 0;

    if (!empty($nuovo_username)) {
        // Controllo unicità username
        $stmt_chk = $conn->prepare("SELECT id FROM `" . TAB_USERS . "` WHERE username = ? AND username != ?");
        $stmt_chk->bind_param("ss", $nuovo_username, $username_corrente);
        $stmt_chk->execute();
        $stmt_chk->store_result();

        if ($stmt_chk->num_rows > 0) {
            $errore = "Questo username è già in uso da un altro utente.";
        }
        $stmt_chk->close();

        if (empty($errore)) {
            // Se l'utente ha richiesto di ricaricare buoni
            $extra_query_buono = "";
            if ($ricarica_buono > 0) {
                // Aggiorniamo aggiungendo al saldo esistente
                $conn->query("UPDATE `" . TAB_USERS . "` SET saldo_buoni = saldo_buoni + $ricarica_buono WHERE username = '" . $conn->real_escape_string($username_corrente) . "'");
            }

            if (!empty($nuova_password)) {
                $hashed = password_hash($nuova_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE `" . TAB_USERS . "` SET username = ?, nome_completo = ?, eta = ?, data_nascita = ?, indirizzo = ?, numero_carta = ?, scadenza_carta = ?, cvv = ?, password = ? WHERE username = ?");
                $stmt->bind_param("ssisssssss", $nuovo_username, $nome_completo, $eta, $data_nascita, $indirizzo, $numero_carta, $scadenza_carta, $cvv, $hashed, $username_corrente);
            } else {
                $stmt = $conn->prepare("UPDATE `" . TAB_USERS . "` SET username = ?, nome_completo = ?, eta = ?, data_nascita = ?, indirizzo = ?, numero_carta = ?, scadenza_carta = ?, cvv = ? WHERE username = ?");
                $stmt->bind_param("ssissssss", $nuovo_username, $nome_completo, $eta, $data_nascita, $indirizzo, $numero_carta, $scadenza_carta, $cvv, $username_corrente);
            }

            if ($stmt && $stmt->execute()) {
                $_SESSION['user'] = $nuovo_username;
                $username_corrente = $nuovo_username;
                $messaggio = "Profilo, dati di pagamento e buoni aggiornati con successo!";
                if ($ricarica_buono > 0) {
                    $messaggio .= " Ricarica di € " . number_format($ricarica_buono, 2) . " effettuata con successo!";
                }
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

// Recupero dati utente
$res_user = ['username' => $username_corrente, 'nome_completo' => '', 'eta' => '', 'data_nascita' => '', 'indirizzo' => '', 'numero_carta' => '', 'scadenza_carta' => '', 'cvv' => '', 'saldo_buoni' => 0.00];
$stmt_get = $conn->prepare("SELECT username, nome_completo, eta, data_nascita, indirizzo, numero_carta, scadenza_carta, cvv, saldo_buoni FROM `" . TAB_USERS . "` WHERE username = ?");
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

    <div style="margin-left: 230px; padding: 40px; display: flex; justify-content: center; box-sizing: border-box;">
        <div style="background-color: #181818; padding: 30px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); max-width: 700px; width: 100%; box-sizing: border-box;">
            <h1 style="font-size: 24px; font-weight: bold; margin-top: 0; margin-bottom: 20px;">Il Mio Profilo e Pagamenti</h1>

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
                
                <!-- DATI PERSONALI -->
                <h3 style="font-size: 16px; color: #1db954; border-bottom: 1px solid #333; padding-bottom: 8px; margin-top: 0;">Informazioni Personali</h3>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; color: #b3b3b3; margin-bottom: 6px;">Username</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($res_user['username'] ?? ''); ?>" style="width: 100%; padding: 12px; background-color: #202020; border: 1px solid #444; color: #fff; border-radius: 6px; box-sizing: border-box;" required="required" />
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; color: #b3b3b3; margin-bottom: 6px;">Nome Completo</label>
                    <input type="text" name="nome_completo" value="<?php echo htmlspecialchars($res_user['nome_completo'] ?? ''); ?>" placeholder="Il tuo nome e cognome" style="width: 100%; padding: 12px; background-color: #202020; border: 1px solid #444; color: #fff; border-radius: 6px; box-sizing: border-box;" />
                </div>

                <div style="display: flex; gap: 16px; margin-bottom: 16px;">
                    <div style="flex: 1;">
                        <label style="display: block; font-size: 12px; color: #b3b3b3; margin-bottom: 6px;">Età</label>
                        <input type="number" name="eta" value="<?php echo htmlspecialchars($res_user['eta'] ?? ''); ?>" style="width: 100%; padding: 12px; background-color: #202020; border: 1px solid #444; color: #fff; border-radius: 6px; box-sizing: border-box;" min="1" max="120" />
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; font-size: 12px; color: #b3b3b3; margin-bottom: 6px;">Data di Nascita</label>
                        <input type="date" name="data_nascita" value="<?php echo htmlspecialchars($res_user['data_nascita'] ?? ''); ?>" style="width: 100%; padding: 12px; background-color: #202020; border: 1px solid #444; color: #fff; border-radius: 6px; box-sizing: border-box;" />
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; color: #b3b3b3; margin-bottom: 6px;">Indirizzo di Casa (per spedizione merchandise)</label>
                    <input type="text" name="indirizzo" value="<?php echo htmlspecialchars($res_user['indirizzo'] ?? ''); ?>" placeholder="Via, Città, CAP" style="width: 100%; padding: 12px; background-color: #202020; border: 1px solid #444; color: #fff; border-radius: 6px; box-sizing: border-box;" />
                </div>

                <!-- METODO DI PAGAMENTO -->
                <h3 style="font-size: 16px; color: #1db954; border-bottom: 1px solid #333; padding-bottom: 8px; margin-top: 24px;">Metodo di Pagamento (per Merchandise e Buoni)</h3>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; color: #b3b3b3; margin-bottom: 6px;">Numero Carta di Credito</label>
                    <input type="text" name="numero_carta" value="<?php echo htmlspecialchars($res_user['numero_carta'] ?? ''); ?>" placeholder="1234 5678 9012 3456" style="width: 100%; padding: 12px; background-color: #202020; border: 1px solid #444; color: #fff; border-radius: 6px; box-sizing: border-box;" />
                </div>

                <div style="display: flex; gap: 16px; margin-bottom: 16px;">
                    <div style="flex: 1;">
                        <label style="display: block; font-size: 12px; color: #b3b3b3; margin-bottom: 6px;">Scadenza (MM/AA)</label>
                        <input type="text" name="scadenza_carta" value="<?php echo htmlspecialchars($res_user['scadenza_carta'] ?? ''); ?>" placeholder="MM/AA" style="width: 100%; padding: 12px; background-color: #202020; border: 1px solid #444; color: #fff; border-radius: 6px; box-sizing: border-box;" />
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; font-size: 12px; color: #b3b3b3; margin-bottom: 6px;">CVV</label>
                        <div style="display: flex; gap: 8px;">
                            <input type="password" id="campo_cvv" name="cvv" value="<?php echo htmlspecialchars($res_user['cvv'] ?? ''); ?>" placeholder="123" maxlength="4" style="width: 100%; padding: 12px; background-color: #202020; border: 1px solid #444; color: #fff; border-radius: 6px; box-sizing: border-box;" />
                            <button type="button" onclick="toggleCvv()" style="background-color: #333; color: #fff; border: 1px solid #444; padding: 0 12px; border-radius: 6px; cursor: pointer; font-size: 12px; white-space: nowrap;" title="Mostra/Nascondi CVV">👁️</button>
                        </div>
                    </div>
                </div>

                <!-- GESTIONE BUONI E CREDITO APPLICAZIONE -->
                <h3 style="font-size: 16px; color: #1db954; border-bottom: 1px solid #333; padding-bottom: 8px; margin-top: 24px;">Portafoglio e Buoni</h3>
                
                <div style="background-color: #222; padding: 16px; border-radius: 8px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="font-size: 12px; color: #b3b3b3; display: block;">Saldo Buoni Disponibile:</span>
                        <span style="font-size: 20px; font-weight: bold; color: #1db954;">€ <?php echo number_format($res_user['saldo_buoni'] ?? 0, 2); ?></span>
                    </div>
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 12px; color: #b3b3b3; margin-bottom: 6px;">Ricarica Buoni tramite Carta</label>
                    <select name="ricarica_buono" style="width: 100%; padding: 12px; background-color: #202020; border: 1px solid #444; color: #fff; border-radius: 6px; box-sizing: border-box;">
                        <option value="0">Seleziona importo ricarica...</option>
                        <option value="10">Ricarica 10,00 €</option>
                        <option value="25">Ricarica 25,00 €</option>
                        <option value="50">Ricarica 50,00 €</option>
                        <option value="100">Ricarica 100,00 €</option>
                    </select>
                </div>

                <!-- CAMBIO PASSWORD -->
                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 12px; color: #b3b3b3; margin-bottom: 6px;">Nuova Password (lascia vuoto per non modificarla)</label>
                    <input type="password" name="password" placeholder="••••••••" style="width: 100%; padding: 12px; background-color: #202020; border: 1px solid #444; color: #fff; border-radius: 6px; box-sizing: border-box;" />
                </div>

                <button type="submit" style="background-color: #1db954; color: #000; border: none; padding: 12px 24px; border-radius: 500px; font-weight: bold; font-size: 13px; cursor: pointer;">Salva Modifiche e Credito</button>
            </form>
        </div>
    </div>

    <script type="text/javascript">
        function toggleCvv() {
            var inputCvv = document.getElementById('campo_cvv');
            if (inputCvv.type === 'password') {
                inputCvv.type = 'text';
            } else {
                inputCvv.type = 'password';
            }
        }
    </script>
</body>
</html>
<?php 
if (isset($conn) && !$conn->connect_error) {
    $conn->close(); 
}
?>