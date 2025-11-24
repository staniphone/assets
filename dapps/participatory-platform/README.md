# Piattaforma partecipativa

Applicazione PHP + MySQL/SQLite pronta per la messa in produzione con front-end pubblico e backoffice di moderazione.

## Caratteristiche
- Invio proposte con quartiere, tema, candidatura opzionale
- Voti e commenti pubblici (1 voto per sessione)
- Stato idee: in revisione, pubblicata, archiviata
- Dashboard backoffice per pubblicare/archiviare, inserire idee, eliminare contenuti
- Statistiche live (idee pubblicate, voti, commenti, distretti attivi)
- Database MySQL per produzione, SQLite per sviluppo rapido

## Configurazione
1. Imposta il database (consigliato MySQL in produzione):
   ```bash
   export PARTICIPATORY_DB_DRIVER=mysql
   export PARTICIPATORY_DB_HOST=localhost
   export PARTICIPATORY_DB_NAME=nome_database
   export PARTICIPATORY_DB_USER=utente
   export PARTICIPATORY_DB_PASS=segreto
   ```
   Senza variabili usa SQLite locale (`storage.sqlite`).

2. Proteggi il backoffice definendo le credenziali:
   ```bash
   export PARTICIPATORY_ADMIN_USER=admin
   export PARTICIPATORY_ADMIN_PASS=password-sicura
   # in alternativa
   export PARTICIPATORY_ADMIN_TOKEN=token-lungo
   ```

3. (Opzionale) Abilita l'endpoint di simulazione voti solo se necessario per ambienti di staging:
   ```bash
   export PARTICIPATORY_ALLOW_SIMULATION=true
   ```
   Se la variabile non è presente la simulazione è disattivata.

4. Avvia il server PHP:
   ```bash
   php -S 0.0.0.0:8080 -t dapps/participatory-platform
   ```

## Routing
- `index.php` – landing pubblica con carosello idee, voti, commenti e form di proposta
- `api.php` – endpoint JSON (idee, voti, commenti, candidabili, attività, operazioni admin)
- `admin.php` – backoffice protetto con moderazione e inserimento manuale

## Schema
Lo schema MySQL/SQLite si trova in `schema.sql` e viene applicato automaticamente all'avvio. Le colonne `status` e `published_at` vengono aggiunte anche su basi dati esistenti.

## Note operative
- Le idee inviate dal pubblico entrano in stato `pending` e diventano pubbliche solo dopo pubblicazione dal backoffice.
- Il carosello si aggiorna periodicamente leggendo i dati reali, senza generare idee fittizie.
- Proteggi `api.php` con rate limiting/CORS secondo le policy del tuo hosting.
