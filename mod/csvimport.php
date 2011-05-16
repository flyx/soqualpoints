<?php
	// writes log to output, formats everything enclosed in 'ticks' bold.
	function wlog ($string) {
		$switch = 0;
		$arr = explode("'", $string);
		$output = "";
		foreach($arr as $curstr) {
			if ($switch == 1) {
				$output .= "<strong>";
				$switch = 2;
			} else {
				if ($switch == 2) {
					$output .= "</strong>";
				}
				$switch = 1;
			}
			$output .= $curstr;
		}
		echo $output;
	}
	
	// writes log to output and appends a <br />
	function logln ($string) {
		wlog($string);
		echo "<br />\n";
	}
	
	// for errors
	function redlogln ($string) {
		echo "<span style=\"color:#900\">";
		wlog($string);
		echo "</span><br />\n";
	}
	
if ($user['rights'] < 3) {
	echo "<strong>Diese User hat keine Administrationsberechtigung.</strong>";
} else {
	
	$skipcols = (bool)$_POST['skipcols'];
	$delfields = (bool)$_POST['delfields'];
	
	if (is_uploaded_file($_FILES['csvfile']['tmp_name'])) {
		if ($handle = fopen($_FILES['csvfile']['tmp_name'], "r")) {
			$taskSets = DB::get_tasks(NULL, NULL, NULL, NULL, NULL);
			
			while ($data = fgetcsv($handle)) {
				$num = count($data);
				
				// -4 - -1: student data; 0,1: not parsed; 2: disc data; 3: prog data
				$state = -4;
				$newStudent = array();
				$student = NULL;
				$error = false;
				
				for ($c = 0; $c < $num and !$error; $c++) {
					switch($state) {
						case -4: $newStudent['team_number'] = $data[$c]; $state++; break;
						case -3: $newStudent['surname'] = $data[$c]; $state++; break;
						case -2: $newStudent['forename'] = $data[$c]; $state++; break;
						case -1: $newStudent['student_number'] = $data[$c];
							if ($newStudent['surname'] == "" or $newStudent['forename'] == "") {
								redlogln("'forename or surname not set, skipping line.'");
								$error = true;
							} else {
								if ((int)$newStudent['student_number'] > 0) {
									$availStuds = DB::get_students(NULL, $newStudent['forename'], $newStudent['surname'], $newStudent['student_number'], NULL);
									if (count($availStuds) > 1) {
										$error = true;
										redlogln("found more than one hit for student number '" .
												$newStudent['student_number'] . "', skipping line.");
									}	
								} else {
									$availStuds = DB::get_students(NULL, $newStudent['forename'], $newStudent['surname'], NULL, NULL);
									if (count($availStuds) > 1) {
										$error = true;
										redlogln("found more than one hit for '" . $newStudent['forename'] .
												" " . $newStudent['surname']. "', skipping line.");
									}
								}
								if (!$error) {
									if (count($availStuds) == 1) {
										$student = $availStuds[0];
										wlog("found student '" . $student->getForename() . " " .
												$student->getSurname() . "'...");
									} else {
										$student = new Student($newStudent, True);
										wlog("added student '" . $student->getForename() . " " .
												$student->getSurname() . "'...");
									}
									$curTaskSet = 0;
									$curIndex = 0;
								}
							}
							if (!$skipcols) {
								$state = 1;
							}
						case 0:
						case 1: $state++; break;
						case 2:
							if ($curIndex == 0) {
								if ($taskSets[$curTaskSet] != NULL) {
									$student->loadPoints($taskSets[$curTaskSet]);
									$points = $student->getPoints($taskSets[$curTaskSet]);
								} else {
									$error = true;
									redlogln("'not enough task sets in database, finishing line.'");
									break;
								}
							}
							if ($data[$c] == "") {
								if ($delfields) {
									$points->setDiscPoints($curIndex, NULL);
								}
							} else {
								$points->setDiscPoints($curIndex, (float)strtr($data[$c], ",","."));
							}
							$curIndex++;
							if ($curIndex >= $taskSets[$curTaskSet]->getNumDiscTasks()) {
								$state = 3;
								$curIndex = 0;
							}
							break;
						case 3:
							if ($data[$c] == "") {
								if ($delfields) {
									$points->setProgPoints($curIndex, NULL);
								}
							} else {
								$points->setProgPoints($curIndex, (float)strtr($data[$c], ",","."));
							}
							$curIndex++;
							if ($curIndex >= $taskSets[$curTaskSet]->getNumProgTasks()) {
								$state = 2;
								$curIndex = 0;
								$curTaskSet++;
								$revData = $points->getRevisionData();
								if ($revData != NULL) {
									$points->storeNewRevision($revData->getNumber() + 1,
											$user['id']);
								} else {
									$points->storeNewRevision(1, $user['id']);
								}
								wlog ("updated points in '" . $taskSets[$curTaskSet-1]->getName() . "'...");
							}
							break;
					}
				}
				if (!$error) {
					logln("line finished without errors.");
				}
			}
		} else {
			redlogln("'Die hochgeladene Datei konnte nicht geöffnet werden.'");
		}
	} else {
		redlogln("'Es wurde keine Datei hochgeladen.'");
	}
}
?>