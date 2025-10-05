# moodle-mod_pi1
Minimale Moodle-Aktivität mit Rest-API-Call

---
 
 ## Server
  - **Domain:** https://alex-aufgabe.de/lms/
  - **Server IP:** 46.101.179.5
  - **Stack:** Ubuntu 24.04 · Nginx · PHP-FPM 8.3 · PostgreSQL · Moodle 5.0
  - **HTTPS:** Let's Encrypt (certbot; systemd timer aktiv)
  -  **Cron:** www-data führt jede Minute Moodle-Cron aus

---

## Verwendung

1. In Kurs "Aktivität oder Material anlegen" -> "API Demo (pi1)"
2. Search Parameter angeben (initiale Stadt -> Fallback bei ungültiger oder nicht sinnvoller Eingabe ist Berlin)
3. Aktivität aufrufen
    - Raw JSON Dump aus der API
    - HTML-formattierte Anzeige
    - Formular zur Suche eines neuen Standorts

---

## Aufgaben Kurzdoku (siehe Issues)
