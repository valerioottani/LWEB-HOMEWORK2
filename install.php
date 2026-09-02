<?php
require_once 'dati_generali.php';

$conn = new mysqli(DB_SERVER, DB_USER, DB_PASS);

if ($conn->connect_error) {
    die("<p style='color:red;'>Connessione al server DBMS fallita: " . $conn->connect_error . "</p>");
}

// 1. Creazione e selezione Database
$conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
$conn->select_db(DB_NAME);

// 2. Reset tabelle (rispettando i vincoli di chiave esterna)
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("DROP TABLE IF EXISTS `acquisti_merch`");
$conn->query("DROP TABLE IF EXISTS `posti_evento`");
$conn->query("DROP TABLE IF EXISTS `merchandise_album`");
$conn->query("DROP TABLE IF EXISTS `messaggi_community`");
$conn->query("DROP TABLE IF EXISTS `playlist_tracce`");
$conn->query("DROP TABLE IF EXISTS `playlist`");
$conn->query("DROP TABLE IF EXISTS `stazioni_radio`");
$conn->query("DROP TABLE IF EXISTS `articoli_blog`");
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
    `ruolo` VARCHAR(20) NOT NULL DEFAULT 'user',
    `nome_completo` VARCHAR(255) DEFAULT '',
    `eta` INT DEFAULT NULL,
    `data_nascita` DATE DEFAULT NULL,
    `indirizzo` VARCHAR(255) DEFAULT '',
    `numero_carta` VARCHAR(25) DEFAULT '',
    `scadenza_carta` VARCHAR(10) DEFAULT '',
    `cvv` VARCHAR(5) DEFAULT '',
    `saldo_buoni` DECIMAL(10,2) DEFAULT 0.00
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

$conn->query("CREATE TABLE `merchandise_album` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `album_id` INT NOT NULL,
    `tipo_prodotto` VARCHAR(50) NOT NULL,
    `prezzo` DECIMAL(6,2) NOT NULL,
    `immagine_prodotto` VARCHAR(100) NOT NULL,
    `disponibile` TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (`album_id`) REFERENCES `" . TAB_ALBUMS . "`(`id`) ON DELETE CASCADE
)");

$conn->query("CREATE TABLE `acquisti_merch` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL,
    `merchandise_id` INT NOT NULL,
    `quantita` INT NOT NULL DEFAULT 1,
    `data_acquisto` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`merchandise_id`) REFERENCES `merchandise_album`(`id`) ON DELETE CASCADE
)");

$conn->query("CREATE TABLE `" . TAB_EVENTS . "` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `giorno` INT NOT NULL,
    `mese` VARCHAR(10) NOT NULL,
    `titolo` VARCHAR(150) NOT NULL,
    `luogo` VARCHAR(150) NOT NULL,
    `link_biglietti` VARCHAR(255) NOT NULL
)");

$conn->query("CREATE TABLE `playlist` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `titolo` VARCHAR(150) NOT NULL,
    `descrizione` TEXT NOT NULL,
    `immagine` VARCHAR(100) NOT NULL,
    `sfondo` VARCHAR(100) NOT NULL,
    `tipo` VARCHAR(20) NOT NULL DEFAULT 'playlist'
)");

$conn->query("CREATE TABLE `stazioni_radio` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(150) NOT NULL,
    `artisti` TEXT NOT NULL,
    `immagine` VARCHAR(100) NOT NULL DEFAULT 'album.png',
    `sfondo_css` VARCHAR(255) NOT NULL DEFAULT 'linear-gradient(135deg, #1e3264 0%, #000000 100%)'
)");

$conn->query("CREATE TABLE `playlist_tracce` (
    `playlist_id` INT NOT NULL,
    `traccia_id` INT NOT NULL,
    PRIMARY KEY (`playlist_id`, `traccia_id`),
    FOREIGN KEY (`playlist_id`) REFERENCES `playlist`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`traccia_id`) REFERENCES `" . TAB_TRACKS . "`(`id`) ON DELETE CASCADE
)");

