<div id="help">

<h2>Hilfe</h2>

<h3>Anlegen von Aufgabenblättern</h3>

<p>Jedes Aufgabenblatt hat einen Namen sowie eine Anzahl an Aufgaben
für den Diskussions- sowie den Programmierteil (diese Anzahl kann 
gleich Null sein).</p>
<ul>
	<li><strong>Diskussionsteil:</strong> Es wird davon ausgegangen,
	dass in jeder dieser Aufgabe 0 oder 1 Punkt vergeben werden kann.
	Die maximal mögliche Anzahl Punkte errechnet sich durch die Summe
	aller Aufgaben.</li>
	<li><strong>Programmierteil:</strong> Hier wird jeder Zahlenwert
	für die Bewertung angenommen. Die maximal mögliche Anzahl Punkte
	wird manuell eingegeben.</li>
</ul>
<p><strong>Wichtig:</strong> Beim Löschen eines Aufgabenblattes gehen
alle dafür eingetragenen Punkte unwiderruflich verloren!</p>

<h3>Datenschutz und Filterformular</h3>
<p>Aus Datenschutzgründen wird nie eine vollständige Liste aller in
der Datenbank eingetragenen Studenten bzw. deren Punkte angezeigt.
Sie lassen sich nur durch Verwendung des Filterformulars finden. Das
Filterformular ignoriert Groß- und Kleinschreibung und sucht die
eingetragene Buchstabenfolge an einer beliebigen Stelle. Eine Filterung
nach <em>in</em> findet also sowohl <em>Inge</em> als auch <em>Heinz
</em>. Das Filterergebnis genügt allen gegebenen Filterkriterien.
Falls die Filterung zu mehr als 10 Ergebnisse liefert, werden diese -
wiederum aus Datenschutzgründen - nicht angezeigt. Die
Filterkriterien müssen dann weiter eingeschränkt werden.</p>

<p>Als User mit <em>Admin</em>-Berechtigung ist es möglich,
sowohl alle Studenten als auch das Ergebnis einer Anfrage, die aus
mehr als 10 Treffern besteht, anzeigen zu lassen. Hierfür muss
nach der Anfrage noch auf einen Button geklickt werden, der
explizit mit dem Präfix <strong>(ADMIN)</strong> versehen ist.</p>

<h3>Studentenverwaltung</h3>
<p>Für jeden Eintrag sollten alle Felder ausgefüllt werden. Es ist
allerdings problemlos möglich, eine Matrikelnummer erst nachträglich
einzutragen. Auch das Ändern aller Daten ist möglich und führt nicht
zu einem Verlust von Punktedaten.</p>

<h3>Punktetabelle</h3>
<p>Alle Punkte werden für jeden Studenten einzeln gespeichert. Das
bedeutet insbesondere, dass auch die Programmierteilpunkte, die die
Studenten in der Regel als Team erbringen, einzeln gespeichert werden.
Das macht es möglich, nachträglich einen Studenten in ein anderes
Team zu verschieben oder einem inaktiven Teammitglied keine Punkte zu
geben.</p>

<p>Die Diskussionsteilpunkte sind durch einfache Checkboxen realisiert.
Zu Beginn sind die Felder im Hintergrund grau, das bedeutet, dass keine
Daten zum entsprechenden Eintrag vorliegen. Trägt man einmal Punkte ein,
werden in jedem Fall alle Diskussionsteilpunkte gesetzt - also entweder
auf 0 oder auf 1. Es besteht keine Möglichkeit, über das Web-Frontend
den Urzustand "keine Daten vorhanden" wiederherzustellen.</p>

<p>Beim Programmierteil dagegen werden Nummernfelder verwendet. Hier
bedeutet ein leeres Feld, dass keine Daten vorhanden sind - man kann
explizit eine 0 eintragen, wenn man 0 Punkte vergeben will. Ist ein
Feld leer, wenn man das Formular absendet, wird die entsprechende
Punktezahl - sofern vorhanden - gelöscht. Es können auch halbe Punkte
vergeben werden.</p>

