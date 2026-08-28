# LWEB-Homework 2: Piattaforma Musicale (Spotify Style) in PHP e MySQL

Questo homework consiste nell'evoluzione del precedente progetto statico in un'applicazione web dinamica basata su PHP e MySQL. Mantenendo l'interfaccia utente in stile Spotify, la piattaforma ora gestisce un database musicale completo con artisti, album, brani, eventi live e un sistema di privilegi tramite autenticazione.

## Componenti del Gruppo e Repository
* Valerio Ottani 
* Giovanni La Penna
* Indirizzi repository GitHub:
  - https://github.com/valerioottani/LWEB-HOMEWORK1.git
  - https://github.com/Giogiogabbana/LWEB-HOMEWORK1.git

## Architettura Tecnologica e Tecniche Utilizzate
L'applicazione è stata sviluppata implementando le seguenti tecnologie e metodologie:
* Interazione PHP-MySQL: Utilizzo dell'estensione MySQLi per connettere l'applicativo al database, eseguendo query dinamiche (SELECT, INSERT, DELETE) per visualizzare e manipolare le entità musicali.
* Modularità: Il codice è suddiviso in file riutilizzabili per facilitarne la manutenzione. Il file `dati_generali.php` centralizza le costanti di connessione al database, venendo poi richiamato da `connection.php` e `install.php`. Anche componenti visive come la sidebar sono separate (`menu.php`).
* Autenticazione e Sicurezza: Implementazione di un sistema di login (`login.php`) e logout (`logout.php`) che sfrutta le variabili di sessione (`$_SESSION`) per tracciare lo stato dell'utente. Le password degli utenti sono protette nel database tramite la funzione `password_hash()`.
* Gestione dei Ruoli: La visualizzazione e i permessi cambiano dinamicamente in base all'utente loggato. I form di inserimento ed eliminazione (di album, eventi e brani) compaiono esclusivamente se l'utente ha il ruolo di "admin".

## Installazione e Configurazione Database
Il progetto include uno script di auto-configurazione per il server.
Eseguendo nel browser il file `install.php`, il sistema provvederà automaticamente a:
1. Creare il database denominato `ottani.valerio.PHP-MySQL`.
2. Generare le tabelle relazionali necessarie (`artisti`, `utenti`, `album`, `tracce`, `eventi`) complete di vincoli (FOREIGN KEY e ON DELETE CASCADE).
3. Popolare il database con dati di test realistici (8 artisti, discografie complete, date dei tour).
4. Creare gli account di default per testare l'applicazione.

Credenziali preconfigurate dallo script:
* Utente Amministratore (può modificare il DB): 
  - Username: admin
  - Password: adminpassword
* Utente Standard (sola lettura):
  - Username: utente
  - Password: utentepassword

## Organizzazione del Sito
L'homework è suddiviso nelle seguenti sezioni navigabili:
1. homepage.php: Pagina di benvenuto dinamica che saluta l'utente per nome e mostra un'anteprima delle stazioni radio, discografia e artisti.
2. artisti.php e artista.php: Elenco completo degli artisti e pagina di dettaglio del singolo artista con biografia e brani. Lato admin è possibile gestire l'aggiunta o l'eliminazione dei brani.
3. discografia.php: Catalogo globale degli album con barra di ricerca testuale. L'amministratore ha a disposizione un form per aggiungere nuovi album al database.
4. eventi.php: Tabella interattiva dei concerti e tour live. Se l'utente è un amministratore, può aggiungere o rimuovere le date dal calendario.
5. admin.php: Dashboard riservata all'amministratore per la gestione avanzata e il redirect alle funzioni del database.

---
Homework realizzato per l'esame di Linguaggi per il Web - Homework 2.