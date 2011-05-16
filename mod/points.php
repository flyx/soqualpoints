<?php
	require("searchform.php");
if (getTableVisibility()) {	
?>
<table id="contenttable">
	<thead>
	<tr>
		<th rowspan="3" class="lastheading">Vorname</th>
		<th rowspan="3" class="lastheading">Nachname</th>
		<th rowspan="3" class="lastheading"><abbr title="Matrikelnummer">Mat.nr.</abbr></th>
		<th rowspan="3" class="lastheading">Team</th>
		<th rowspan="2" colspan="2" class="lastcell">Gesamt-<br />punkte</th>
<?php
	foreach($taskSets as $taskSet) {
		echo "\t\t<th ";
		echo "colspan=\"". ($taskSet->getNumDiscTasks() +
		$taskSet->getNumProgTasks()) ."\">" . $taskSet->getName() . "</th>\n";
		echo "\t\t<th class=\"lastcell\"></th>\n";
	}
?>
	</tr>
	<tr>
<?php
	foreach($taskSets as $taskSet) {
		if ($taskSet->getNumDiscTasks() > 0) {
			echo "\t\t<th colspan=\"" . $taskSet->getNumDiscTasks() . "\"";
			echo ">Disk.</th>\n";
		}
		if ($taskSet->getNumProgTasks() > 0) {
			echo "\t\t<th ";
			echo "colspan=\"" . $taskSet->getNumProgTasks() . "\">Prog.</th>\n";
		}
		echo "\t\t<th class=\"lastcell\"></th>\n";
	}
?>
	</tr>
	<tr>
		<th class="lastheading">Disk.</th>
		<th class="lastcell lastheading">Prog.</th>
<?php
	foreach($taskSets as $taskSet) {
		$curNum = 1;
		for($i = 0; $i < $taskSet->getNumDiscTasks(); $i++) {
			echo "\t\t<th class=\"lastheading\">$curNum</th>\n";
			$curNum++;
		}
		for($i = 0; $i < $taskSet->getNumProgTasks(); $i++) {
			echo "\t\t<th ";
			echo "class=\"lastheading\">$curNum</th>\n";
			$curNum++;
		}
		echo "\t\t<th class=\"lastcell lastheading\"></th>\n";
	}
?>
	</tr>
	</thead>
	<tbody>
<?php
	$curTeam = -1;
	$teamLight = false;
	foreach($students as $student) {
		if ($student->getTeamNum() != $curTeam) {
			$teamLight = !$teamLight;
			$curTeam = $student->getTeamNum();
		}
		if ($teamLight) {
			echo "\t<tr class=\"lightbg\">\n";
		} else {
			echo "\t<tr>\n";
		}
		echo "\t\t<td>" . $student->getForename() . "</td>\n";
		echo "\t\t<td>" . $student->getSurname() . "</td>\n";
		echo "\t\t<td>" . $student->getStudNum() . "</td>\n";
		echo "\t\t<td>" . $student->getTeamNum() . "</td>\n";
		$discPoints = 0;
		$maxDiscPoints = 0;
		$progPoints = 0;
		$maxProgPoints = 0;
		foreach ($taskSets as $taskSet) {
			$points = $student->getPoints($taskSet);
			if ($points->hasDiscPoints()) {
				$discPoints += $points->countDiscPoints();
				$maxDiscPoints += $taskSet->getNumDiscTasks();
			}
			if ($points->hasProgPoints()) {
				$progPoints += $points->countProgPoints();
				$maxProgPoints += $taskSet->getMaxProgPoints();
			}
		}
		if ($maxDiscPoints == 0 or ((float)$discPoints / (float)$maxDiscPoints >= 0.5)) {
			$discClass = "enoughpoints";
		} else {
			$discClass = "fewpoints";
		}
		if ($maxProgPoints == 0 or ((float)$progPoints / (float)$maxProgPoints > 0.66)) {
			$progClass = "enoughpoints";
		} else {
			$progClass = "fewpoints";
		}
		echo "\t\t<td class=\"$discClass\">$discPoints</td>\n";
		echo "\t\t<td class=\"$progClass lastcell\">$progPoints</td>\n";
		
		foreach ($taskSets as $taskSet) {
			$points = $student->getPoints($taskSet);
			if ($user['rights'] > 1 and (!($points->hasDiscPoints() and
					$points->hasProgPoints()) or ($selEditId == $student->getID()
					and $selEditTaskSet == $taskSet->getID()))) {
				
				echo "\t\t<form action=\"?page=points&amp;action=setpoints\"" .
					 " method=\"post\">\n";
				echo "\t\t\t<input type=\"hidden\" name=\"task_set_id\" value=";
				echo "\"" . $taskSet->getID() . "\" />\n";
				echo "\t\t\t<input type=\"hidden\" name=\"student_id\" value=";
				echo "\"" . $student->getID() . "\" />\n";
				
				for ($i = 0; $i < $taskSet->getNumDiscTasks(); $i++) {		
					echo "\t\t\t<td";
					if ($points->getDiscPoints($i) === NULL) {
						echo " class=\"unset\"";
					}
					echo "><input type=\"checkbox\" name=\"disc$i\"";
					if ($points->getDiscPoints($i) > 0) {
						echo " checked=\"checked\"";
					}
					echo " value=\"1\" /></td>\n";
				}
				for ($i = 0; $i < $taskSet->getNumProgTasks(); $i++) {
					$progPoints = $points->getProgPoints($i);
					if ($progPoints == -1) $progPoints = "";
					echo "\t\t\t<td><input type=\"number\" min=\"0\" step=\"0.5\" name=\"prog$i\"";
					echo "value=\"$progPoints\" size=\"2\" /></td>\n";
				}
				echo "\t\t\t<td class=\"lastcell\"><input type=\"submit\" value=\"set\" />";
				echo "<a class=\"tableButton\" href=\"?page=revisions&amp;id=" . $student->getID();
				echo "&amp;showset=" . $taskSet->getID() . "\">⇡</a>";
				echo "<span class=\"revision\">" . $points->getRevisionData()->getNumber() . "</span>";
				
				echo "</td>\n";
				echo "\t\t</form>\n";
				
			} else {
				for ($i = 0; $i < $taskSet->getNumDiscTasks(); $i++) {
					if ($points->getDiscPoints($i) === NULL) {
						echo "\t\t<td class=\"unset\"></td>\n";
					} else {
						echo "\t\t<td>";
						if ($points->getDiscPoints($i) > 0) {
							echo "✓";
						}
						echo "</td>\n";
					}
				}
				for ($i = 0; $i < $taskSet->getNumProgTasks(); $i++) {
					$classes = "";
					if ($points->getProgPoints($i) === NULL) {
						$classes .= "unset";
						echo "\t\t<td class=\"$classes\"></td>\n";
					} else {
						echo "\t\t<td";
						if ($classes != "") {
							echo " class=\"$classes\"";
						}
						echo ">" . $points->getProgPoints($i) . "</td>\n";
					}
				}
				
				echo "\t\t<td class=\"lastcell\">";
				if ($user['rights'] > 1) {
					echo "<a class=\"tableButton\" href=\"?page=points&amp;edit_id=";
					echo $student->getID() . "&amp;edit_taskset=" . $taskSet->getID();
					if ($selForce) echo "&amp;force=";
					echo "\">✎</a>&nbsp;";
				}
				echo "<a class=\"tableButton\" href=\"?page=revisions&amp;id=" . $student->getID();
				echo "&amp;showset=" . $taskSet->getID() . "\">⇡</a>";
				echo "<span class=\"revision\">" . $points->getRevisionData()->getNumber() . "</span>";
				echo "</td>\n";
			}
		}
		
		echo "\t</tr>\n";
	}
	
	// form for setting points to a whole team
	if (isset($selTeamnum) and count($students) > 0 and $user['rights'] > 1) {
		echo "\t<tr>\n";
		echo "\t\t<td colspan=\"6\" class=\"lastcell\">Punkte für Team $selTeamnum setzen:</td>\n";
		foreach($taskSets as $taskSet) {
			for ($i = 0; $i < $taskSet->getNumDiscTasks(); $i++) {
				echo "\t\t<td class=\"black\"></td>\n";
			}
			echo "\t\t<form action=\"?page=points&amp;action=setpoints\" method=\"post\">\n";
			echo "\t\t\t<input type=\"hidden\" name=\"task_set_id\" value=";
			echo "\"" . $taskSet->getID() . "\" />\n";
			echo "\t\t\t<input type=\"hidden\" name=\"team_num\" value=\"$selTeamnum\" />\n";
			
			for ($i = 0; $i < $taskSet->getNumProgTasks(); $i++) {
				echo "\t\t\t<td><input type=\"text\" name=\"prog$i\"";
				echo "size=\"2\" /></td>\n";
			}
			echo "\t\t\t<td class=\"lastcell\"><input type=\"submit\" value=\"set\" /></td>\n";
			echo "\t\t</form>\n";
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
		<input type="hidden" name="page" value="points" />
		<input type="hidden" name="force" value="" />
		<span class="formsubmit"><input type="submit" value="<?php echo $forceLabel; ?> (ADMIN)" /></span>
	</form>
<?php } ?>
</div>
<?php } ?>