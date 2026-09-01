<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connection.php';

if (isset($_GET['azione']) && $_GET['azione'] === 'rimuovi') {
    $id_rimuovi = (int)$_GET['id'];
    unset($_SESSION['carrello'][$id_rimuovi]);
    header('Location: carrello.php');
    exit;
}

if (isset($_GET['azione']) && $_GET['azione'] === 'svuota') {
    unset($_SESSION['carrello']);
    header('Location: carrello.php');
    exit;
}

$carrello = isset($_SESSION['carrello']) ? $_SESSION['carrello'] : [];
$totale_generale = 0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify - Carrello</title>
    <style type="text/css">
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #181818;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .cart-table th, .cart-table td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            font-size: 14px;
        }
        .cart-table th {
            background-color: #222222;
            color: #b3b3b3;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 1px;
        }
        .action-btn {
            background-color: #1db954;
            color: #000000;
            border: none;
            padding: 12px 28px;
            border-radius: 500px;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }
        .action-btn:hover {
            background-color: #1ed760;
            transform: scale(1.02);
        }
        .empty-btn {
            background-color: transparent;
            color: #b3b3b3;
            border: 1px solid #535353;
            padding: 10px 20px;
            border-radius: 500px;
            font-weight: bold;
            font-size: 13px;
            text-decoration: none;
        }
        .empty-btn:hover {
            color: #ffffff;
            border-color: #ffffff;
        }
        .remove-link {
            color: #e91429;
            text-decoration: none;
            font-size: 12px;
            font-weight: bold;
        }
        .remove-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body style="background: linear-gradient(180deg, #1f1f1f 0%, #121212 40%, #0a0a0a 100%); background-attachment: fixed; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0; min-height: 100vh;">

    <?php include 'menu.php'; ?>

    <div style="margin-left: 230px; padding: 32px; max-width: 1000px; box-sizing: border-box;">
        
        <h1 style="font-size: 32px; font-weight: 900; margin-bottom: 24px;">Il tuo Carrello</h1>

        <?php if (!empty($carrello)): ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Prodotto</th>
                        <th>Album di Riferimento</th>
                        <th>Prezzo Unitario</th>
                        <th>Quantità</th>
                        <th>Totale</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($carrello as $item): 
                        $subtotale = $item['prezzo'] * $item['quantita'];
                        $totale_generale += $subtotale;
                    ?>
                        <tr>
                            <td>
                                <strong style="color: #ffffff;"><?php echo htmlspecialchars($item['tipo_prodotto']); ?></strong>
                            </td>
                            <td style="color: #b3b3b3;"><?php echo htmlspecialchars($item['album_titolo']); ?></td>
                            <td>€ <?php echo number_format($item['prezzo'], 2, ',', '.'); ?></td>
                            <td><?php echo (int)$item['quantita']; ?></td>
                            <td style="font-weight: bold; color: #1db954;">€ <?php echo number_format($subtotale, 2, ',', '.'); ?></td>
                            <td>
                                <a href="carrello.php?azione=rimuovi&id=<?php echo $item['id']; ?>" class="remove-link">Rimuovi</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 30px; background-color: #181818; padding: 24px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.05);">
                <div>
                    <a href="carrello.php?azione=svuota" class="empty-btn">Svuota Carrello</a>
                    <a href="discografia.php" style="color: #1db954; text-decoration: none; font-size: 13px; font-weight: bold; margin-left: 20px;">Continua lo shopping</a>
                </div>
                <div style="text-align: right;">
                    <p style="margin: 0 0 12px 0; font-size: 16px; color: #b3b3b3;">Totale Ordine: <strong style="font-size: 24px; color: #ffffff; margin-left: 8px;">€ <?php echo number_format($totale_generale, 2, ',', '.'); ?></strong></p>
                    <a href="checkout.php" class="action-btn">Procedi all'Ordine</a>
                </div>
            </div>

        <?php else: ?>
            <div style="background-color: #181818; padding: 40px; border-radius: 8px; text-align: center; border: 1px solid rgba(255,255,255,0.05);">
                <p style="color: #b3b3b3; font-size: 16px; margin-bottom: 20px;">Il tuo carrello è attualmente vuoto.</p>
                <a href="discografia.php" class="action-btn">Vai alla Discografia & Store</a>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>
<?php $conn->close(); ?>