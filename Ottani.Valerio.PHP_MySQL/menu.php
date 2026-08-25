<?php
$is_logged = isset($_SESSION['user']);
$ruolo = isset($_SESSION['ruolo']) ? $_SESSION['ruolo'] : '';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div style="width: 230px; height: 100%; position: fixed; top: 0; left: 0; background-color: #000000; padding: 24px; box-sizing: border-box; z-index: 100;">
    <!-- LOGO SPOTIFY INGRANDITO SENZA TESTO -->
    <div style="margin-bottom: 36px; text-align: left;">
        <a href="homepage.php" style="display: inline-block; text-decoration: none;">
            <img src="img/logo.png" alt="Spotify" style="width: 64px; height: 64px; object-fit: contain; display: block;" />
        </a>
    </div>

    <!-- VOCI DI MENU DINAMICHE -->
    <ul style="list-style: none; margin: 0; padding: 0;">
        <li style="margin-bottom: 18px;">
            <a href="homepage.php" style="color: <?php echo ($current_page === 'homepage.php') ? '#1db954' : '#b3b3b3'; ?>; text-decoration: none; font-size: 14px; font-weight: bold;">Home</a>
        </li>
        <li style="margin-bottom: 18px;">
            <a href="artisti.php" style="color: <?php echo ($current_page === 'artisti.php') ? '#1db954' : '#b3b3b3'; ?>; text-decoration: none; font-size: 14px; font-weight: bold;">Artisti</a>
        </li>
        <li style="margin-bottom: 18px;">
            <a href="discografia.php" style="color: <?php echo ($current_page === 'discografia.php') ? '#1db954' : '#b3b3b3'; ?>; text-decoration: none; font-size: 14px; font-weight: bold;">Discografia</a>
        </li>
        <li style="margin-bottom: 18px;">
            <a href="eventi.php" style="color: <?php echo ($current_page === 'eventi.php') ? '#1db954' : '#b3b3b3'; ?>; text-decoration: none; font-size: 14px; font-weight: bold;">Eventi Live</a>
        </li>
        <?php if ($is_logged && $ruolo === 'admin'): ?>
            <li style="margin-bottom: 18px;">
                <a href="admin.php" style="color: <?php echo ($current_page === 'admin.php') ? '#1db954' : '#b3b3b3'; ?>; text-decoration: none; font-size: 14px; font-weight: bold;">Gestione Artisti</a>
            </li>
        <?php endif; ?>
    </ul>

    <div style="position: absolute; bottom: 24px; left: 24px; right: 24px; padding-top: 20px; border-top: 1px solid #282828;">
        <?php if ($is_logged): ?>
            <p style="color: #b3b3b3; font-size: 11px; margin: 0 0 2px 0;">Profilo attivo:</p>
            <p style="color: #ffffff; font-weight: bold; font-size: 14px; margin: 0 0 2px 0;"><?php echo htmlspecialchars($_SESSION['user']); ?></p>
            <p style="color: #1db954; font-size: 11px; font-weight: bold; text-transform: uppercase; margin: 0 0 12px 0;">[<?php echo htmlspecialchars($ruolo); ?>]</p>
            <a href="logout.php" style="color: #e22134; font-size: 12px; font-weight: bold; text-decoration: none;">LOGOUT</a>
        <?php else: ?>
            <a href="login.php" style="background-color: #ffffff; color: #000000; padding: 8px 20px; border-radius: 500px; text-decoration: none; font-size: 12px; font-weight: bold; display: block; text-align: center;">ACCEDI</a>
        <?php endif; ?>
    </div>
</div>