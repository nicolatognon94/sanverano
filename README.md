# Sanverano — Full Stack Laravel + Vue 3
Test per il monitoraggio e il controllo di illuminazione pubblica sviluppata con Laravel + Vue 3
Il progetto integra dati provenienti da Vendor, analizza le telemetrie, le normalizza, le importa a db e le rende disponibili tramite una dashboard

## Requisiti
- PHP 8.4+
- Composer
- Node.js 22+
- npm
- MySQL/MariaDB
- Docker e Docker Compose
- Python 3.11+ per lo starter kit

## Installazione 
Clonare il progetto e installare le dipendenze

```bash
composer install
npm install
```

Configurare il file `.env` con i parametri del database.

Creare le tabelle:

```bash
php artisan migrate
```

Importare l'anagrafica dell'impianto:

```bash
php artisan app:import-plant
```

Avviare il frontend:

```bash
npm run dev
```


## Avvio dello starter kit

Dalla directory `starter-kit`:

```bash
docker compose up -d
```


## Ascolto MQTT

Il worker Laravel ascolta i topic dei vendor MQTT:

```bash
php artisan app:mqtt-listen
```

## Storico

Lo starter kit permette di generare lo storico deterministico di 30 giorni:

```bash
python3 simulator.py backfill --days 30 --out assets/history.ndjson
```

Lo storico generato non deve essere committato nel repository.

L'import dello storico Lot C viene eseguito tramite:

```bash
php artisan app:import-history ../starter-kit/assets/history.ndjson
```


## API principali

```text
GET  /api/dashboard
GET  /api/cabinets/{id}
GET  /api/cabinets/{id}/power
POST /api/commands
```
