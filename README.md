# LWEB-Homework 2: Piattaforma Musicale (Spotify Style) in PHP e MySQL

Questo homework consiste nell'evoluzione del precedente progetto statico in un'applicazione web dinamica basata su PHP e MySQL. Mantenendo l'interfaccia utente in stile Spotify, la piattaforma ora gestisce un database musicale completo con artisti, album, brani, eventi live, merchandise e un sistema avanzato di e-commerce, portafoglio, storico ordini e privilegi tramite autenticazione.

## Componenti del Gruppo e Repository
* Valerio Ottani 
* Giovanni La Penna
* Indirizzi repository GitHub:
  - https://github.com/valerioottani/LWEB-HOMEWORK2.git
  - https://github.com/Giogiogabbana/LWEB-HOMEWORK2.git

## Architettura Tecnologica e Tecniche Utilizzate
L'applicazione è stata sviluppata implementando le seguenti tecnologie e metodologie:
* **Interazione PHP-MySQL:** Utilizzo dell'estensione MySQLi per connettere l'applicativo al database, eseguendo query dinamiche (SELECT, INSERT, UPDATE, DELETE) per visualizzare e manipolare le entità musicali e commerciali.
* **Modularità:** Il codice è suddiviso in file riutilizzabili per facilitarne la manutenzione. Il file `dati_generali.php` centralizza le costanti di connessione al database, venendo poi richiamato da `connection.php` e `install.php`. Anche componenti visive come la sidebar sono separate (`menu.php`).
* **Autenticazione e Sicurezza:** Implementazione di un sistema di login (`login.php`), registrazione (`register.php`) e logout (`logout.php`) che sfrutta le variabili di sessione (`$_SESSION`) per tracciare lo stato dell'utente. Le password degli utenti sono protette nel database tramite la funzione `password_hash()`.
* **Gestione dei Ruoli e Profilo:** La visualizzazione e i permessi cambiano dinamicamente in base all'utente loggato. I form di inserimento, modifica ed eliminazione compaiono esclusivamente se l'utente ha il ruolo di "admin". Gli utenti standard dispongono di un'area profilo (`profilo.php`) per la gestione dei dati personali, dei metodi di pagamento protetti da PIN/CVV e della ricarica dei buoni.

## Installazione e Configurazione Database
Il progetto include uno script di auto-configurazione per il server. Eseguendo nel browser il file `install.php`, il sistema provvederà automaticamente a:
1. Creare il database denominato `ottani.valerio.PHP-MySQL`, `giovanni.lapenna.PHP-MySql`.
2. Generare le tabelle relazionali necessarie complete di vincoli (`FOREIGN KEY` e `ON DELETE CASCADE`) e campi di associazione nominale per i posti degli eventi.
3. Popolare il database con dati di test realistici (artisti, discografie complete, date dei tour, articoli editoriali e uno storico pulito per gli utenti).
4. Creare gli account di default per testare l'applicazione.

### Credenziali preconfigurate dallo script:
* **Utente Amministratore:** 
  - Username: `admin`
  - Password: `adminpassword`
* **Utente Standard:**
  - Username: `utente`
  - Password: `utentepassword`
* **CVV carta credito Utente:**
  - Cvv: `456`
* **CVV carta credito Admin:**
  - Cvv: `123`  

## Organizzazione del Sito e Sezioni Principali
L'homework è suddiviso nelle seguenti sezioni navigabili:
1. **homepage.php**: Pagina di benvenuto dinamica che saluta l'utente per nome e mostra un'anteprima delle stazioni radio, discografia e artisti.
2. **artisti.php e artista.php**: Elenco completo degli artisti e pagina di dettaglio del singolo artista con biografia e brani (con gestione admin per l'eliminazione dei brani).
3. **discografia.php**: Catalogo globale degli album con accesso diretto alle pubblicazioni ufficiali.
4. **gestione_discografia.php e gestione_utenti.php**: Pannelli amministrativi avanzati per il pieno controllo CRUD sugli album e sugli account degli utenti.
5. **carrello.php e checkout_merch.php**: Sistema di e-commerce integrato per la gestione del carrello dei prodotti di merchandise con verifica del PIN/CVV della carta salvata nel profilo.
6. **eventi.php e prenota_evento.php**: Calendario dei concerti live e pagina di prenotazione dei biglietti con card visive interattive (`standing.png`, `superiore.png`, `anello.png`), filtri dinamici in JavaScript e gestione real-time dei posti a sedere.
7. **profilo.php**: Area riservata all'utente per aggiornare le proprie informazioni anagrafiche, la carta di credito (con pulsante per mostrare/nascondere il CVV), cambiare la password e ricaricare il saldo buoni.
8. **storico_acquisti.php**: Sezione centralizzata accessibile dalla barra laterale che mostra l'elenco filtrato per utente di tutti gli articoli di merchandise acquistati (con ricevuta stampabile) e dei biglietti per gli eventi live prenotati.
9. **blog.php**: Sezione editoriale con notizie approfondite sul panorama musicale e rap italiano.

---
Homework realizzato per l'esame di Linguaggi per il Web - Homework 2.
