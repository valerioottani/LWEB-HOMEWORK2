<?php
require_once 'dati_generali.php';

$conn = new mysqli(DB_SERVER, DB_USER, DB_PASS);

if ($conn->connect_error) {
    die("<p style='color:red;'>Connessione al server DBMS fallita: " . $conn->connect_error . "</p>");
}

// 1. Creazione e selezione Database
$conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
$conn->select_db(DB_NAME);

// 2. Reset tabelle
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("DROP TABLE IF EXISTS `" . TAB_TRACKS . "`");
$conn->query("DROP TABLE IF EXISTS `" . TAB_ALBUMS . "`");
$conn->query("DROP TABLE IF EXISTS `" . TAB_EVENTS . "`");
$conn->query("DROP TABLE IF EXISTS `" . TAB_ARTISTS . "`");
$conn->query("DROP TABLE IF EXISTS `" . TAB_USERS . "`");
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// 3. Creazione Tabelle
$conn->query("CREATE TABLE `" . TAB_ARTISTS . "` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL UNIQUE,
    `biografia` TEXT NOT NULL,
    `immagine` VARCHAR(100) NOT NULL
)");

$conn->query("CREATE TABLE `" . TAB_USERS . "` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `ruolo` VARCHAR(20) NOT NULL DEFAULT 'user'
)");

$conn->query("CREATE TABLE `" . TAB_ALBUMS . "` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `artista_id` INT NOT NULL,
    `titolo` VARCHAR(150) NOT NULL,
    `anno` INT NOT NULL,
    `copertina` VARCHAR(100) NOT NULL,
    FOREIGN KEY (`artista_id`) REFERENCES `" . TAB_ARTISTS . "`(`id`) ON DELETE CASCADE
)");

$conn->query("CREATE TABLE `" . TAB_TRACKS . "` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `album_id` INT NOT NULL,
    `titolo` VARCHAR(150) NOT NULL,
    `durata` VARCHAR(10) NOT NULL,
    `immagine_brano` VARCHAR(100) NOT NULL DEFAULT 'album.png',
    FOREIGN KEY (`album_id`) REFERENCES `" . TAB_ALBUMS . "`(`id`) ON DELETE CASCADE
)");

$conn->query("CREATE TABLE `" . TAB_EVENTS . "` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `giorno` INT NOT NULL,
    `mese` VARCHAR(10) NOT NULL,
    `titolo` VARCHAR(150) NOT NULL,
    `luogo` VARCHAR(150) NOT NULL,
    `link_biglietti` VARCHAR(255) NOT NULL
)");

// 4. Popolamento Artisti
$artisti = [
    ['Luchè', 'Luca Imprudente, in arte Luchè, ha ridefinito il rap e la musica urban partenopea e italiana.', 'primo_piano.png'],
    ['Geolier', 'Emanuele Palumbo, tra le voci più influenti e ascoltate della nuova scena rap napoletana.', 'geolier.jpg'],
    ['Marracash', 'Fabio Bartolo Rizzo, universalmente celebrato come il King del Rap italiano.', 'marra.jpg'],
    ['Lazza', 'Jacopo Lazzarini, pianista, rapper e produttore milanese ai vertici delle classifiche.', 'lazza.jpg'],
    ['Guè', 'Cosimo Fini, colonna portante della cultura hip hop italiana e membro storico dei Club Dogo.', 'gue.jpg'],
    ['Sfera Ebbasta', 'Gionata Boschetti, pioniere indiscusso della trap italiana e artista multiplatino.', 'sfera.jpg'],
    ['SixTeenReid', 'Giovane talento e beatmaker emergente con sonorità innovative nel panorama urban.', 'sixteenreid.jpg'],
    ['Luca Romeo', 'Cantautore e musicista con uno stile originale tra melodie pop e urban contemporaneo.', 'romeo.jpg']
];

foreach ($artisti as $a) {
    $n = $conn->real_escape_string($a[0]);
    $b = $conn->real_escape_string($a[1]);
    $img = $conn->real_escape_string($a[2]);
    $conn->query("INSERT INTO `" . TAB_ARTISTS . "` (nome, biografia, immagine) VALUES ('$n', '$b', '$img')");
}

