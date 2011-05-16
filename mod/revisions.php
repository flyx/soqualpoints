<?php
	require("searchform.php");
if (getTableVisibility()) { ?>
<table id="contenttable">
	<thead>
	<tr>
		<th rowspan="3" class="lastheading">Vorname</th>
		<th rowspan="3" class="lastheading">Nachname</th>
		<th rowspan="3" class="lastheading"><abbr title="Matrikelnummer">Mat.nr.</abbr></th>
		<th rowspan="3" class="lastheading lastcell">Team</th>
<?php
	foreach($taskSets as $taskSet) {
		echo "\t\t<th ";
		echo "colspan=\"". ($taskSet->getNumDiscTasks() +
		$taskSet->getNumProgTasks()) ."\">" . $taskSet->getName() . "</th>\n";
		echo "\t\t<th class=\"lastcell\" rowspan=\"2\" colspan=\"3\">Revisionen</th>\n";
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
	}
?>
	</tr>
	<tr>
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
		}?>
		
		<th class="lastheading">Num.</th>
		<th class="lastheading">User</th>
		<th class="lastheading lastcell">Timestamp</th>
<?php }
?>
	</tr>
	</thead>
	<tbody>
<?php
	$teamLight = false;
	$users = DB::get_users();
	foreach($students as $student) {
		$teamLight = !$teamLight;
		if ($teamLight) {
			echo "\t<tr class=\"lightbg\">\n";
		} else {
			echo "\t<tr>\n";
		}
		echo "\t\t<td>" . $student->getForename() . "</td>\n";
		echo "\t\t<td>" . $student->getSurname() . "</td>\n";
		echo "\t\t<td>" . $student->getStudNum() . "</td>\n";
		echo "\t\t<td class=\"lastcell\">" . $student->getTeamNum() . "</td>\n";
	
		$revisions = DB::get_all_revisions($student->getID(), $taskSets);
		$maxRevisions = 1;
		$curRevNum = array();
		foreach ($revisions as $index => $revisionSet) {
			$curRevNum[$index] = count($revisionSet);
			if ($maxRevisions < $curRevNum[$index]) {
				$maxRevisions = $curRevNum[$index];
			}
		}
		for($i = 0; $i < $maxRevisions; $i++) {
			foreach($taskSets as $taskSet) {
				if ($curRevNum[$taskSet->getID()] > 0) {
					$points = $revisions[$taskSet->getID()][$curRevNum[$taskSet->getID()]--];
					for ($j = 0; $j < $taskSet->getNumDiscTasks(); $j++) {
					    if ($points->getDiscPoints($i) == -1) {
					    	echo "\t\t<td class=\"unset\"></td>\n";
					    } else {
					    	echo "\t\t<td>";
					    	if ($points->getDiscPoints($j) > 0) {
					    		 echo "✓";
					    	}
					    	echo "</td>\n";
					    }
					}
					for ($j = 0; $j < $taskSet->getNumProgTasks(); $j++) {
					    if ($points->getProgPoints($j) == -1) {
					    	echo "\t\t<td class=\"unset\"></td>\n";
					    } else {
					    	echo "\t\t<td>" . $points->getProgPoints($j) . "</td>\n";
					    }
					}
					echo "\t\t<td><span class=\"revision\">" . $points->getRevisionData()->getNumber() . "</span></td>\n";
					echo "\t\t<td>" . $users[$points->getRevisionData()->getUserId()]['name'] . "</td>\n";
					echo "\t\t<td class=\"lastcell\">" . $points->getRevisionData()->getTimestamp() . "</td>\n";
				} else {
					echo "\t\t<td class=\"lastcell\" colspan=\"" .
							($taskSet->getNumDiscTasks() + $taskSet->getNumProgTasks() + 3) .
							"\"></td>\n";
				}
			}
			echo "\t</tr>";
			if ($i < $maxRevisions - 1) {
				if ($teamLight) {
					echo "\t<tr class=\"lightbg\">\n";
				} else {
					echo "\t<tr>\n";
				}
				echo "\t\t<td class=\"lastcell\" colspan=\"4\"></td>";
			}
		}
	}?>
	</tbody>
</table>
<?php } else { ?>
<div id="contentinfo">
	<p><?php echo $contentInfoLabel; ?></p>
<?php if ($allowForce) { ?>
	<form action="" method="get">
		<input type="hidden" name="page" value="revisions" />
		<input type="hidden" name="force" value="" />
		<span class="formsubmit"><input type="submit" value="<?php echo $forceLabel; ?> (ADMIN)" /></span>
	</form>
<?php } ?>
</div>
<?php } ?>