$conn->query("CREATE TABLE `messaggi_community` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `artista_gruppo` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL,
    `messaggio` TEXT NOT NULL,
    `data_invio` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE `posti_evento` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `evento_id` INT NOT NULL,
    `settore` VARCHAR(50) NOT NULL,
    `numero_posto` VARCHAR(20) NOT NULL,
    `prezzo` DECIMAL(6,2) NOT NULL,
    `occupato` TINYINT(1) NOT NULL DEFAULT 0,
    `username` VARCHAR(50) DEFAULT NULL,
    FOREIGN KEY (`evento_id`) REFERENCES `" . TAB_EVENTS . "`(`id`) ON DELETE CASCADE
)");

$conn->query("CREATE TABLE `articoli_blog` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `titolo` VARCHAR(255) NOT NULL,
    `contenuto` TEXT NOT NULL,
    `autore` VARCHAR(100) DEFAULT 'Redazione',
    `data` VARCHAR(50) DEFAULT 'Oggi'
)");


// 4. Popolamento Artisti Originali
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


// 5. Popolamento Album e Brani Originali
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
    'SixTeenReid' => [
        'album' => 'Raccolta Singoli', 'anno' => 2020, 'copertina' => 'carletto_sfondo.png',
        'tracce' => [
            ['TEMPO PERSO', '3:02', 'tempo_perso.png'],
            ['Piena Estate (feat. Luca Romeo)', '2:50', 'piena_estate.png'],
            ['Calmo', '2:21', 'calmo.png'],
            ['Neanche io', '2:33', 'neanche_io.png']
        ]
    ],
    'Luca Romeo' => [
        'album' => 'Raccolta Singoli', 'anno' => 2020, 'copertina' => 'luca_sfondo.png',
        'tracce' => [
            ['TEMPO PERSO', '3:02', 'tempo_perso.png'],
            ['Piena Estate (feat. SixTeenReid)', '2:50', 'piena_estate.png'],
            ['Strega', '3:27', 'strega.png'],
            ['Un\'altra Scusa', '2:50', 'altra_scusa.png'],
            ['Alice', '2:42', 'alice.png']
        ]
    ]
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


// 6. Popolamento Merchandise Originale
$merch_iniziale = [
    [1, 'Vinile 33 Giri (Collector Edition)', 29.90, 'vinile_luche.png', 1],
    [1, 'CD Audio Autografato', 18.00, 'cd_luche.png', 1],
    [1, 'T-Shirt Ufficiale Tour', 25.00, 'maglia_luche.png', 0],
    
    [2, 'Vinile 33 Giri Colorato', 32.00, 'vinile_geolier.png', 1],
    [2, 'CD Audio Standard', 16.50, 'cd_geolier.png', 1],
    [2, 'Felpa / Maglia Oversize', 45.00, 'maglia_geolier.png', 1],

    [3, 'Vinile 33 Giri', 30.00, 'vinile_marra.png', 1],
    [3, 'CD Audio', 17.00, 'cd_marra.png', 0],
    [3, 'T-Shirt King del Rap', 24.00, 'maglia_marra.png', 1],

    [4, 'Vinile Collector Sirio', 35.00, 'vinile_lazza.png', 1],
    [4, 'CD Audio Sirio', 16.00, 'cd_lazza.png', 1],
    [4, 'Maglia Ufficiale Lazza', 28.00, 'maglia_lazza.png', 1]
];

foreach ($merch_iniziale as $m) {
    $alb_id = (int)$m[0];
    $tipo = $conn->real_escape_string($m[1]);
    $prezzo = (float)$m[2];
    $img_prod = $conn->real_escape_string($m[3]);
    $disp = (int)$m[4];
    $conn->query("INSERT INTO `merchandise_album` (album_id, tipo_prodotto, prezzo, immagine_prodotto, disponibile) VALUES ($alb_id, '$tipo', $prezzo, '$img_prod', $disp)");
}