// 5. Popolamento Album e Brani con immagini veritiere dalla cartella img/
$dati_brani = [
    'Luchè' => [
        'album' => 'Dove volano le aquile', 'anno' => 2022, 'copertina' => 'Dove_volano_le_aquile.png',
        'tracce' => [
            ['DVI', '2:45', 'Dove_volano_le_aquile.png'],
            ['Le Pietre Non Volano (feat. Marracash)', '3:32', 'marra.jpg'],
            ['Torna Da Me', '3:47', 'non_abbiamo_eta.png'],
            ['Potere', '3:12', 'non_abbiamo_eta.png'],
            ['O Primmo Ammore', '3:55', 'Malammore.png'],
            ['Che Dio Mi Benedica', '3:18', 'Malammore.png']
        ]
    ],
    'Geolier' => [
        'album' => 'Il Coraggio dei Bambini', 'anno' => 2023, 'copertina' => 'geolier.jpg',
        'tracce' => [
            ['Come Vuoi', '3:12', 'coraggio.png'],
            ['X CASO (feat. Sfera Ebbasta)', '3:34', 'coraggio.png'],
            ['I P ME, TU P TE', '3:07', 'ipme.png'],
            ['Chiagne (feat. Lazza)', '3:05', 'chiagne.png'],
            ['Money', '2:50', 'coraggio.png'],
            ['Maradona', '2:42', 'coraggio.png']
        ]
    ],
    'Marracash' => [
        'album' => 'Noi, Loro, Gli Altri', 'anno' => 2021, 'copertina' => 'marra.jpg',
        'tracce' => [
            ['Crazy Love', '3:12', 'noiloro.png'],
            ['Infinity Love (feat. Guè)', '3:00', 'noiloro.png'],
            ['Nemesi', '3:45', 'noiloro.png'],
            ['Bravi a Cadere', '3:18', 'persona.png'],
            ['Crudelia - I Nervi', '3:30', 'persona.png'],
            ['Dubbi', '3:52', 'persona.png']
        ]
    ],
    'Lazza' => [
        'album' => 'Sirio', 'anno' => 2022, 'copertina' => 'lazza.jpg',
        'tracce' => [
            ['Cenere', '3:28', 'cenere.png'],
            ['Piove (feat. Sfera Ebbasta)', '3:10', 'sirio.png'],
            ['Panico (feat. Takagi & Ketra)', '3:02', 'sirio.png'],
            ['Molotov', '3:11', 'sirio.png'],
            ['Uscito Di Galera', '2:55', 'sirio.png'],
            ['Senza Rumore', '3:35', 'sirio.png']
        ]
    ],
    'Guè' => [
        'album' => 'Madreperla', 'anno' => 2023, 'copertina' => 'gue.jpg',
        'tracce' => [
            ['Brivido (feat. Marracash)', '3:44', 'brivido.png'],
            ['Cookies N Cream (feat. Sfera & Anna)', '3:15', 'madreperla.png'],
            ['Mollami Pt. 2', '2:40', 'madreperla.png'],
            ['Prefissi', '3:02', 'madreperla.png'],
            ['Chico', '3:21', 'fini.png'],
            ['Salvador Dalì', '3:10', 'santeria.png']
        ]
    ],
    'Sfera Ebbasta' => [
        'album' => 'Famoso', 'anno' => 2020, 'copertina' => 'famoso.png',
        'tracce' => [
            ['Bottiglie Privè', '3:10', 'famoso.png'],
            ['Baby (feat. J Balvin)', '3:16', 'famoso.png'],
            ['Tik Tok (feat. Marracash & Guè)', '3:42', 'famoso.png'],
            ['Famoso', '2:58', 'famoso.png'],
            ['Bang Bang', '3:20', 'bang_bang.png'],
            ['Male', '3:05', 'famoso.png']
        ]
    ],
];

