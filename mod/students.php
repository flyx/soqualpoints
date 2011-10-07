<?php
	require("searchform.php");
if (getTableVisibility()) {	
?>
<table id="contenttable">
	<thead>
	<tr>
		<th>Vorname</th>
		<th>Nachname</th>
		<th>Matrikelnr.</th>
		<th>Team</th>
<?php
	if ($user['rights'] > 1) {
		echo "\t\t<th colspan=\"2\"></th>";
	}
?>
	</tr>
	</thead>
	<tbody>
<?php
	$editEverything = (count($students) <= 5);
	$recentTeam = -1;
	$lightBg = False;
	foreach ($students as $student) {
		if ($student->getTeamNum() != $recentTeam) {
			$recentTeam = $student->getTeamNum();
			$lightBg = !$lightBg;
		}
		echo $lightBg ? "\t<tr class=\"lightbg\">\n" : "\t<tr>\n";
		if (($editEverything or $selEditId == $student->getID()) and $user['rights'] > 1) {
			echo "\t\t<form action=\"?page=students&amp;action=updatestudent\" method=\"post\">\n";
			echo "\t\t\t<input type=\"hidden\" name=\"id\" value=\"" . $student->getID() . "\" />\n";
			echo "\t\t\t<td><input name=\"forename\" type=\"text\" size=\"20\" value=\"" .
				 $student->getForename() . "\" /></td>\n";
			echo "\t\t\t<td><input name=\"surname\" type=\"text\" size=\"20\" value=\"" .
				 $student->getSurname() . "\" /></td>\n";
			echo "\t\t\t<td><input name=\"student_number\" type=\"text\" size=\"12\" value=\"" .
				 $student->getStudNum() . "\" /></td>\n";
			echo "\t\t\t<td><input name=\"team_number\" type=\"number\" min=\"0\" step=\"1\" size=\"3\" value=\"" .
				 $student->getTeamNum() . "\" /></td>\n";
			echo "\t\t\t<td><input type=\"submit\" value=\"Speichern\" /></td>\n";
			echo "\t\t</form>\n";
			echo "\t\t<form action=\"?page=students&amp;action=delstudent\"" .
				 " method=\"post\">\n";
			echo "\t\t\t<input type=\"hidden\" name=\"id\" value=\"" . $student->getID() .
				 "\" />\n";
			echo "\t\t\t<td>\n";
			echo "\t\t\t\t<input type=\"submit\" value=\"del\" />\n";
			echo "\t\t\t</td>";
			echo "\t\t</form>\n";
		} else {
			echo "\t\t<td>" . $student->getForename() . "</td>\n";
			echo "\t\t<td>" . $student->getSurname() . "</td>\n";
			echo "\t\t<td>" . $student->getStudNum() . "</td>\n";
			echo "\t\t<td>" . $student->getTeamNum() . "</td>\n";
			echo "\t\t<td>";
			if ($user['rights'] > 1) {
				echo "<a class=\"tableButton\" href=\"?page=students&amp;edit_id=";
				echo $student->getID();
				if ($selForce) echo "&amp;force=";
				echo "\">✎</a>&nbsp;";
			}
			echo "\t\t</td><td></td>\n";
		}
		echo "\t</tr>\n";
	} ?>
	</tbody>
</table>
<?php } else { ?>
<div id="contentinfo">
	<p><?php echo $contentInfoLabel; ?></p>
<?php if ($allowForce) { ?>
	<form action="" method="get">
		<input type="hidden" name="page" value="students" />
		<input type="hidden" name="force" value="" />
		<span class="formsubmit"><input type="submit" value="<?php echo $forceLabel; ?> (ADMIN)" /></span>
	</form>
<?php } ?>
</div>
<?php }
	if ($user['rights'] > 1) {
?>
	<form action="?page=students&amp;action=addstudent" method="post">
	<table id="addform">
		<thead>
		<tr>
			<th colspan="5">Student hinzufügen</th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td class="formlabel">Vorname:</td>
			<td class="formlabel">Nachname:</td>
			<td class="formlabel">Matrikelnummer:</td>
			<td class="formlabel">Team:</td>
			<td></td>

		</tr>
		<tr>
			<td  class="formfield">
				<input name="forename" type="text" size="20" />
			</td>
			<td class="formfield">
				<input name="surname" type="text" size="20" />
			</td>
			
			<td  class="formfield">
				<input name="student_number" type="text" min="0" step="1" size="20" />
			</td>
			<td class="formfield">
				<input name="team_number" type="number" min="0" step="1" size="20" />
			</td>
			<td class="formsubmit"><input type="submit" value="Hinzufügen" /></td>
		</tr>
		</tbody>
	</table>
	</form>
<?php } ?>