// 7. Popolamento Eventi Originali
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
    [27, 'SET', 'Napoli Urban Fest 2026', 'Palapartenope, Napoli', 'https://www.ticketone.it'],
    [15, 'OTT', 'Autunno Rap Arena Tour - Opening Night', 'Unipol Arena, Bologna', 'https://www.ticketone.it'],
    [28, 'OTT', 'Halloween Hip Hop Night feat. Sfera & Lazza', 'Alcatraz, Milano', 'https://www.ticketone.it'],
    [12, 'NOV', 'Winter Urban Fest - Special Edition', 'Nelson Mandela Forum, Firenze', 'https://www.ticketone.it'],
    [20, 'DIC', 'Christmas Rap Party & Showcase', 'Atlantico, Roma', 'https://www.ticketone.it'],
    [31, 'DIC', 'Capodanno Rap & Urban Countdown Live', 'Piazza del Plebiscito, Napoli', 'https://www.ticketone.it']
];

foreach ($eventi as $ev) {
    $g = (int)$ev[0];
    $m = $conn->real_escape_string($ev[1]);
    $t = $conn->real_escape_string($ev[2]);
    $l = $conn->real_escape_string($ev[3]);
    $link = $conn->real_escape_string($ev[4]);
    $conn->query("INSERT INTO `" . TAB_EVENTS . "` (giorno, mese, titolo, luogo, link_biglietti) VALUES ($g, '$m', '$t', '$l', '$link')");
}


// 8. Popolamento Posti a sedere (inizialmente senza alcun utente associato nello storico)
$settori_base = [
    ['Parterre Standing', 35.00],
    ['Tribuna VIP Numerata', 65.00],
    ['Primo Anello Laterale', 45.00]
];

$res_ev = $conn->query("SELECT id FROM `" . TAB_EVENTS . "`");
if ($res_ev) {
    while ($ev = $res_ev->fetch_assoc()) {
        $ev_id = (int)$ev['id'];
        foreach ($settori_base as $s) {
            $settore_nome = $s[0];
            $prezzo_base = $s[1];
            for ($i = 1; $i <= 4; $i++) {
                $num_posto = strtoupper(substr($settore_nome, 0, 3)) . '-' . $i;
                $occupato = ($i % 3 == 0) ? 1 : 0;
                
                // username viene impostato a NULL per evitare che appaiano biglietti pre-assegnati nello storico
                $conn->query("INSERT INTO `posti_evento` (evento_id, settore, numero_posto, prezzo, occupato, username) VALUES ($ev_id, '$settore_nome', '$num_posto', $prezzo_base, $occupato, NULL)");
            }
        }
    }
}


// 9. Popolamento Playlist, Stazioni Radio e Classifiche Originali
$playlist_dati = [
    [1, 'Rap d\'Autore & Poesia', 'I pezzi più introspettivi e profondi della scena urban.', 'primo_piano.png', 'linear-gradient(135deg, #1e3264 0%, #000000 100%)', 'playlist'],
    [2, 'Stadi in Fiamme', 'Gli inni generazionali che hanno riempito i palazzetti e gli stadi.', 'marra.jpg', 'linear-gradient(135deg, #51356b 0%, #000000 100%)', 'playlist'],
    [3, 'Club & Freestyle Vibes', 'Le tracce più ritmate e i feat che hanno spaccato le classifiche.', 'lazza.jpg', 'linear-gradient(135deg, #148a08 0%, #000000 100%)', 'playlist'],
    [101, 'Top 50 • Italia', 'I brani più ascoltati oggi in Italia.', 'cenere.png', 'linear-gradient(135deg, #1e3264 0%, #000000 100%)', 'classifica'],
    [102, 'Global Top 50', 'I brani virali e più riprodotti al mondo.', 'marra.jpg', 'linear-gradient(135deg, #51356b 0%, #000000 100%)', 'classifica'],
    [103, 'Italia Viral 50', 'Le tracce che stanno spopolando di più in Italia.', 'geolier.jpg', 'linear-gradient(135deg, #8d67ab 0%, #000000 100%)', 'classifica'],
    [104, 'Top Rap Italia', 'Il meglio della scena urban e rap del momento.', 'lazza.jpg', 'linear-gradient(135deg, #148a08 0%, #000000 100%)', 'classifica']
];

