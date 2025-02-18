===========================================================
 @CMSIMPLE_XH_VERSION@
 released @CMSIMPLE_XH_DATE@
===========================================================

 1. VORBEREITUNG / SERVER-TEST
 
 Lade die Datei "reqcheck.php" in das Verzeichnis hoch,
 in dem deine CMSimple_XH-Installation erfolgen soll.
 
 Rufe diese Datei mit einem Browser auf:
 http[s]://example.com/[Unterverzeichnis/]reqcheck.php
 
 Wenn Fehler oder Probleme gemeldet werden
 müssen diese beseitigt werden.
 
 Erst wenn alles GRÜN ist - also alles in Ordnung -
 kannst du mit der Installation beginnen,
 nachdem du die Datei "reqcheck.php" gelöscht hast.

===========================================================

 2. INSTALLATION

 Entpacke das ZIP-Archiv aus dem Download.
 Lade jetzt alle Dateien aus dem Ordner "cmsimplexh/"
 auf deinen Webserver hoch.
 
 Auf manchen Servern müssen explizit Schreibrechte
 für einige Dateien vergeben werden, siehe dazu:
 https://wiki.cmsimple-xh.org/doku.php/de:installation
 
 Ausführliche UPDATE-Instruktionen
 sind im CMSimple_XH-Forum verfügbar:
 https://www.cmsimpleforum.com/viewtopic.php?f=16&t=4895

===========================================================

 3. STANDARD-PASSWORT ÄNDERN
 
 Das Standard-Passwort für diese Installation lautet:
 "test" (ohne Anführungszeichen)
 
 Das Standard-Passwort muss nun unverzüglich geändert werden!
 Dafür hast du nach dem ersten Seitenaufruf 5 Minuten Zeit.
 In dieser Zeit kann nur das Passwort geändert werden,
 alle weiteren Einstellungen lassen sich nicht abspeichern.
 Gehe wie folgt vor:
 
 Logge dich mit 'test' ein! Du wirst automatisch weitergeleitet:
 Einstellungen > Passwort
 Hier kannst du nun dein eigenes Passwort eintragen.
 
 Sind die 5 min verstrichen, ist eine Anmeldung am System erst wieder möglich,
 wenn per FTP-Programm die Datei /userfiles/downloads/.defaultpw.lock geslöscht wird.
 Ab diesem Moment laufen wieder 5 min.

 WICHTIGER HINWEIS
 =================
 Bitte ändere das Standard-Passwort NICHT 
 mit einem Text-Editor direkt in der config.php,
 da diese nur das verschlüsselte Password enthält.
 Ändere das Standard-Passwort sofort nach dem ersten
 Login ONLINE! (Login mit dem Standard-Passwort "test")
 Dies ist die sicherste funktionierende Methode.
 
 Zur Bearbeitung der CMSimple_XH Systemdateien
 sollte ausschliesslich ein Editor (wie z.B. notepad++)
 benutzt werden, der die Codierung
 "utf-8 ohne BOM" (Byte Order Mark) erkennt, die Dateien
 so öffnet und auch wieder so abspeichert.
 
 Wenn die Systemdateien in einer anderen Codierung
 als "utf-8 ohne BOM" abgespeichert werden,
 kann es zu schwerwiegenden Problemen mit verschiedenen
 Funktionen von CMSimple_XH kommen.
 
 PASSWORT VERGESSEN
 Falls du dein Passwort vergessen hast, kannst du das
 Standard-Passwort "test" wieder herstellen.
 Trage dazu (offline) in der Datei "config.php"
 unter $cf['security']['password']= folgendes ein:
 \$2y\$10\$TtMCJlxEv6D27BngvfdNrewGqIx2R0aPCHORruqpe63LQpz7.E9Gq
 
 Anschließend die Datei "config.php" wieder
 auf den Server hochladen. Danach kannst du dich wieder
 mit dem Standard-Passwort "test" einloggen.

===========================================================

 Software-Beschreibung:
 ======================
 CMSimple_XH ist ein schnelles, kleines, leicht
 zu bedienendes und leicht zu installierendes
 modulares Content Management System (CMS), das
 keine Datenbank benötigt.
 CMSimple_XH speichert den Inhalt aller Seiten
 in einer einzigen HTML-Datei.
 Es ist freie Open Source Software unter der
 GPL3 Lizenz.
 