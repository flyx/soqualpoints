<div id="fadingcontent"></div>
<div id="tablewrapper" style="bottom: <?php
	if ($enable_debug) echo "250px"; else echo "0"; ?>; top: 1.5em; padding: 1em; padding-top: 0.5em;">
<?php
	if ($user['rights'] < 3) {
		echo "<strong>Diese User hat keine Administrationsberechtigung.</strong>";
	} else {
		$rn[1] = '1 - read';
		$rn[2] = '2 - read &amp; write';
		$rn[3] = '3 - admin';
?>	
<div id="useraccounts">

<h2>Benutzerkontenverwaltung</h2>
		
<table id="usertable">
	<tr>
		<th class="lastheading">ID</th>
		<th class="lastheading">Name</th>
		<th class="lastheading">PW ändern</th>
		<th class="lastheading">Rechte</th>
		<th class="lastheading" colspan="2">ändern</th>
	</tr>
<?php
		$query = "SELECT id, name, rights FROM `users` ORDER BY id ASC";
		$result = DB::query($query);
		while ($row = mysql_fetch_assoc($result)) {
			echo "\t<tr>\n";
			echo "\t\t<td>" . $row['id'] . "</td>\n";
			echo "\t\t<td>" . htmlspecialchars($row['name'], ENT_QUOTES, "UTF-8") . "</td>\n";
			echo "\t\t<form action=\"?page=admin&amp;action=updateuser\" ";
			echo "method=\"post\">\n";
			echo "\t\t\t<input type=\"hidden\" name=\"id\" value=\"" . $row['id'];
			echo "\" />\n";
			echo "\t\t\t<td><input name=\"new_password\" type=\"password\" ";
			echo "size=\"15\" /></td>\n";
			if ($row['id'] == 1) {
				echo "\t\t\t<td>" . $rn[3] . "</td>\n";
				echo "\t\t\t<td><input type=\"submit\" value=\"set\" /></td>\n";
			} else if ($row['rights'] == 0) {
				echo "\t\t\t<td><em>beantragt</em></td>\n";
				echo "\t\t\t<input type=\"hidden\" name=\"approve\" value=\"X\" />\n";
				echo "\t\t\t<td><input type=\"submit\" value=\"approve\" /></td>\n";
			} else {
				echo "\t\t\t<td><select name=\"rights\">\n";
				for ($i = 1; $i <= 3; $i++) {
					echo "\t\t\t\t<option value=\"$i\"";
					if ($row['rights'] == $i) {
						echo " selected=\"selected\"";
					}
					echo ">" . $rn[$i] . "</option>\n";
				}
				echo "\t\t\t</select></td>\n";
				echo "\t\t\t<td><input type=\"submit\" value=\"set\" /></td>\n";
			}
			
			echo "\t\t</form>\n";
			if ($row['id'] == 1) {
				echo "\t\t<td></td>\n";
			} else {
				echo "\t\t<form action=\"?page=admin&amp;action=deluser\" ";
				echo "method=\"post\">\n";
				echo "\t\t\t<td>\n";
				echo "\t\t\t\t<input type=\"hidden\" name=\"id\" value=\"";
				echo $row['id'] . "\" />\n";
				echo "\t\t\t\t<input type=\"submit\" value=\"del\" />\n";
				echo "\t\t\t</td>\n";
				echo "\t\t</form>\n";
			}
			echo "\t</tr>\n";
		}
?>
</table>
		
<table id="newuser"><tr>
	<th colspan="5">Neuen Benutzer erstellen</th>
</tr><tr>
	<td>Name</td>
	<td>Passwort</td>
	<td>Rechte</td>
	<td></td>
</tr><tr>
	<form action="?page=admin&amp;action=adduser" method="post">
		<td><input type="text" name="username" size="10" /></td>
		<td><input type="password" name="password" size="10" /></td>
		<td><select name="rights">
<?php
	for ($i = 1; $i <= 3; $i++) {
		echo "\t\t\t<option value=\"$i\">" . $rn[$i] . "</option>\n";
	}
?>
		</select></td>
		<td><input type="submit" value="OK" /></td>
	</form>
</tr></table>	
	
</div>

<div id="csvimport">
	<h2>CSV Import</h2>
	<p><strong>Wichtig:</strong> Falls Aufgabenblattpunkte importiert werden
	sollen, müssen die Aufgabenblätter vor dem Import manuell erstellt werden.</p>
	<form enctype="multipart/form-data" action="?page=csvimport" method="POST">
		<input type="hidden" name="MAX_FILE_SIZE" value="1048576" />
		<input name="csvfile" type="file" /><br />
		<table class="checkboxtable"><tr><td>
			<input type="checkbox" name="skipcols" value="1" />
		</td><td>
			Datei enthält 2 Spalten mit Gesamtpunktzahlen (werden beim Import
			übersprungen)
		</td></tr><tr><td>
			<input type="checkbox" name="delfields" value="1" />
		</td><td>
			Existierende Werte löschen, falls der Wert in der CSV-Datei leer ist
			(gilt nicht für Felder, die gar nicht in der CSV-Datei stehen)
		</td></tr></table>
		<input type="submit" value="Hochladen" />
	</form>
</div>

<div id="csvexport">
	<h2>CSV Export</h2>
	<p>Die exportierte CSV-Datei enthält sämtliche Studenten und deren Punkte
	für sämtliche Aufgabenblätter. Die Daten der Aufgabenblätter werden nicht
	mit ausgegeben, da es im CSV-Format keine sinnvolle Möglichkeit dazu gibt.
	Die exportierte Datei enthält auch nicht die Gesamtpunkte. Sie lässt sich
	mit der Import-Funktion wieder in die Datenbank einlesen.</p>
	<form action="csvexport.php" method="POST">
		<input type="submit" value="Exportieren" />
	</form>
</div>
<?php } ?>
</div>