foreach ($playlist_dati as $pl) {
    $pid = (int)$pl[0];
    $ptit = $conn->real_escape_string($pl[1]);
    $pdesc = $conn->real_escape_string($pl[2]);
    $pimg = $conn->real_escape_string($pl[3]);
    $psfond = $conn->real_escape_string($pl[4]);
    $ptipo = $conn->real_escape_string($pl[5]);
    $conn->query("INSERT INTO `playlist` (id, titolo, descrizione, immagine, sfondo, tipo) VALUES ($pid, '$ptit', '$pdesc', '$pimg', '$psfond', '$ptipo')");
}

$stazioni_radio_dati = [
    ['Luchè Radio', 'Con Geolier, Guè, Marracash e molti altri', 'primo_piano.png', 'linear-gradient(135deg, #1e3264 0%, #000000 100%)'],
    ['Geolier Radio', 'Con Luchè, Lazza, Sfera Ebbasta e altri', 'geolier.jpg', 'linear-gradient(135deg, #8d67ab 0%, #000000 100%)'],
    ['Marracash Radio', 'Con Guè, Fabri Fibra, Salmo e altri', 'marra.jpg', 'linear-gradient(135deg, #e8115b 0%, #000000 100%)'],
    ['Lazza Radio', 'Con Sfera Ebbasta, Shiva, Tedua e altri', 'lazza.jpg', 'linear-gradient(135deg, #148a08 0%, #000000 100%)'],
    ['Guè Radio', 'Con Club Dogo, Marracash, Noyz Narcos e altri', 'gue.jpg', 'linear-gradient(135deg, #e91429 0%, #000000 100%)']
];

foreach ($stazioni_radio_dati as $st) {
    $snome = $conn->real_escape_string($st[0]);
    $sartisti = $conn->real_escape_string($st[1]);
    $simg = $conn->real_escape_string($st[2]);
    $ssfondo = $conn->real_escape_string($st[3]);
    $conn->query("INSERT INTO `stazioni_radio` (nome, artisti, immagine, sfondo_css) VALUES ('$snome', '$sartisti', '$simg', '$ssfondo')");
}

$playlist_tracce_dati = [
    [1, 1], [1, 13], [1, 19],
    [2, 7], [2, 9], [2, 11], [2, 17],
    [3, 2], [3, 8], [3, 12], [3, 23],
    [101, 17], [101, 9], [101, 1], [101, 7], [101, 13],
    [102, 11], [102, 17], [102, 2], [102, 8],
    [103, 7], [103, 19], [103, 23], [103, 1],
    [104, 1], [104, 11], [104, 17], [104, 13], [104, 7]
];

foreach ($playlist_tracce_dati as $pt) {
    $pl_id = (int)$pt[0];
    $tr_id = (int)$pt[1];
    $conn->query("INSERT INTO `playlist_tracce` (playlist_id, traccia_id) VALUES ($pl_id, $tr_id)");
}


// 10. Popolamento Messaggi di Default per la Community
$messaggi_default = [
    ['Luchè', 'admin', 'Benvenuti nello spazio ufficiale della community! Condividete qui le vostre impressioni sui dischi.'],
    ['Geolier', 'user', 'Il nuovo album è semplicemente pazzesco, non smetto di ascoltarlo.'],
    ['Marracash', 'admin', 'Il King del Rap non delude mai. Qual è la vostra traccia preferita di Persona?'],
    ['Lazza', 'user', 'Sirio è un capolavoro assoluto dall inizio alla fine!'],
    ['Guè', 'user', 'Madreperla ha un flow e dei beat che spaccano troppo.'],
    ['Sfera Ebbasta', 'admin', 'Preparatevi perché quest anno ci saranno grandissime novità live!'],
    ['SixTeenReid', 'user', 'Bella raga, chi viene al prossimo concerto in tour?'],
    ['Luca Romeo', 'user', 'Sto provando a rifare gli accordi di chitarra, pezzi incredibili.']
];