foreach ($dati_brani as $nome_art => $dati) {
    $res_art = $conn->query("SELECT id FROM `" . TAB_ARTISTS . "` WHERE nome = '" . $conn->real_escape_string($nome_art) . "'");
    if ($res_art && $row = $res_art->fetch_assoc()) {
        $art_id = $row['id'];
        $tit_alb = $conn->real_escape_string($dati['album']);
        $anno = (int)$dati['anno'];
        $cop = $conn->real_escape_string($dati['copertina']);

        $conn->query("INSERT INTO `" . TAB_ALBUMS . "` (artista_id, titolo, anno, copertina) VALUES ($art_id, '$tit_alb', $anno, '$cop')");
        $alb_id = $conn->insert_id;

        foreach ($dati['tracce'] as $tr) {
            $t_tit = $conn->real_escape_string($tr[0]);
            $t_dur = $conn->real_escape_string($tr[1]);
            $t_img = $conn->real_escape_string($tr[2]);
            $conn->query("INSERT INTO `" . TAB_TRACKS . "` (album_id, titolo, durata, immagine_brano) VALUES ($alb_id, '$t_tit', '$t_dur', '$t_img')");
        }
    }
}

// 6. Popolamento Eventi
$eventi = [
    [18, 'LUG', 'Luchè Summer Tour Live', 'Piazza del Plebiscito, Napoli', 'https://www.ticketone.it'],
    [24, 'LUG', 'Geolier Stadium Tour', 'Stadio Diego Armando Maradona, Napoli', 'https://www.ticketone.it'],
    [31, 'LUG', 'Marracash - Marrageddon Festival', 'Ippodromo La Maura, Milano', 'https://www.ticketone.it'],
    [8, 'AGO', 'Lazza - Locura Tour Live', 'Arena di Verona, Verona', 'https://www.ticketone.it'],
    [14, 'AGO', 'Sfera Ebbasta Summer Showcase', 'Red Valley Festival, Olbia', 'https://www.ticketone.it'],
    [24, 'AGO', 'Urban Vibes Showcase (Guè & Friends)', 'Ippodromo delle Capannelle, Roma', 'https://www.ticketone.it'],
    [5, 'SET', 'SixTeenReid Producer Night', 'Rock in Roma, Ippodromo delle Capannelle', 'https://www.ticketone.it'],
    [12, 'SET', 'Luca Romeo Acoustic & Live Band', 'Auditorium Parco della Musica, Roma', 'https://www.ticketone.it'],
    [19, 'SET', 'Milano Hip Hop All-Stars (Special Guests)', 'Mediolanum Forum, Assago (MI)', 'https://www.ticketone.it'],
    [27, 'SET', 'Napoli Urban Fest 2026', 'Palapartenope, Napoli', 'https://www.ticketone.it']
];

foreach ($eventi as $ev) {
    $g = (int)$ev[0];
    $m = $conn->real_escape_string($ev[1]);
    $t = $conn->real_escape_string($ev[2]);
    $l = $conn->real_escape_string($ev[3]);
    $link = $conn->real_escape_string($ev[4]);
    $conn->query("INSERT INTO `" . TAB_EVENTS . "` (giorno, mese, titolo, luogo, link_biglietti) VALUES ($g, '$m', '$t', '$l', '$link')");
}

// 7. Popolamento Utenti
$pass_admin = password_hash('adminpassword', PASSWORD_DEFAULT);
$pass_user = password_hash('utentepassword', PASSWORD_DEFAULT);
$conn->query("INSERT INTO `" . TAB_USERS . "` (username, password, ruolo) VALUES 
    ('admin', '$pass_admin', 'admin'), 
    ('utente', '$pass_user', 'user')");

$conn->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Installazione - Spotify</title>
</head>
<body style="background-color: #121212; color: #ffffff; font-family: Arial, sans-serif; padding: 40px; text-align: center;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #181818; padding: 32px; border-radius: 8px;">
        <h1 style="color: #1db954; font-size: 24px; margin-bottom: 16px;">Installazione Completata con Successo!</h1>
        <p style="color: #b3b3b3; font-size: 14px; line-height: 1.6;">Brani e immagini reali abbinate correttamente.</p>
        <p style="margin-top: 24px;"><a href="artisti.php" style="background-color: #1db954; color: #ffffff; padding: 12px 28px; border-radius: 500px; text-decoration: none; font-weight: bold; font-size: 13px;">VAI AD ARTISTI</a></p>
    </div>
</body>
</html>