<?php
session_start();
require_once 'connection.php';

$id_articolo = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// Database con articoli in stile editoriale / giornalistico
$notizie = [
    1 => [
        'titolo' => 'Il ritorno di Luchè: anatomia di un successo che ridefinisce il rap d\'autore',
        'autore' => 'Redazione Urban',
        'data' => '25 Agosto 2026',
        'img' => 'primo_piano.png',
        'paragrafi' => [
            'C\'è un momento preciso, nella storia recente della musica urban italiana, in cui le metriche di strada si fondono con la maturità autoriale, dando vita a opere che trascendono il semplice genere di appartenenza. È esattamente questo il territorio esplorato da Luca Imprudente, in arte Luchè, con il suo acclamato progetto discografico.',
            'Ascoltare i brani di "Dove volano le aquile" significa immergersi in un flusso di coscienza crudo ma incredibilmente raffinato. Le produzioni musicali, caratterizzate da tappeti sonori cinematografici e bpm calibrati al millimetro, fanno da perfetto contraltare a una scrittura che non cerca mai la scorciatoia commerciale. Ogni verso racconta di una Napoli vissuta non come cartolina, ma come metropoli globale, complessa, stratificata.',
            'Il riscontro del pubblico, suggellato da sold-out oceanici nei live club di tutta Italia, dimostra che la credibilità artistica non si costruisce a tavolino. Luchè ha saputo attendere i suoi tempi, coltivando un rapporto simbiotico con la propria fanbase e dimostrando che la coerenza, nel lungo periodo, è l\'unica vera valuta che conta nel mercato discografico odierno.'
        ]
    ],
    2 => [
        'titolo' => 'La nuova età dell\'oro del Rap Italiano: trionfi, stadi e la consacrazione dei live estivi',
        'autore' => 'Pincopallino S.',
        'data' => '24 Agosto 2026',
        'img' => 'marra.jpg',
        'paragrafi' => [
            'Se qualcuno avesse detto dieci anni fa che il rap italiano avrebbe riempito gli stadi di San Siro e del Maradona con la stessa naturalezza delle vecchie rockstar, probabilmente sarebbe stato preso per utopista. Eppure, l\'estate musicale del 2026 fotografa esattamente questa realtà: il genere urban è l\'asse portante dell\'intera industria culturale del Paese.',
            'Protagonisti assoluti di questa transizione culturale sono artisti del calibro di Marracash, Geolier e Lazza. Non si tratta più soltanto di concerti, ma di veri e propri eventi collettivi in cui la scaletta diventa un manifesto generazionale. Dalle barre introspettive e sociali di Marracash agli inni generazionali di Geolier, ogni live è un trionfo di scenografie imponenti e direzione artistica impeccabile.',
            'Mentre i festival estivi si avviano verso i titoli di coda, l\'attenzione si sposta già sui prossimi mesi. La sensazione palpabile è che non siamo di fronte a una semplice bolla passeggera, ma a un\'evoluzione strutturale: il rap italiano è cresciuto, ha studiato, ha trovato una voce propria e, soprattutto, ha conquistato un pubblico trasversale che non ha più alcuna intenzione di fare passi indietro.'
        ]
    ]
];

$articolo_corrente = isset($notizie[$id_articolo]) ? $notizie[$id_articolo] : $notizie[1];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Spotify Blog - <?php echo htmlspecialchars($articolo_corrente['titolo']); ?></title>
</head>
<body style="background: linear-gradient(180deg, #1f1f1f 0%, #121212 40%, #0a0a0a 100%); background-attachment: fixed; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; padding: 0; min-height: 100vh;">

    <?php include 'menu.php'; ?>

    <div style="margin-left: 230px; padding: 40px; display: flex; justify-content: center; box-sizing: border-box;">
        
        <!-- Contenitore Articolo Stile Giornale Centrato -->
        <div style="background-color: #181818; padding: 48px 56px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); max-width: 800px; width: 100%; box-sizing: border-box;">
            
            <!-- Intestazione Centrata -->
            <div style="text-align: center; margin-bottom: 32px;">
                <span style="font-size: 11px; font-weight: bold; color: #1db954; text-transform: uppercase; letter-spacing: 2px;">Spotify News & Editoriali</span>
                <h1 style="font-size: 36px; font-weight: 900; margin: 16px 0 20px 0; line-height: 1.25; color: #ffffff;"><?php echo htmlspecialchars($articolo_corrente['titolo']); ?></h1>
                
                <div style="display: flex; justify-content: center; align-items: center; gap: 12px; color: #b3b3b3; font-size: 13px;">
                    <span>Articolo a cura di <strong><?php echo htmlspecialchars($articolo_corrente['autore']); ?></strong></span>
                    <span>•</span>
                    <span><?php echo htmlspecialchars($articolo_corrente['data']); ?></span>
                </div>
            </div>

            <!-- Immagine Principale (centrata leggermente più in basso per inquadrare il viso) -->
            <div style="text-align: center; margin-bottom: 36px;">
                <img src="img/<?php echo htmlspecialchars($articolo_corrente['img']); ?>" alt="" style="width: 100%; max-height: 450px; object-fit: cover; object-position: 50% 25%; border-radius: 8px; box-shadow: 0 12px 32px rgba(0,0,0,0.7);" />
            </div>

            <!-- Corpo dell'Articolo (Discorsivo e curato) -->
            <div style="display: flex; flex-direction: column; gap: 24px; margin-bottom: 40px; text-align: justify;">
                <?php foreach ($articolo_corrente['paragrafi'] as $paragrafo): ?>
                    <p style="font-size: 16px; line-height: 1.85; color: #d8d8d8; margin: 0; text-indent: 20px;"><?php echo htmlspecialchars($paragrafo); ?></p>
                <?php endforeach; ?>
            </div>

            <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.1); margin-bottom: 28px;" />

            <!-- Link di ritorno -->
            <div style="text-align: left;">
                <a href="homepage.php" style="color: #1db954; text-decoration: none; font-size: 13px; font-weight: bold; cursor: pointer;">← Torna alla Homepage</a>
            </div>

        </div>

    </div>

</body>
</html>