foreach ($messaggi_default as $msg) {
    $art_gruppo = $conn->real_escape_string($msg[0]);
    $usr = $conn->real_escape_string($msg[1]);
    $testo = $conn->real_escape_string($msg[2]);
    $conn->query("INSERT INTO `messaggi_community` (artista_gruppo, username, messaggio) VALUES ('$art_gruppo', '$usr', '$testo')");
}


// 11. Popolamento Articoli per il Blog
// 11. Popolamento Articoli per il Blog
$articoli_iniziali = [
    [
        "Il ritorno di Luchè: anatomia di un successo che ridefinisce il rap d'autore", 
        "C'è un momento preciso nella carriera di un artista in cui la ricerca della maturità artistica incontra il favore incondizionato del pubblico. Luchè ha saputo incarnare questa evoluzione in modo unico, portando la scrittura urban a livelli di profondità letteraria inaspettati. Il disco non è solo una sequenza di hit, ma un vero e proprio viaggio introspettivo nei quartieri di Napoli, tra le ombre del passato e la luce di una consacrazione ampiamente meritata. Le produzioni musicali, curate nei minimi dettagli, fondono sonorità internazionali con campionamenti viscerali, creando un tappeto sonoro perfetto per le barre taglienti del rapper napoletano. Un'opera che conferma come il rap italiano possa essere allo stesso tempo popolare e culturalmente elevato.", 
        "Redazione Urban", 
        "25 Agosto 2026"
    ],
    [
        "La nuova età dell'oro del Rap Italiano", 
        "Se qualcuno avesse detto dieci anni fa che il rap avrebbe dominato in modo così assoluto le classifiche e i palinsesti culturali italiani, probabilmente sarebbe stato preso per visionario. Eppure oggi ci troviamo immersi in una vera e propria età dell'oro, dove il genere urbano si è ramificato in mille sottogeneri, accogliendo contaminazioni pop, elettroniche e cantautorali. Gli artisti di oggi non sono più semplici performer, ma veri e propri produttori di tendenze, stilisti e imprenditori di sé stessi. Questa trasformazione ha permesso alla scena di sdoganarsi definitivamente, entrando nei teatri più prestigiosi e conquistando il rispetto della critica tradizionalista, senza però perdere quell'autenticità e quella fame di rivalsa che ne costituiscono l'essenza originaria.", 
        "Pincopallino S.", 
        "24 Agosto 2026"
    ],
    [
        "Dietro le Quinte del Tour", 
        "Portare in giro per l’Italia un tour di grandi dimensioni richiede una macchina organizzativa complessa, fatta di mesi di prove, sopralluoghi tecnici e una dedizione totale da parte di decine di professionisti. Dietro le due ore di spettacolo mozzafiato che il pubblico vive sotto il palco, si nasconde un lavoro certosino che comincia all'alba con il montaggio delle imponenti strutture scenografiche, dei giochi di luce e dei sistemi audio di ultima generazione. Abbiamo seguito la crew durante le prove generali: la tensione si mescola all'entusiasmo, e ogni singolo dettaglio viene calibrato al millimetro per garantire un'esperienza immersiva e indimenticabile per i fan. È la magia della musica live, che torna a battere forte nei palazzetti e negli stadi di tutto il paese.", 
        "Redazione Live", 
        "20 Agosto 2026"
    ],
    [
        "Il Ritorno del Vinile", 
        "Nell'era dello streaming on-demand e della fruizione musicale liquida e veloce, il mercato del vinile continua a registrare una crescita esponenziale, quasi in controtendenza rispetto alla digitalizzazione totale. Ma qual è il segreto di questo successo intramontabile? Per gli appassionati, il 33 giri rappresenta molto più di un semplice supporto audio: è un oggetto d'arte, un rituale tangibile che restituisce centralità all'ascolto consapevole dell'album nella sua interezza, dalla prima all'ultima traccia. Le copertine curate nei minimi dettagli, il calore inconfondibile della puntina che scivola sul solco e il piacere del collezionismo spingono sia i nostalgici di un tempo sia le nuove generazioni ad affollare i negozi di dischi, consacrando il vinile come il re indiscusso del merchandising musicale di lusso.", 
        "Marco V.", 
        "15 Agosto 2026"
    ]
];

