moodle-blocks_course_fisher
===========================
Originariamente sviluppato da Roberto Pinna, Diego Fantoma e Angelo Calò, il course fisher consente la creazione automatica dei corsi da parte dei docenti, acquisendo l’offerta formativa da fonti esterne (json, csv, DB, soap ecc) attraverso la configurazione dei relativi plugin.

Modificato, così da ottenere una ottimizzazione della funzionalità originaria, da Francesco Carbone, della Scuola di Scienze umane, sociali e del partimonio culturale dell'Università degli Studi di Padova.


Questa versione modificata consente:

- La creazione dei corsi mediante l'importazione di un template, con la personalizzazione automatica dell'introduzione del corso e del nome della prima sezione.

- La creazione automatica di una "Url" che rimanda alla scheda didattica nel sito di Ateneo del corrispondente insegnamento.

- L'invio di una mail agli amministratori qualora, all'attivazione dei corsi da parte dei docenti, si verifichino condizioni particolari legate alle specifiche esigenze di gestione dei corsi.

- Una ottimale gestione dei corsi "mutuati" (condivisi da più corsi di laurea) grazie all'aggregazione delle mutuazioni in fase di attivazione, con la risoluzione di alcune criticità presenti nelle versioni precedenti del plugin (per esempio la inutile iscrizione degli utenti a più corsi con metodo meta corso).

- La definizione del backend json attraverso una "Url" parametrizzata, velocizzando drasticamente i tempi di caricamento delle informazioni nei casi di file particolarmente pesanti.