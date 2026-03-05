# AWO Feedback-Plattform (Ferienbetreuung)

Ein komplett eigenes, sicheres Feedback-System für Eltern und Kinder (LAMP-Stack, speziell für IONOS Shared Hosting optimiert).

## Features
- **Öffentlich:** Zwei Formulare für Eltern und Kinder (anonym, DSGVO-konform, kein Tracking).
- **Admin-Bereich:** Sicherer Login, Dashboard (Filterung nach Standort & Ferien), Datenübersicht.
- **Export:** Robuster CSV-Export (von Excel einwandfrei lesbar).
- **Sicherheit:** PDO (Prepared Statements), CSRF-Schutz, serverseitige Validierung, XSS-Prävention (htmlentities/htmlspecialchars).
- **Jahresabschluss:** Funktion, um alle Datensätze zu leeren.

## IONOS Installations-Anleitung

1. **Datenbank erstellen**
   - Loggen Sie sich ins IONOS Control-Center ein.
   - Erstellen Sie eine neue MySQL-Datenbank.
   - Notieren Sie sich: Hostname (`dbXXXXX.hosting-data.io`), Datenbankname, Benutzername und Passwort.

2. **Datenbank-Schema importieren**
   - Öffnen Sie phpMyAdmin über IONOS.
   - Importieren Sie die Datei `sql/schema.sql` in die neu angelegte Datenbank.

3. **Dateien hochladen**
   - Laden Sie den gesamten Projektordner (z. B. als `/lernsystem/feedback/`) per FTP auf Ihren Webspace hoch.
   - *(Optional aber empfohlen für IONOS)*: Schützen Sie das `includes/`-Verzeichnis oder verschieben Sie die `config.php` eine Ebene über Ihr Webroot (`htdocs` oder `public_html`), um direkten Zugriff komplett zu unterbinden.

4. **Konfiguration anpassen**
   - Kopieren Sie `includes/config.sample.php` zu `includes/config.php`.
   - Tragen Sie in `config.php` die notierten Datenbank-Zugangsdaten ein.
   - **WICHTIG**: Ändern Sie das Standard-Passwort für den Admin-Bereich! Generieren Sie einen neuen Hash (z. B. lokal mit `echo password_hash('MeinNeuesPasswort!', PASSWORD_DEFAULT);`) und ersetzen Sie den Wert in der Datei. Standard-Login ist `admin` / `admin123`.

5. **Testen**
   - Rufen Sie `/public/eltern/index.php` und `/public/kinder/index.php` auf. Senden Sie je einen Test-Eintrag ab.
   - Rufen Sie `/admin/index.php` auf, loggen Sie sich ein, prüfen Sie das Dashboard und testen Sie den CSV-Export.

## Acceptance Checks (Manuell durchzuführen)

- [ ] Aufruf Eltern-Feedback möglich.
- [ ] Auswahl von "Andere Quelle" bei Eltern-Formular blendet Textfeld ein und markiert es als Pflichtfeld.
- [ ] Erfolgreiches Speichern eines Eltern-Feedbacks leitet auf die Danke-Seite weiter.
- [ ] Aufruf Kinder-Feedback möglich.
- [ ] Alle Smileys sind klickbar (Auswahl farblich markiert).
- [ ] Erfolgreiches Speichern eines Kinder-Feedbacks leitet auf die Danke-Seite weiter.
- [ ] Admin-Bereich: Login ohne korrekte Daten wird abgewiesen.
- [ ] Admin-Bereich: Login mit korrekten Daten öffnet Dashboard.
- [ ] Admin-Bereich: Das Dashboard zeigt die neu eingegebenen Testdaten (Anzahl & Tabelle).
- [ ] Admin-Bereich: Der Export-Button lädt eine CSV-Datei herunter.
- [ ] Admin-Bereich: Die CSV-Datei lässt sich in Excel korrekt als Spalten öffnen (durch BOM und Strichpunkt getrennt).
- [ ] Admin-Bereich: Lösch-Funktion leert die Datenbank vollständig (wird erst am Jahresende genutzt!).

## Erweiterbarkeit (Excel XLSX Export)
Da auf IONOS Shared Hosting oft kein Composer via SSH nutzbar ist, wurde standardmäßig ein voll kompatibler CSV-Export gebaut. Sollte natives `.xlsx` nötig sein:
Führen Sie lokal `composer install` (oder `composer require phpoffice/phpspreadsheet`) aus und laden Sie den generierten `/vendor` Ordner auf den Server. Dann kann `export.php` umgebaut werden.