foreach ($articoli_iniziali as $art) {
    $t = $conn->real_escape_string($art[0]);
    $c = $conn->real_escape_string($art[1]);
    $au = $conn->real_escape_string($art[2]);
    $d = $conn->real_escape_string($art[3]);
    $conn->query("INSERT INTO `articoli_blog` (titolo, contenuto, autore, data) VALUES ('$t', '$c', '$au', '$d')");
}


// 12. Popolamento Account e Storico Acquisti di Test
$pass_admin = password_hash('adminpassword', PASSWORD_DEFAULT);
$pass_user = password_hash('utentepassword', PASSWORD_DEFAULT);
$pass_other = password_hash('password123', PASSWORD_DEFAULT);

$conn->query("INSERT INTO `" . TAB_USERS . "` (username, password, ruolo, nome_completo, eta, data_nascita, indirizzo, numero_carta, scadenza_carta, cvv, saldo_buoni) VALUES 
    ('admin', '$pass_admin', 'admin', 'Valerio Ottani', 24, '2002-04-18', 'Via Tiburtina 212, Roma', '4532 1890 5674 1234', '12/28', '123', 0.00), 
    ('utente', '$pass_user', 'user', 'Martina Ferri', 23, '2003-09-14', 'Via Monte Napoleone 8, Milano', '5412 7500 1234 8901', '09/27', '456', 0.00),
    ('federico_esposito', '$pass_other', 'user', 'Federico Esposito', 26, '2000-11-03', 'Corso Umberto I 45, Napoli', '', '', '', 0.00),
    ('alessia_monti', '$pass_other', 'user', 'Alessia Monti', 22, '2004-02-27', 'Via Mazzini 15, Bologna', '', '', '', 0.00),
    ('lorenzo_santoro', '$pass_other', 'user', 'Lorenzo Santoro', 27, '1999-06-19', 'Via Etnea 102, Catania', '', '', '', 0.00)");

$acquisti_test = [
    ['utente', 1, 1],
    ['utente', 5, 1],
    ['alessia_monti', 4, 1],
    ['federico_esposito', 10, 1]
];

foreach ($acquisti_test as $acq) {
    $usr = $conn->real_escape_string($acq[0]);
    $m_id = (int)$acq[1];
    $qta = (int)$acq[2];
    $conn->query("INSERT INTO `acquisti_merch` (username, merchandise_id, quantita) VALUES ('$usr', $m_id, $qta)");
}

$conn->close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Installazione Completata - Spotify</title>
</head>
<body style="background-color: #121212; color: #ffffff; font-family: Arial, sans-serif; padding: 40px; text-align: center;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #181818; padding: 32px; border-radius: 8px;">
        <h1 style="color: #1db954; font-size: 24px; margin-bottom: 16px;">Installazione Completata con Successo!</h1>
        <p style="color: #b3b3b3; font-size: 14px; line-height: 1.6;"></p>
        <p style="margin-top: 24px;"><a href="login.php" style="background-color: #1db954; color: #ffffff; padding: 12px 28px; border-radius: 500px; text-decoration: none; font-weight: bold; font-size: 13px;">VAI AL LOGIN</a></p>
    </div>
</body>
</html>
