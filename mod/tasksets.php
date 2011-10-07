<div id="fadingcontent"></div>
<?php
	$taskSets = DB::get_tasks(NULL, NULL, NULL, NULL, NULL);
?>
<table id="contenttable" class="tasksettable">
	<thead>
	<tr>
		<th>Name</th>
		<th>Anzahl Aufgaben Diskussionsteil</th>
		<th>Anzahl Aufgaben Programmierteil</th>
		<th>Maximale Punkte Programmierteil</th>
<?php
	if ($user['rights'] > 1) {
		echo "\t\t<th colspan=\"2\"></th>";
	}
?>		
	</tr>
	</thead>
	<tbody>
<?php
	$lightBg = False;
	foreach ($taskSets as $taskSet) {
		$lightBg = !$lightBg;
		if ($lightBg) {
			echo "\t<tr class=\"lightbg\">\n";
		} else {
			echo "\t<tr>\n";
		}
		if ($user['rights'] > 1) {
			echo "\t\t<form action=\"?page=tasksets&amp;action=updateset\" method=\"post\">\n";
			echo "\t\t\t<input name=\"id\" type=\"hidden\" value=\"" .
				 $taskSet->getID() . "\" />\n";
			echo "\t\t\t<td><input name=\"name\" type=\"text\" value=\"" .
				 $taskSet->getName() . "\" size=\"16\" /></td>\n";
			echo "\t\t\t<td><input name=\"count_disc_tasks\" type=\"number\" min=\"0\" step=\"1\" value=\"" .
				 $taskSet->getNumDiscTasks() . "\" size=\"5\" /></td>\n";
			echo "\t\t\t<td><input name=\"count_prog_tasks\" type=\"number\" min=\"0\" step=\"1\" value=\"" .
				 $taskSet->getNumProgTasks() . "\" size=\"5\" /></td>\n";
			echo "\t\t\t<td><input name=\"max_prog_points\" type=\"number\" min=\"0\" step=\"1\" value=\"" .
				 $taskSet->getMaxProgPoints() . "\" size=\"5\" /></td>\n";
			echo "\t\t\t<td><input type=\"submit\" value=\"Speichern\" /></td>\n";
			echo "\t\t</form>\n";
			echo "\t\t<form action=\"?page=tasksets&amp;action=delset\"" .
				 " method=\"post\">\n";
			echo "\t\t\t<input type=\"hidden\" name=\"id\" value=\"" . $taskSet->getID() .
				 "\" />\n";
			echo "\t\t\t<td>\n";
			echo "\t\t\t\t<input type=\"submit\" value=\"del\" />\n";
			echo "\t\t\t</td>";
			echo "\t\t</form>\n";
		} else {
			echo "\t\t<td>" . $taskSet->getName() . "</td>\n";
			echo "\t\t<td>" . $taskSet->getNumDiscTasks() . "</td>\n";
			echo "\t\t<td>" . $taskSet->getNumProgTasks() . "</td>\n";
			echo "\t\t<td>" . $taskSet->getMaxProgPoints() . "</td>\n";
		}	
		echo "\t</tr>\n";
	}
	if ($user['rights'] > 1) {
?>
</table>
	<form action="?page=tasksets&amp;action=addset" method="post">
	<table id="addform" class="tasksettable">
		<tr>
			<th colspan="5">Aufgabenblatt hinzufügen</th>
		</tr>
		<tr>
			<td class="formlabel">Name:</td>
			<td class="formlabel">Aufgaben Diskussionsteil:</td>
			<td class="formlabel">Aufgaben Programmierteil:</td>
			<td class="formlabel">Punkte Programmierteil:</td>
			<td></td>

		</tr>
		<tr>
			<td  class="formfield">
				<input name="name" type="text" size="16" />
			</td>
			<td class="formfield">
				<input name="count_disc_tasks" type="number" min="0" step="1" size="5" />
			</td>
			
			<td  class="formfield">
				<input name="count_prog_tasks" type="number" min="0" step="1" size="5" />
			</td>
			<td class="formfield">
				<input name="max_prog_points" type="number" min="0" step="1" size="5" />
			</td>
			<td class="formsubmit"><input type="submit" value="Hinzufügen" /></td>
		</tr>
		</tbody>
	</table>
	</form>
<?php } ?>