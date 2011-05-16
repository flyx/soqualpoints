<?php
	// erases session entry if value is empty string or 0, but not NULL.
	// sets value to session entry if value is NULL
	// updates session entry to be value otherwise
	function syncToSession($name, &$value) {
		if ($value === "" || $value === -1) {
			unset($_SESSION[$name]);
			$value = NULL;
		} else if ($value === NULL) {
			if (isset($_SESSION[$name])) {
				$value = $_SESSION[$name];
			}
		} else {
			$_SESSION[$name] = $value;
		}
	}
	
	function numParam($value) {
		if ($value === "") {
			return -1;
		} else if ($value === NULL) {
			return NULL;
		} else {
			return (int) $value;
		}
	}
	
	// get filter parameter
	if (isset($_GET['resetfilter'])) {
		$selId = NULL;
		$selForename = "";
		$selSurname = "";
		$selStudnum = "";
		$selTeamnum = -1;
		$selSet = -1;
	} else {
		$selId = numParam(checkParam('id'));
		$selForename = checkParam('forename');
		$selSurname = checkParam('surname');
		$selStudnum = checkParam('stud_num');
		$selTeamnum = numParam(checkParam('team_num'));
		$selSet = numParam(checkParam('showset'));
	}
	$selEditId = numParam(checkParam('edit_id'));
	$selEditTaskSet = numParam(checkParam('edit_taskset'));
	$selForce = isset($_GET['force']) && $user['rights'] >= 3;
	
	// sync filter parameter to session
	syncToSession('forename', $selForename);
	syncToSession('surname', $selSurname);
	syncToSession('studnum', $selStudnum);
	syncToSession('teamnum', $selTeamnum);
	syncToSession('set', $selSet);
	
	if ($selId !== NULL) {
		$students = DB::get_students($selId, NULL, NULL, NULL, NULL);
	} else {
		// load students according to current filter
		$students = DB::filter_students($selForename, $selSurname, $selStudnum,
				$selTeamnum);
	}
	
	// load all tasksets
	$taskSets = DB::get_tasks(NULL, NULL, NULL, NULL, NULL);
	
	// build up option list of all available task sets
	// and remove all invisible task sets from the list
	
	function getOption($name, $value) {
		global $selSet;
		$selected = $selSet == $value ? "selected=\"selected\"" : "";
		return "<option value=\"$value\" " . $selected . ">$name</option>\n";
	}
	
	$setShowOptions = getOption("Alle", -1);
	
	foreach ($taskSets as $index => $taskSet) {
		$setShowOptions .= getOption($taskSet->getName(), $taskSet->getID());
		
		if ($selSet !== NULL and $selSet != $taskSet->getID()) {
			unset($taskSets[$index]);
		}
	}
		
	// load student points for all tasksets
	DB::get_current_points($students, $taskSets);
	
	/*foreach ($students as $student) {
		foreach ($taskSets as $taskSet) {
			$student->loadPoints($taskSet);
		}
	}*/
	
	// display filter input
?>
<form action="" method="get">
	<input type="hidden" name="page" value=<?php echo "\"$page\"";?> />
	<div id="filter"><table>
		<tr>
			<th colspan="7">
<?php echo isset($selId) ? "Einzelansicht" : "Filter"; ?>
			</th>
		</tr>
		<tr>
<?php if (isset($selId)) { ?>
			<td class="formsubmit" colspan="7"><input type="submit" name="filter" value="Einzelansicht verlassen" /></td>

<?php } else { ?>
			<td class="formlabel">Vorname:</td>
			<td class="formlabel">Nachname:</td>
			<td class="formlabel">Matrikelnummer:</td>
			<td class="formlabel">Team:</td>
			<td class="formlabel">Aufgabenblatt:</td>
			<td class="formsubmit" rowspan="2"><input type="submit" name="filter" value="Filtern" /></td>
			<td class="formsubmit" rowspan="2"><input type="submit" name="resetfilter" value="Zurücksetzen" /></td>

		</tr>
		<tr>
			<td  class="formfield">
				<input name="forename" type="text" size="20" value="<?php
					echo htmlspecialchars($selForename); ?>" />
			</td>
			<td class="formfield">
				<input name="surname" type="text" size="20" value="<?php
					echo htmlspecialchars($selSurname); ?>" />
			</td>
			
			<td  class="formfield">
				<input name="stud_num" type="text" size="20" value="<?php
					echo htmlspecialchars($selStudnum); ?>" />
			</td>
			<td class="formfield">
				<input name="team_num" type="number" min="0" step="1" size="20" value="<?php
					echo htmlspecialchars($selTeamnum); ?>" />
			</td>
			<td class="formfield">
				<select name="showset"> <?php echo $setShowOptions; ?></select>
			</td>
<?php } ?>
		</tr>
	</table></div>
</form>

<?php
	// implements some restrictions on table visibility based on number of students filtered
	function getTableVisibility () {
		global $selId, $selForename, $selSurname, $selStudnum, $selTeamnum, $selForce;
		global $contentInfoLabel, $allowForce, $forceLabel, $students, $user;
		$allowForce = FALSE;
		$showTable = FALSE;
		
		if ($selId === NULL && $selForename === NULL && $selSurname === NULL &&
				$selStudnum === NULL && $selTeamnum === NULL) {
			if ($selForce) {
				$showTable = TRUE;
			} else {
				$contentInfoLabel = "Benutze die Filterfunktion, um Studenten anzuzeigen.";
				$allowForce = $user['rights'] >= 3;
				$forceLabel = "Alle Studenten anzeigen";
			}
		} else if (count($students) > 10) {
			if ($user['rights'] >= 3 && isset($_GET['force'])) {
				$showTable = TRUE;
			} else {
				$contentInfoLabel = "Die Suchkriterien treffen auf zu viele Studenten zu. Bitte schränke sie weiter ein.";
				$allowForce = $user['rights'] >= 3;
				$forceLabel = "Trotzdem anzeigen";
			}
		} else {
			$showTable = TRUE;
		}
		return $showTable;
	}
?>
<div id="spaaaaaaace"></div>