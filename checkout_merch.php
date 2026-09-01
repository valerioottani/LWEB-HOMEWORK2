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
$merchandise_id = isset($_GET['merch_id']) ? (int)$_GET['merch_id'] : (isset($_POST['merch_id']) ? (int)$_POST['merch_id'] : 0);
$errore = '';
$ordine_completato = false;
$ultimo_id_ordine = 0;

// GESTIONE INVIO FORM DI CHECKOUT (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['conferma_ordine'])) {
    $quantita = isset($_POST['quantita']) ? max(1, (int)$_POST['quantita']) : 1;
    $nuovo_indirizzo = isset($_POST['indirizzo']) ? trim($_POST['indirizzo']) : '';

    // Aggiornamento indirizzo utente nel profilo se modificato
    if (!empty($nuovo_indirizzo)) {
        $stmt_upd = $conn->prepare("UPDATE `" . TAB_USERS . "` SET indirizzo = ? WHERE username = ?");
        $stmt_upd->bind_param("ss", $nuovo_indirizzo, $username_corrente);
        $stmt_upd->execute();
        $stmt_upd->close();
    }

    // Registrazione dell'ordine nel database con la quantità scelta
    $stmt_acq = $conn->prepare("INSERT INTO `acquisti_merch` (username, merchandise_id, quantita) VALUES (?, ?, ?)");
    if ($stmt_acq) {
        $stmt_acq->bind_param("sii", $username_corrente, $merchandise_id, $quantita);
        if ($stmt_acq->execute()) {
            $ultimo_id_ordine = $conn->insert_id;
            $ordine_completato = true;
        } else {
            $errore = "Errore durante la registrazione dell'ordine nel database.";
        }
        $stmt_acq->close();
    } else {
        $errore = "Errore di connessione al database.";
    }
}

