# Piattaforma partecipativa (demo PHP + MySQL/SQLite)

Questa cartella contiene una versione dimostrativa della piattaforma civica descritta:
- pubblicazione di idee per quartiere/tema
- voti e commenti senza login obbligatorio
- candidatura automatica dopo 10 voti reali sulle proprie idee (o opt-in manuale)
- simulatore di attività che aggiunge voti/idee random per mantenere la pagina viva

## Avvio rapido con SQLite (demo)
1. Assicurati di avere PHP 8+ installato.
2. Avvia il server locale dalla cartella del progetto:
   ```bash
   php -S 0.0.0.0:8080 -t dapps/participatory-platform
   ```
3. Apri <http://localhost:8080> e inizia a proporre idee.

## Uso con MySQL
1. Crea un database e applica lo schema `schema.sql`.
2. Esporta variabili d'ambiente prima di avviare PHP:
   ```bash
   export PARTICIPATORY_DB_DRIVER=mysql
   export PARTICIPATORY_DB_HOST=localhost
   export PARTICIPATORY_DB_NAME=nome_database
   export PARTICIPATORY_DB_USER=utente
   export PARTICIPATORY_DB_PASS=segreto
   ```
3. Avvia il server come sopra. Le tabelle verranno create se mancanti.

## Endpoint principali (`api.php`)
- `GET api.php?action=ideas` – elenco idee con conteggio voti/commenti.
- `POST api.php?action=ideas` – crea idea (`title`, `desc`, `district`, `theme`, `author`, `author_email`, `candidate_opt_in`).
- `POST api.php?action=vote` – aggiunge voto a una idea (`idea_id`). Il token del votante è gestito via sessione.
- `POST api.php?action=comment` – commento rapido (`idea_id`, `author`, `body`).
- `GET api.php?action=candidates` – candidabili (email presente + 10 voti complessivi o opt-in).
- `GET api.php?action=activity` – statistiche e feed recente.
- `POST api.php?action=simulate` – genera in automatico voti o nuove idee (disattivabile con `PARTICIPATORY_ALLOW_SIMULATION=false`).

## Note
- Di default viene usato SQLite locale (`storage.sqlite`), utile per test immediati senza credenziali.
- Per produzione abilita MySQL e proteggi `api.php` con rate-limit/CORS secondo necessità.
- Il front-end chiama periodicamente `simulate` per mantenere il flusso di attività, come richiesto dal concept.
