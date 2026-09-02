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

// Recupero indirizzo, saldo buoni e dati della carta salvati nel profilo dell'utente
$stmt_usr = $conn->prepare("SELECT nome_completo, indirizzo, saldo_buoni, numero_carta, scadenza_carta, cvv FROM `" . TAB_USERS . "` WHERE username = ?");
$stmt_usr->bind_param("s", $username_corrente);
$stmt_usr->execute();
$user_info = $stmt_usr->get_result()->fetch_assoc();
$stmt_usr->close();

$indirizzo_salvato = !empty($user_info['indirizzo']) ? $user_info['indirizzo'] : '';
$saldo_disponibile = (float)($user_info['saldo_buoni'] ?? 0);
$carta_salvata = !empty($user_info['numero_carta']) ? $user_info['numero_carta'] : '';
$cvv_salvato = !empty($user_info['cvv']) ? $user_info['cvv'] : '';

// GESTIONE INVIO FORM DI CHECKOUT (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['conferma_ordine'])) {
    $quantita = isset($_POST['quantita']) ? max(1, (int)$_POST['quantita']) : 1;
    $nuovo_indirizzo = isset($_POST['indirizzo']) ? trim($_POST['indirizzo']) : '';
    $metodo_pagamento = isset($_POST['metodo_pagamento']) ? $_POST['metodo_pagamento'] : 'carta';
    $pin_inserito = isset($_POST['pin_carta']) ? trim($_POST['pin_carta']) : '';
    
    $prezzo_unitario = (float)$prodotto['prezzo'];
    $totale_ordine = $prezzo_unitario * $quantita;

    // Controllo se sceglie di pagare con i buoni
    if ($metodo_pagamento === 'buoni') {
        if ($saldo_disponibile < $totale_ordine) {
            $errore = "Saldo buoni insufficiente per completare l'ordine. Ricarica il portafoglio dal tuo profilo.";
        }
    } else {
        if (empty($carta_salvata)) {
            $errore = "Non hai una carta salvata nel tuo profilo. Aggiungila prima di procedere.";
        } elseif ($pin_inserito !== $cvv_salvato) {
            $errore = "PIN / CVV non corretto. Inserisci il codice di sicurezza associato alla tua carta.";
        }
    }

    if (empty($errore)) {
        if (!empty($nuovo_indirizzo)) {
            $stmt_upd = $conn->prepare("UPDATE `" . TAB_USERS . "` SET indirizzo = ? WHERE username = ?");
            $stmt_upd->bind_param("ss", $nuovo_indirizzo, $username_corrente);
            $stmt_upd->execute();
            $stmt_upd->close();
        }

        if ($metodo_pagamento === 'buoni') {
            $nuovo_saldo = $saldo_disponibile - $totale_ordine;
            $stmt_buoni = $conn->prepare("UPDATE `" . TAB_USERS . "` SET saldo_buoni = ? WHERE username = ?");
            $stmt_buoni->bind_param("ds", $nuovo_saldo, $username_corrente);
            $stmt_buoni->execute();
            $stmt_buoni->close();
            $saldo_disponibile = $nuovo_saldo;
        }

        $stmt_acq = $conn->prepare("INSERT INTO `acquisti_merch` (username, merchandise_id, quantita) VALUES (?, ?, ?)");
        if ($stmt_acq) {
            $stmt_acq->bind_param("sii", $username_corrente, $merchandise_id, $quantita);
            if ($stmt_acq->execute()) {
                $ordine_completato = true;
            } else {
                $errore = "Errore durante la registrazione dell'ordine nel database.";
            }
            $stmt_acq->close();
        } else {
            $errore = "Errore di connessione al database.";
        }
    }
}
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
            <!-- MESSAGGIO DI SUCCESSO E REINDIRIZZAMENTO ALLO STORICO -->
            <div style="background-color: #181818; padding: 40px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.6);">
                <div style="font-size: 48px; margin-bottom: 16px;">✅</div>
                <h1 style="font-size: 28px; font-weight: 900; margin: 0 0 10px 0; color: #1db954;">Ordine Confermato con Successo!</h1>
                <p style="color: #b3b3b3; font-size: 14px; line-height: 1.6; margin-bottom: 30px;">Il tuo ordine è stato registrato correttamente. Puoi visualizzare lo storico dei tuoi acquisti e stampare le relative ricevute direttamente dalla nuova sezione dedicata nella barra laterale.</p>
                
                <div>
                    <a href="storico_acquisti.php" style="background-color: #1db954; color: #000000; padding: 12px 28px; border-radius: 500px; font-weight: bold; text-decoration: none; font-size: 13px; display: inline-block;">📦 Vai a Storico Acquisti</a>
                </div>
            </div>

        <?php else: ?>
            <!-- FORM DI CHECKOUT -->
            <h1 style="font-size: 28px; font-weight: 900; margin-bottom: 24px;">Riepilogo Ordine & Checkout</h1>

            <div style="display: flex; gap: 32px; background-color: #181818; padding: 30px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                
                <div style="width: 250px; text-align: center;">
                    <img src="img/<?php echo htmlspecialchars($prodotto['immagine_prodotto']); ?>" alt="" style="width: 180px; height: 180px; object-fit: contain; background-color: #222; border-radius: 8px; margin-bottom: 12px;" />
                    <h3 style="font-size: 16px; font-weight: bold; margin: 0 0 4px 0;"><?php echo htmlspecialchars($prodotto['tipo_prodotto']); ?></h3>
                    <p style="font-size: 13px; color: #b3b3b3; margin: 0 0 8px 0;"><?php echo htmlspecialchars($prodotto['album_titolo']) . ' (' . htmlspecialchars($prodotto['artista']) . ')'; ?></p>
                    <p style="font-size: 18px; font-weight: 900; color: #1db954; margin: 0;">€ <span id="prezzo_unitario"><?php echo number_format($prodotto['prezzo'], 2, '.', ''); ?></span></p>
                </div>

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
                                <option value="buoni"> Saldo Buoni (Disponibile: € <?php echo number_format($saldo_disponibile, 2, ',', '.'); ?>)</option>
                                <option value="carta" selected="selected"> Carta di Credito / Debito</option>
                                <option value="paypal">PayPal</option>
                                <option value="contrassegno"> Contrassegno alla Consegna</option>
                            </select>
                        </div>

                        <div id="sezione_carta" style="background-color: #222; padding: 16px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #333;">
                            <p style="font-size: 13px; font-weight: bold; margin: 0 0 12px 0; color: #1db954;">Verifica Proprietà Carta</p>
                            
                            <div style="margin-bottom: 12px;">
                                <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Carta Registrata</label>
                                <input type="text" value="<?php echo !empty($carta_salvata) ? '•••• •••• •••• ' . substr($carta_salvata, -4) : 'Nessuna carta salvata nel profilo'; ?>" class="form-input" disabled="disabled" style="background-color: #181818; color: #888;" />
                            </div>

                            <div style="margin-bottom: 4px;">
                                <label style="font-size: 11px; color: #b3b3b3; display: block; margin-bottom: 4px;">Inserisci PIN / CVV della tua carta per confermare</label>
                                <input type="password" name="pin_carta" placeholder="Inserisci PIN (es. CVV salvato)" maxlength="4" class="form-input" style="width: 150px;" />
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
            if (metodo === 'carta' || metodo === 'paypal') {
                sezioneCarta.style.display = 'block';
            } else {
                sezioneCarta.style.display = 'none';
            }
        }

        window.onload = function() {
            gestisciCampiCarta();
        };
    </script>
</body>
</html>
<?php 
if (isset($conn) && !$conn->connect_error) {
    $conn->close(); 
}
?>