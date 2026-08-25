
# LWEB - LINGUAGGI PER IL WEB
# RELAZIONE TECNICA: HOMEWORK 2 (PHP & MySQL)

# Nomi dei componenti del gruppo:
- Valerio Ottani (Matricola: 2139668)
-Giovanni La Penna (Matricola: 2147312)

# Indirizzi dei repository corrispondenti su GitHub:
- https://github.com/valerioottani/LWEB-HOMEWORK1.git


# 1. INTRODUZIONE ED OBIETTIVI DELL'ELABORATO
Questo homework rappresenta l'evoluzione in chiave dinamica della piattaforma web dedicata all'artista Luchè, originariamente sviluppata come sito statico in XHTML/CSS. 

L'obiettivo cardine del progetto è la transizione verso un'architettura a tre livelli (Three-Tier), integrando un database relazionale MySQL gestito lato server tramite PHP. L'intera applicazione è stata ingegnerizzata per rispettare in modo transigente i vincoli di validazione dello standard XHTML 1.0 Strict, garantendo al contempo una netta separazione tra la logica di business e i fogli di stile delegati alla presentazione visiva.


# 2. ARCHITETTURA TECNOLOGICA E MODULARIZZAZIONE
Per evitare la duplicazione del codice e consentire una manutenzione centralizzata, l'applicazione è stata scomposta in moduli riutilizzabili:

* dati_generali.php: È il nucleo di configurazione globale del sistema. Definisce le costanti necessarie alla connessione (DB_SERVER, DB_USER, DB_PASS) e mappa i nomi delle tabelle (TAB_ARTISTS, TAB_USERS, TAB_ALBUMS). Modificando unicamente questo file, l'applicazione può essere migrata istantaneamente su qualunque server o DBMS.
* connection.php: Inizializza l'oggetto di connessione sfruttando l'estensione nativa orientata agli oggetti MySQLi di PHP. Include un controllo d'errore bloccante (connect_error) con output controllato e imposta esplicitamente il set di caratteri su "utf8mb4" per prevenire anomalie di codifica con lettere accentate o caratteri speciali.
* menu.php: Contiene la struttura XHTML isolata della sidebar di navigazione sinistra. Viene incluso dinamicamente tramite istruzioni "include" all'interno dei file di view, ottimizzando l'indice di riusabilità del software.


# 3. STRUTTURA DEL DATABASE E PERSISTENZA DEI DATI
Il modello relazionale dei dati è strutturato per gestire le entità principali del dominio informativo musicale. In conformità con le specifiche tassative del docente, la base di dati è identificata univocamente dalla stringa: "valerio.ottani.PHP-MySQL".

Le tabelle create ed elaborate includono:
1. TAB_ARTISTS (artisti): Memorizza l'identificativo primario autoincrementale, il nome dell'artista (configurato come UNIQUE per prevenire ridondanze), la biografia testuale e il puntatore testuale all'immagine di profilo.
2. TAB_USERS (utenti): Dedicata alle credenziali del personale e degli utenti registrati. Contiene i campi id, username (UNIQUE) e password.
3. TAB_ALBUMS (album): Gestisce la discografia associata. Mappa una chiave esterna (FOREIGN KEY) verso la tabella artisti, configurata con la clausola "ON DELETE CASCADE". Questo assicura l'integrità referenziale del database: qualora un artista venisse rimosso, tutti i suoi album verrebbero eliminati in automatico dal DBMS in background.


# 4. SCRIPT DI INSTALLAZIONE AUTOMATICA (install.php)
Come esplicitamente richiesto dai vincoli d'esame, nella directory radice è presente lo script "install.php" delegato al deploy e popolamento automatizzato. I compiti eseguiti dallo script includono:
* Connessione iniziale al server DBMS senza pre-selezione del database.
* Esecuzione della query "CREATE DATABASE IF NOT EXISTS" impostando la codifica strutturale utf8mb4.
* Generazione sequenziale delle tabelle con i relativi vincoli di chiave e indici relazionali.
* Inserimento dei record di test originari dell'Homework 1 (Geolier, Lazza, Marracash, Sfera Ebbasta, Guè) tramite istruzioni "INSERT IGNORE" per prevenire violazioni dei vincoli di unicità in caso di esecuzioni multiple dello script.
* Gestione dinamica degli album: lo script non utilizza ID statici cablati nel codice per associare gli album di test a Geolier, bensì interroga preventivamente la tabella degli artisti con una query SELECT, estrae l'ID autoincrementale assegnatogli dal DBMS e lo utiliza per l'inserimento, scongiurando disallineamenti di chiavi esterne.


