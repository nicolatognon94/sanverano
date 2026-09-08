# NOTES

## 1. Cosa ho fatto e cosa ho tagliato, con il perché

Ho innanzitutto creato il database, definendo le varie tabelle necessarie per la prova, con particolare attenzione alla gestione dei due lotti con caratteristiche differenti e, di conseguenza, alle relative tabelle per le telemetrie.

Ho scelto questa soluzione per evitare di avere molte colonne nulle e mantenere i dati il più possibile ordinati e coerenti con la tipologia di apparecchiatura.

Importando il file `plant.csv`, i dati vengono suddivisi tra le varie tabelle e, durante l'ascolto del broker MQTT, le telemetrie vengono importate nelle tabelle `cabinets_readings` e `point_readings`.

Una volta strutturato il database ho creato l'import di `plant.csv`, eseguito tramite un comando Artisan.

Dopo aver popolato il database ho iniziato a sviluppare la parte applicativa.

Ho quindi creato il listener MQTT che importa i dati all'interno del database. I dati vengono prima uniformati attraverso i parser dei vari vendor e successivamente salvati nel database, per poi essere rappresentati tramite una dashboard sviluppata in Vue 3 e Bootstrap.

Ho gestito il grafico all'interno dei singoli cabinet utilizzando Chart.js. Non sono riuscito a implementare un WebSocket principalmente per una questione di tempo. Ho comunque provato a impostare questa soluzione.

L'idea era configurare un WebSocket che rimanesse in ascolto degli aggiornamenti e permettesse di popolare il grafico in tempo reale. Per il momento ho optato per un polling periodico dell'endpoint.

Nell'ultima parte mi sono dedicato alla gestione dei comandi. Anche questa parte è stata sviluppata abbastanza velocemente per la stessa motivazione precedente, ma ho comunque implementato il flusso di creazione del comando, invio MQTT e riconciliazione dello stato tramite la telemetria ricevuta.

Per quanto riguarda la Parte 3, relativa all'analisi dei 30 giorni di storico, sono riuscito a implementare l'import dello storico nel database, ma non sono riuscito a completare l'analisi del consumo reale rispetto a quello atteso.

Le principali funzionalità non implementate sono quindi dovute principalmente al tempo a disposizione.

## 2. Normalizzazione vendor

Tutti i vendor MQTT passano da `VendorInterface`.

Ogni implementazione trasforma il payload proprietario in una struttura interna comune.

Il `VendorResolver` seleziona il parser corretto in base al topic.

Per aggiungere un terzo vendor è sufficiente creare una nuova implementazione dell'interfaccia e registrarla nel resolver, senza modificare il worker MQTT.

## 3. Idempotenza e riconciliazione

Le letture dello storico Lot C utilizzano come identificativo logico:

```text
cabinet_id + line + measured_at
```

Il database applica un vincolo `unique` su questa combinazione e l'import utilizza `updateOrCreate()`.

In questo modo lo stesso storico può essere reimportato senza creare duplicati.

Per i comandi viene utilizzato un `correlation_id` univoco. Il comando viene inizialmente salvato come `pending` e successivamente riconciliato con la telemetria ricevuta.

Nel caso del Lotto A, se lo stato riportato dalla telemetria corrisponde a quello richiesto, il comando viene considerato confermato.

## 4. Evitare overload del browser

La dashboard non carica continuamente lo storico completo.

Il grafico del dettaglio viene aggiornato tramite polling periodico dell'endpoint dedicato alla potenza.

Ho optato per questa soluzione principalmente per avere un aggiornamento automatico funzionante in tempi compatibili con la prova, anche se una soluzione WebSocket sarebbe stata più adatta per un aggiornamento realmente realtime.

## 5. Parte 3

Non sono riuscito a completare l'analisi dei 30 giorni.

Ho comunque implementato l'import dello storico del Lotto C nel database, mantenendo il processo in streaming per evitare di caricare l'intero file in memoria.

Rimane da completare il calcolo del consumo atteso, il confronto con il consumo reale e la classificazione delle anomalie.

## 6. Prima di arrivare a 200k punti

Prima di portare una soluzione di questo tipo in produzione con circa 200.000 punti prenderei in considerazione:

- ottimizzazione degli indici;
- utilizzo di Redis e della cache dove necessario;
- batch insert/upsert;
- evitare, dove possibile, di caricare grandi quantità di dati tramite Collection Laravel e preferire query più efficienti o aggregazioni direttamente sul database;
- introdurre una coda per l'import e l'elaborazione delle telemetrie, in modo che eventuali errori non blocchino l'intera procedura;
- gestire correttamente il numero di worker/job concorrenti per evitare di spostare semplicemente il problema dal processo principale alla coda;
- introdurre sistemi di monitoraggio e logging più strutturati.

## 7. Utilizzo dell'AI

Ho utilizzato l'AI come supporto durante lo sviluppo principalmente per:

- comprendere meglio il contesto della prova e ragionare sull'architettura;
- confrontare diverse soluzioni architetturali;
- fare debugging;
- verificare le scelte implementative;
- ricevere supporto nella definizione dei parser e dei servizi;
- revisionare la documentazione.

Il codice è stato sviluppato, modificato e verificato direttamente durante la realizzazione della prova.

## 8. Tempo

Onestamente ho impiegato un po' di tempo iniziale per comprendere il contesto della prova e le diverse modalità di comunicazione dei dispositivi.

Come tempo effettivo di sviluppo stimerei tra le 10 e le 15 ore.

Ho preso un permesso dal lavoro il venerdì per poter dedicare più tempo alla prova e ho poi lavorato nei ritagli di tempo del sabato mattina, della domenica mattina e nelle serate di lunedì.