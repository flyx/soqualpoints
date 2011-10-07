Punktetabelle Softwarequalität, v3.0
====================================

Systemanforderungen
-------------------

 * PHP 5.x
 * MySQL 5.1.x
 * Ein Webserver (gestestet mit Apache 2.2.x)

Installation
------------

Zunächst muss eine neue MySQL-Datenbank
angelegt werden. Die Punktetabelle
Softwarequalität (nachfolgend abgekürzt mit PS)
ist nicht dafür ausgelegt, sich eine Datenbank
mit anderen Anwendungen zu teilen.

Alle Dateien von PS (außer dem README) müssen
unter Beibehaltung der Ordnerstruktur in einen
über HTTP(S) erreichbaren Pfad auf dem Server
gelegt werden. Die Installationsroutine wird eine
Datei `config.php` erstellen, dafür muss der
Webserver Schreibrechte auf den Installationsordner
von PS haben.
Falls eine Datei `config.php` bereits existiert,
muss diese vor Beginn der Installation gelöscht
werden.

Wenn diese Voraussetzungen erfüllt sind, kann
das Installations-Webinterface über
`$URL/setup.php` gestartet werden.

Hier kann der Titel der Installation, die Daten
des MySQL-Servers und Name und Passwort des
Root-Users angegeben werden. **Diese Daten werden
in keinster Weise auf Sinnigkeit überprüft**. Nur
die Datenbank-Schnittstelle wird einen Fehler
werfen, wenn die eingegebenen Daten inkorrekt sind.

Es besteht die Option, die Datenbank zu
verschlüsseln. **Diese Einstellung kann und darf
nachträglich nicht mehr verändert werden**. Bei
der Verschlüsselung ist zu beachten, dass die
Daten nicht wiederhergestellt werden können, falls
alle Benutzer ihr Passwort vergessen. Das ist ja
schließlich der Sinn der Verschlüsselung, nicht wahr?

Nachdem das Setup-Skript gelaufen ist, sollte die
Datei setup.php vom Server gelöscht werden.
Sie lässt sich zwar nicht benutzen, solange die
Datei `config.php` existiert, ist aber dennoch ein
Sicherheitsrisiko.

_Hinweis für Entwickler: `config.php` enthält eine
Zeile_

	$enable_debug = False;
	
_Um Debugausgabe zu aktivieren, muss `$enable_debug`
auf `True` gesetzt werden. **Die Debugausgabe sollte
niemals auf einem Produktivsystem verwendet werden.
Sie schreibt Datenbank-Queries auf die Seite, die
eventuell den Datenbankschlüssel oder Hashwerde von
Passwörtern enthalten können.**_

Administration
--------------

Nach erfolgreicher Einrichtung kann man sich mit
dem Root-Usernamen und Passwort einloggen und hat
Zugriff auf das Admin-Panel.

In der Benutzerkontenverwaltung können neue Benutzer
hinzugefügt, existierende Benutzer gelöscht und
Rechte geändert werden. Es gibt 4 Rechtestufen:

 *	0 - beantragt:
 
	Ein neuer User hat sich über das Registrieren-
	Formular der Seite angemeldet. Dieser muss von
	einem Administrator freigeschaltet werden,
	bevor er Zugriff auf die Seite hat. Diese
	Stufe kann nicht nachträglich vergeben werden.

 *	1 - read:
 
	Ein Benutzer mit Leserechten. Er hat keinerlei
	Rechte, irgendetwas zu verändern.

 *	2 - read & write:
 
	Ein Benutzer mit Lese- und Schreibrechten. Er
	kann Punkte, Studenten und Aufgabenblätter
	erstellen, bearbeiten und löschen.

 *	3 - admin:
 
	Ein Administrator. Der Root-User ist immer
	Administrator, das kann nicht geändert werden.
	Nur Administratoren haben Zugriff auf das Admin-
	Panel.

CSV In- und Export
------------------

Über den Export als CSV-Datei lässt sich die
Datenbank denkbar einfach sichern. Ungeachtet der
Konfiguration der Datenbank (verschlüsselt oder
nicht) enthält die exportierte Datei immer Klartext.

Der Import ist einerseits für das Wiedereinspielen
eines Exports geeignet, andererseits aber auch zum
Importieren anderer Datenquellen, wie etwa einer
Studentenliste, die mit OpenOffice erstellt und als
CSV exportiert wurde.

Für den Import wird folgendes Format für jede
Zeile verwendet:

	Teamnummer, Nachname, Vorname, Matrikelnummer (, Punkte)*

Die Daten der Aufgabenblätter werden weder ex- noch
importiert. Sie müssen vor dem Import im System erstellt werden.

Der Import löscht keine bestehenden Daten, sondern
überschreibt sie nur gegebenenfalls. Falls die
CSV-Datei oder die bestehende Datenbank mehrere identische
Studenten enthält, schlägt der Import fehl.

Dokumentation
-------------

Die Dokumentation des Benutzerinterfaces findet
sich nach der Installation von PS unter
`$URL/?page=help`.

Kontakt
-------

Fehlermeldungen und Featurerequests können auf der
[GitHub-Seite des Projekts](https://github.com/flyx86/soqualpoints)
als Issues gespostet werden. Dort können auch
Nachrichten an mich persönlich geschickt werden.

Lizenz
------

Soqualpoints ist freie Software und steht unter
der GNU GPL v3 (siehe Datei `COPYING`).