# 5. LOGICA APPLICATIVA E SICUREZZA LATO SERVER (PHP)
* Autenticazione e Sessioni: Il sistema implementa un'area riservata tramite l'avvio e il controllo delle variabili di sessione nativa ($_SESSION). Lo stato "admin" o "user standard" viene propagato in sicurezza tra le pagine solo a seguito di una corretta verifica delle credenziali nel form di login.
* Protezione dalle SQL Injection: Ogni dato proveniente da form esterni tramite metodo POST o passato in query-string tramite metodo GET (como l'ID del cantante nella pagina di dettaglio) viene preventivamente castato a tipo intero mediante "intval()" o sanificato tramite la funzione "real_escape_string()" prima di essere incorporato nelle stringhe di query SQL.
* Sicurezza delle Password: La password degli utenti memorizzata nel database non è salvata in chiaro, ma viene cifrata tramite l'algoritmo di hashing unidirezionale bcrypt mediante la funzione "password_hash()", verificata in fase di login con "password_verify()".


# 6. INTERFACCIA UTENTE ED OTTIMIZZAZZIONI CSS IN STILE SPOTIFY
Il frontend riproduce fedelmente l'esperienza d'uso e il visual system della piattaforma desktop di Spotify (Dark Mode), implementando logiche dinamiche avanzate:
* Indicatori di Navigazione Attiva: Nelle rispettive viste principali, blocchi CSS ad alta specificità intercettano l'URL corrente emesso dal modulo menu.php, illuminando la voce di menu selezionata con il colore verde ufficiale (#1db954) e assegnando un font-weight marcato per fornire un feedback visivo immediato di posizionamento all'utente.
* Controllo Rigido dei Cursori (No Manina): Per rispettare l'esatta semantica dell'interfaccia, è stato rimosso il comportamento predefinito dei cursori ipertestuali sulle card e sui widget puramente informativi che non reindirizzano a nuove pagine (come le card della discografia o i suggerimenti degli artisti). Mediante l'uso della proprietà "cursor: default !important" applicata a livello CSS, il cursore mantiene la forma di freccia standard.
* Logica di Sostituzione Icone: Nella griglia globale degli artisti ("artisti.php"), un controllo condizionale annidato nel ciclo di estrazione PHP intercetta il record nominale di Luchè e ne forza il rendering grafico associandolo al file locale "primo_piano.png", bypassando i tracciati standard memorizzati e garantendo la personalizzazione della Fan Page principale.
* Pannello Operazioni Admin su Singolo Artista: All'interno della vista di dettaglio "artista.php", lo stato di amministratore autenticato sblocca un doppio livello operativo: il form di inserimento asincrono per l'aggiunta di nuove tracce/album e i comandi di cancellazione rapida (pulsanti "Elimina") posizionati in linea accanto a ciascun brano della lista dei brani popolari.


# 7. CREDENZIALI PER LA VALUTAZIONE DEL DOCENTE (Profili di Accesso)
Per testare i meccanismi di validazione, i pannelli condizionali basati sui ruoli di sessione, l'aggiunta dinamica e i moduli di eliminazione dei record, utilizzare i seguenti profili di test configurati nel sistema:

A) UTENTE AMMINISTRATORE (Abilita i form verdi di aggiunta e i tasti di eliminazione)
* Username Admin: admin
* Password Admin: adminpassword

B) UTENTE STANDARD / CLIENTE (Navigazione classica come ospite o utente registrato, senza pannelli di gestione)
* Username Utente: utente
* Password Utente: utentepassword
*(In alternativa, l'utente comune può essere registrato liberamente e in tempo reale utilizzando il modulo di registrazione presente sul sito)*


---
Homework realizzato per l'esame di Linguaggi per il Web - Homework 2 (PHP-MySQL).
Sapienza Università di Roma - Facoltà di Ingegneria dell'Informazione, Informatica e Statistica.
Anno Accademico 2025/2026.