<p>Das Formular zum Eintragen der Punkte wird angezeigt, wenn für keine
der Diskussionsteilaufgaben oder keine der Programmierteilaufgaben eine
Punktezahl in der Datenbank gespeichert ist. Sind bei beiden je
mindestens eine Zahl vorhanden, wird kein Formular angezeigt, sondern
die Punkte als Text ausgegeben. Beim Diskussionsteil ist <em>leer</em>
= 0, <strong>✓</strong> = 1. Beim Programmierteil wird der Zahlenwert -
sofern vorhanden - angezeigt. Um bestehende Punkte zu ändern, kann über
den <em>✎</em>-Button das Formular angezeigt werden.</p>

<p>Die Formulare sind immer pro Student pro Aufgabenblatt. Mit einem Mal
können nicht die Punkte für mehrere Aufgabenblätter oder mehrere
Studenten gesetzt werden. Ausnahme: Bei der Suche nach einer Teamnummer
wird ein zusätzliches Formular angezeigt, mit dem die Punkte des 
Programmierteils für alle Mitglieder dieses Teams gesetzt werden
können.</p>

<p>Die Gesamtpunktzahl vorne in der Tabelle errechnet sich immer
über die angezeigten Aufgabenblätter - nicht über alle verfügbaren.
Für den Diskussionsteil muss die Hälfte der möglichen Punkte
erreicht werden, für den Programmierteil zwei Drittel. Die 
erreichbaren Punkte werden ebenfalls nur über die angezeigten
Aufgabenblätter errechnet. Grüner Hintergrund bedeutet, die
erforderliche Anzahl ist erreicht; Roter Hintergrund bedeutet,
sie ist nicht erreicht. Ist im Diskussionsteil oder Programmierteil
eines Aufgabenblatts für keine einzige Aufgabe ein Punktewert
gesetzt, fließt dieser Aufgabenblattteil <strong>nicht</strong> in die Berechnung
der Gesamtpunktzahl und das Verhältnis mit ein. Die Gesamtpunktzahl
ist damit immer eine Zusammenfassung aller ganz oder teilweise
bearbeiteten Teilen der Aufgabenblätter.</p>

<p>Beispiel: Ein Student hat kein einziges Häkchen im
Diskussionsteil, die Felder sind aber nicht grau (also ist der
Wert 0 für alle Felder gesetzt). Die Maximalpunktzahl ist
dann gleich der Anzahl aller Diskussionsteilaufgaben, die 
Gesamtpunktzahl ist 0, das Feld wird rot. Im Programmierteil
steht nirgendwo eine Zahl. Kein Feld ist gesetzt, Maximal- und
Gesamtpunktzahl sind beide 0, das Feld wird grün. (Jaja, die 
Mathematiker dürfen sich jetzt ärgern...) Wäre nur ein Feld
des Programmierteils gesetzt gewesen, wäre sofort die manuell
gesetzte maximale Punktzahl des Aufgabenblattes zur Berechnung
herangezogen worden.</p>

<h3>Revisionen</h3>
<p>Von der Punktetabelle wird eine Revisionsgeschichte geführt. Diese
erfasst ausschließlich die eingetragenen Punkte - nicht die Daten
der Studenten und auch nicht die der Aufgabenblätter. Die Revisionen
werden pro Student pro Aufgabenblatt geführt. Es wird gespeichert,
welcher User zu welchem Zeitpunkt welche neuen Daten eingetragen hat.
Es wird nicht überprüft, ob die neuen Daten mit den alten übereinstimmen
(und eine neue Revision dadurch überflüssig wäre).</p>

<p>Die Revisionen sind eine reine Informationsquelle, sie besitzen 
keine weitere Funktion. Ein Rollback auf einen älteren Punktestand muss
also manuell vorgenommen werden. Zugriff auf die Revisionen erhält man
entweder durch die Benutzung des Filterformulars oder durch den Button 
<em>⇡</em> in der Punktetabelle.</p>
</div>

<div id="fadingcontent"></div>