// Recupero informazioni sul prodotto e sull'album
if ($merchandise_id > 0) {
    $stmt_prod = $conn->prepare("SELECT m.*, a.titolo AS album_titolo, a.id AS album_id, a.copertina, art.nome AS artista 
                                 FROM `merchandise_album` m 
                                 JOIN `" . TAB_ALBUMS . "` a ON m.album_id = a.id 
                                 JOIN `" . TAB_ARTISTS . "` art ON a.artista_id = art.id 
                                 WHERE m.id = ?");
    $stmt_prod->bind_param("i", $merchandise_id);
    $stmt_prod->execute();
    $res_prod = $stmt_prod->get_result();

    if ($res_prod && $res_prod->num_rows > 0) {
        $prodotto = $res_prod->fetch_assoc();
    } else {
        die("Prodotto non trovato.");
    }
    $stmt_prod->close();
} else {
    header('Location: discografia.php');
    exit();
}

// Recupero indirizzo corrente dell'utente
$stmt_usr = $conn->prepare("SELECT nome_completo, indirizzo FROM `" . TAB_USERS . "` WHERE username = ?");
$stmt_usr->bind_param("s", $username_corrente);
$stmt_usr->execute();
$user_info = $stmt_usr->get_result()->fetch_assoc();
$stmt_usr->close();

$indirizzo_salvato = !empty($user_info['indirizzo']) ? $user_info['indirizzo'] : '';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify - Checkout Merchandise</title>
    <style type="text/css">
        .form-input {
            background-color: #222;
            color: #fff;
            border: 1px solid #444;
            padding: 10px;
            border-radius: 6px;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
        }
        .btn-green {
            background-color: #1db954;
            color: #000000;
            border: none;
            padding: 12px 24px;
            border-radius: 500px;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }
        .btn-green:hover {
            background-color: #1ed760;
            transform: scale(1.02);
        }
    </style>
</head>
<body style="background: linear-gradient(180deg, #1f1f1f 0%, #121212 40%, #0a0a0a 100%); background-attachment: fixed; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0; min-height: 100vh;">

    <?php include 'menu.php'; ?>

    <div style="margin-left: 230px; padding: 40px; max-width: 900px; box-sizing: border-box;">
        
        <?php if (!empty($errore)): ?>
            <div style="background-color: rgba(226,33,52,0.15); border: 1px solid #e22134; color: #e22134; padding: 14px; border-radius: 8px; margin-bottom: 24px; font-size: 13px;">
                <?php echo htmlspecialchars($errore); ?>
            </div>
        <?php endif; ?>

        <?php if ($ordine_completato): ?>
            <!-- MESSAGGIO DI SUCCESSO E LINK ALLA RICEVUTA GRAFICA -->
            <div style="background-color: #181818; padding: 40px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.6);">
                <div style="font-size: 48px; margin-bottom: 16px;">✅</div>
                <h1 style="font-size: 28px; font-weight: 900; margin: 0 0 10px 0; color: #1db954;">Ordine Confermato con Successo!</h1>
                <p style="color: #b3b3b3; font-size: 14px; line-height: 1.6; margin-bottom: 30px;">Il tuo pagamento è andato a buon fine. Clicca sul pulsante sottostante per visualizzare, stampare o salvare la ricevuta in PDF.</p>
                
                <?php 
                    $metodo_scelto = isset($_POST['metodo_pagamento']) ? $_POST['metodo_pagamento'] : 'carta';
                    $num_carta = isset($_POST['numero_carta']) ? trim($_POST['numero_carta']) : '';
                    $ultime_cifre_carta = (strlen($num_carta) >= 4) ? substr($num_carta, -4) : '1234';
                ?>
                <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
                    <a href="ricevuta_ordine.php?id=<?php echo $ultimo_id_ordine; ?>&metodo=<?php echo $metodo_scelto; ?>&carta=<?php echo $ultime_cifre_carta; ?>" target="_blank" style="background-color: #1db954; color: #000000; padding: 12px 24px; border-radius: 500px; font-weight: bold; text-decoration: none; font-size: 13px;">📄 Visualizza / Salva Ricevuta</a>
                    <a href="discografia.php" style="background-color: #282828; color: #ffffff; padding: 12px 24px; border-radius: 500px; font-weight: bold; text-decoration: none; font-size: 13px;">📦 Visualizza Storico Acquisti</a>
                </div>
            </div>

        <?php else: ?>
            <!-- FORM DI CHECKOUT -->
            <h1 style="font-size: 28px; font-weight: 900; margin-bottom: 24px;">Riepilogo Ordine & Checkout</h1>

            <div style="display: flex; gap: 32px; background-color: #181818; padding: 30px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                
                <!-- Anteprima Prodotto -->
                <div style="width: 250px; text-align: center;">
                    <img src="img/<?php echo htmlspecialchars($prodotto['immagine_prodotto']); ?>" alt="" style="width: 180px; height: 180px; object-fit: contain; background-color: #222; border-radius: 8px; margin-bottom: 12px;" />
                    <h3 style="font-size: 16px; font-weight: bold; margin: 0 0 4px 0;"><?php echo htmlspecialchars($prodotto['tipo_prodotto']); ?></h3>
                    <p style="font-size: 13px; color: #b3b3b3; margin: 0 0 8px 0;"><?php echo htmlspecialchars($prodotto['album_titolo']) . ' (' . htmlspecialchars($prodotto['artista']) . ')'; ?></p>
                    <p style="font-size: 18px; font-weight: 900; color: #1db954; margin: 0;">€ <span id="prezzo_unitario"><?php echo number_format($prodotto['prezzo'], 2, '.', ''); ?></span></p>
                </div>

                <!-- Form Dati -->
                <div style="flex-grow: 1;">
                    <form action="checkout_merch.php?merch_id=<?php echo $merchandise_id; ?>" method="POST">
                        <input type="hidden" name="merch_id" value="<?php echo $prodotto['id']; ?>" />
                        <input type="hidden" name="conferma_ordine" value="1" />
                        
                        <div style="margin-bottom: 20px;">
                            <label style="font-size: 12px; color: #b3b3b3; display: block; margin-bottom: 6px; text-transform: uppercase; font-weight: bold;">Quantità</label>
                            <input type="number" id="quantita" name="quantita" value="1" min="1" max="10" class="form-input" style="width: 100px;" oninput="calcolaTotale()" required="required" />
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="font-size: 12px; color: #b3b3b3; display: block; margin-bottom: 6px; text-transform: uppercase; font-weight: bold;">Indirizzo di Spedizione</label>
                            <input type="text" name="indirizzo" value="<?php echo htmlspecialchars($indirizzo_salvato); ?>" placeholder="Inserisci via, civico, città e CAP..." class="form-input" required="required" />
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="font-size: 12px; color: #b3b3b3; display: block; margin-bottom: 6px; text-transform: uppercase; font-weight: bold;">Metodo di Pagamento</label>
                            <select name="metodo_pagamento" id="metodo_pagamento" class="form-input" onchange="gestisciCampiCarta()">
                                <option value="carta">💳 Carta di Credito / Debito</option>
                                <option value="paypal">🅿️ PayPal</option>
                                <option value="contrassegno">💵 Contrassegno alla Consegna</option>
                            </select>
                        </div>

                        <!-- Sezione Dati Carta -->
                        <div id="sezione_carta" style="background-color: #222; padding: 16px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #333;">
                            <p style="font-size: 13px; font-weight: bold; margin: 0 0 12px 0; color: #1db954;">Dati della Carta</p>
                            
                            <div style="margin-bottom: 12px;">
                                <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Numero Carta</label>
                                <input type="text" name="numero_carta" placeholder="1234 5678 9012 3456" maxlength="19" class="form-input" />
                            </div>

                            <div style="display: flex; gap: 12px;">
                                <div style="flex: 1;">
                                    <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Scadenza (MM/AA)</label>
                                    <input type="text" name="scadenza_carta" placeholder="MM/AA" maxlength="5" class="form-input" />
                                </div>
                                <div style="flex: 1;">
                                    <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">CVV</label>
                                    <input type="password" name="cvv_carta" placeholder="123" maxlength="4" class="form-input" />
                                </div>
                            </div>
                        </div>

                        <div style="background-color: #222; padding: 16px; border-radius: 8px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 15px; font-weight: bold;">Totale Complessivo:</span>
                            <span style="font-size: 20px; font-weight: 900; color: #1db954;">€ <span id="totale_ordine"><?php echo number_format($prodotto['prezzo'], 2, ',', '.'); ?></span></span>
                        </div>

                        <button type="submit" class="btn-green">Conferma e Acquista Ora</button>
                    </form>
                </div>

            </div>

            <div style="margin-top: 24px;">
                <a href="dettaglio_album.php?id=<?php echo $prodotto['album_id']; ?>" style="color: #1db954; text-decoration: none; font-size: 13px; font-weight: bold;">← Torna all'Album</a>
            </div>
        <?php endif; ?>

    </div>

    <script type="text/javascript">
        function calcolaTotale() {
            var prezzo = parseFloat(document.getElementById('prezzo_unitario').innerText);
            var qta = parseInt(document.getElementById('quantita').value) || 1;
            var totale = (prezzo * qta).toFixed(2).replace('.', ',');
            document.getElementById('totale_ordine').innerText = totale;
        }

        function gestisciCampiCarta() {
            var metodo = document.getElementById('metodo_pagamento').value;
            var sezioneCarta = document.getElementById('sezione_carta');
            if (metodo === 'carta') {
                sezioneCarta.style.display = 'block';
            } else {
                sezioneCarta.style.display = 'none';
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