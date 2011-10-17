<?php
	require_once("user.php");

	function doAction($action) {
		global $message;
		global $page;
		global $user;
		
		switch ($action) {
			case 'addstudent':
				if ($_POST['forename'] != "" and $_POST['surname'] != "") {
					$newStudent['forename'] = htmlspecialchars($_POST['forename'],
							ENT_QUOTES, "UTF-8");
					$newStudent['surname'] = htmlspecialchars($_POST['surname'],
							ENT_QUOTES, "UTF-8");
					$newStudent['student_number'] = (int)$_POST['student_number'];
					$newStudent['team_number'] = (int)$_POST['team_number'];
					$student = new Student($newStudent, True);
					$message = 'Neuen Student hinzugefügt';
				} else {
					$message = 'Mindestens Vor- und Nachname müssen bekannt sein!';
				}
				break;
			case "updatestudent":
				if ($_POST['forename'] != "" and $_POST['surname'] != "" and $_POST['id'] != "") {
					// get updated student data from post
					$forename = htmlspecialchars($_POST['forename'], ENT_QUOTES,
							"UTF-8");
					$surname = htmlspecialchars($_POST['surname'], ENT_QUOTES,
							"UTF-8");
					$studNum = (int)$_POST['student_number'];
					$teamNum = (int)$_POST['team_number'];
					$studId = (int)$_POST['id'];
					
					// get student from database
					$studentArray = DB::get_students($studId, NULL, NULL, NULL, NULL);
					$student = $studentArray[$studId];
					if ($student != NULL) {
						$student->updateData($forename, $surname, $studNum, $teamNum);
						$message = 'Studentdaten aktualisiert.';
					} else {
						$message = 'Fehler: Student nicht gefunden.';
					}
				} else {
					$message = 'Mindestens Vor- und Nachname müssen angegeben sein!';
				}
				break;
			case "delstudent":
				if ($_POST['id'] != "") {
					$studId = (int)$_POST['id'];
					if (isset($_POST['confirm'])) {
						DB::delete_student($studId);
						// delete all points of this student
						DB::query("DELETE FROM `points` WHERE student_id=" .
								 $studId);
						DB::query("DELETE FROM `revisions` WHERE student_id=" .
								 $studId);
						$message = "Student wurde gelöscht.";
					} else {
						$confirm = "<div id=\"fadingcontent\" style=\"height: 7em; padding: 1em;\">\n";
						$confirm .= "<strong>Student wirklich löschen?</strong>";
						$confirm .= "<br />\n";
						$confirm .= "<div class=\"confbut\">";
						$confirm .= "<form action=\"?page=$page&amp;action=delstudent\"";
						$confirm .= " method=\"post\">\n";
						$confirm .= "\t<input type=\"hidden\" name=\"id\" value=\"";
						$confirm .= $studId . "\" />\n";
						$confirm .= "\t<input type=\"hidden\" name=\"confirm\" ";
						$confirm .= "value=\"x\" />\n";
						$confirm .= "\t<input type=\"submit\" value=\"Ja\" />\n";
						$confirm .= "</form></div>\n";
						$confirm .= "<div class=\"confbut\">";
						$confirm .= "<form action=\"\" method=\"get\">\n";
						$confirm .= "\t<input type=\"hidden\" name=\"page\" value=\"";
						$confirm .= $page . "\" />\n";
						$confirm .= "\t<input type=\"submit\" value=\"Nein\" />\n";
						$confirm .= "</form></div>\n</div>\n";
						return $confirm;
					}	
				} else {
					$message = 'Fehler: Keine id gegeben.';
				}
				break;
			case 'addset':
				if ($_POST['max_prog_points'] != "" and
						$_POST['count_prog_tasks'] != "" and
						$_POST['count_disc_tasks'] != "" and
						$_POST['name'] != "") {
					$newSet['max_prog_points'] = (int)$_POST['max_prog_points'];
					$newSet['count_prog_tasks'] = (int)$_POST['count_prog_tasks'];
					$newSet['count_disc_tasks'] = (int)$_POST['count_disc_tasks'];
					$newSet['name'] = htmlspecialchars($_POST['name'],
							ENT_QUOTES, "UTF-8");
					$sum = $newSet['count_prog_tasks'] + $newSet['count_disc_tasks'];
					if ($sum > 0 and $sum <= 50) {
						$taskSet = new TaskSet($newSet, True);
						$message = 'Neues Aufgabenblatt hinzugefügt';
					} else {
						$message = 'Ein Aufgabenblatt muss mindestens eine ' .
								'und höchstens 50 Aufgaben haben.';
					}
				} else {
					$message = 'Aufgabenblatt nicht hinzugefügt: Angaben ungenügend.';
				}
				break;
			case 'updateset':
				if ($_POST['max_prog_points'] != "" and
						$_POST['count_prog_tasks'] != "" and
						$_POST['count_disc_tasks'] != "" and
						//$_POST['name'] != "" and
						$_POST['id'] != "") {
					$mpp = (int)$_POST['max_prog_points'];
					$cpt = (int)$_POST['count_prog_tasks'];
					$cdt = (int)$_POST['count_disc_tasks'];
					$name = htmlspecialchars($_POST['name'], ENT_QUOTES, "UTF-8");
					$id = (int)$_POST['id'];
					$taskArray = DB::get_tasks($id, NULL, NULL, NULL, NULL);
					$taskSet = $taskArray[$id];
					if ($taskSet != NULL) {
						if ($cpt + $cdt > 0 and $cpt + $cdt <= 50) {
							$taskSet->updateData($mpp, $cpt, $cdt, $name);
							$message = "Aufgabenblattdaten aktualisiert.";
						} else {
							$message = 'Ein Aufgabenblatt muss mindestens eine ' .
								'und höchstens 50 Aufgaben haben.';
						}
					} else {
						$message = "Aufgabenblatt nicht gefunden.";
					}
				} else {
					$message = "Ungenügende Angaben.";
				}
				break;
			case 'delset':
				if ($_POST['id'] != "") {
					$setId = (int)$_POST['id'];
					if (isset($_POST['confirm'])) {
						DB::delete_taskSet($setId);
						
						// delete all points for that task set
						DB::query("DELETE FROM `points` WHERE task_set_id=" .
								 $setId);
						DB::query("DELETE FROM `revisions` WHERE task_set_id=" .
								 $setId);
						$message = "Aufgabenblatt wurde gelöscht.";
					} else {
						$confirm = "<div id=\"fadingcontent\" style=\"height: 7em; padding: 1em;\">\n";
						$confirm .= "<strong>Aufgabenblatt wirklich löschen?</strong>";
						$confirm .= "<br />\n";
						$confirm .= "<div class=\"confbut\">";
						$confirm .= "<form action=\"?page=$page&amp;action=delset\"";
						$confirm .= " method=\"post\">\n";
						$confirm .= "\t<input type=\"hidden\" name=\"id\" value=\"";
						$confirm .= $setId . "\" />\n";
						$confirm .= "\t<input type=\"hidden\" name=\"confirm\" ";
						$confirm .= "value=\"x\" />\n";
						$confirm .= "\t<input type=\"submit\" value=\"Ja\" />\n";
						$confirm .= "</form></div>\n";
						$confirm .= "<div class=\"confbut\">";
						$confirm .= "<form action=\"\" method=\"get\">\n";
						$confirm .= "\t<input type=\"hidden\" name=\"page\" value=\"";
						$confirm .= $page . "\" />\n";
						$confirm .= "\t<input type=\"submit\" value=\"Nein\" />\n";
						$confirm .= "</form></div>\n</div>\n";
						return $confirm;
					}	
				} else {
					$message = 'Fehler: Keine id gegeben.';
				}
				break;
			case 'setpoints':
				if (isset($_POST['task_set_id'])) {
				
					// the TaskSet is needed to know how much tasks there are,
					// so load it first
					$setId = (int)$_POST['task_set_id'];
					$taskArray = DB::get_tasks($setId, NULL, NULL, NULL, NULL);
					$taskSet = $taskArray[$setId];			
					if ($taskSet != NULL) {
						$pointList = array();
						
						if (isset($_POST['student_id'])) {
							$teamPoints = False;
						
							// set points for a single student
							$studId = (int)$_POST['student_id'];
							$revNum = DB::recentRevisionNumber($studId, $taskSet->getID());
							$points = new TaskSetPoints($studId, $taskSet, $revNum);
							$pointList[] = $points; 
							
							// set discussion points only for single student
							for ($i = 0; $i < $taskSet->getNumDiscTasks(); $i++) {
								// post-value won't be set when checkbox is disabled
								// so set the points to 0 if there is no such post-value
								
								if (isset($_POST['disc' . $i])) {
									$value = strtr($_POST['disc' . $i], ",", ".");
									$points->setDiscPoints($i, (int)$value);
								} else {
									$points->setDiscPoints($i, 0);
								}
							}
						} else if ($_POST['team_num'] != "") {
							$teamPoints = True;
							$students = DB::get_students(NULL, NULL, NULL, NULL,
									(int)$_POST['team_num']); 
							foreach($students as $student) {
								$revNum = DB::recentRevisionNumber($student->getID(), $taskSet->getID());
								$pointList[] = new TaskSetPoints($student->getID(), $taskSet, $revNum);
							}
						} else {
							$message = "Student oder Team müssen angegeben sein";
						}
							
						foreach($pointList as $points) {
							
							// set programming points for single student or team
							for ($i = 0; $i < $taskSet->getNumProgTasks(); $i++) {
								$value = strtr($_POST['prog' . $i], ",", ".");
								if (!($teamPoints and $value=="")) {
									if ($value == "") {
										$points->setProgPoints($i, NULL);
									} else {
										$points->setProgPoints($i, (float)$value);
									}
								}
							}
						
							
							// write the changes into the database
							$revData = $points->getRevisionData();
							if ($revData != NULL) {						
								$points->storeNewRevision($revData->getNumber() + 1,
										$user['id']);
							} else {
								$points->storeNewRevision(1, $user['id']);
							}
						}
						if (!isset($message)) {
							$message = 'Punkte wurden gespeichert.';
						}
					} else {
						$message = 'Angegebenes Aufgabenblatt existiert nicht.';
					}
				} else {
					$message = 'Aufgabenblatt oder Student wurden nicht angegeben';
				}
				break;
			case 'adduser':
				$un = $_POST['username'];
				$pw = $_POST['password'];
				$ri = (int)$_POST['rights'];
				if ($user['rights'] >= 3) {
					if ($un != "" and $pw != "" and $ri > 0) {
						createUser($un, $pw, $user['id'], $ri);
						$message = 'User hinzugefügt';
					} else {
						$message = 'User nicht hinzugefügt: Daten ungenügend.';
					}
				} else {
					$message = 'Für diese Aktion sind Administratorrechte notwendig.';
				}
				break;
			case 'updateuser':
				
				if ($_POST['id'] != "") {
					if ($user['rights'] >= 3) {
						$id = (int)$_POST['id'];
						
						if (isset($_POST['approve'])) {
							if (approveUser($id, $user['id'])) {
								$message = 'User freigeschaltet.';
							} else {
								$message = 'Eingabe feherhaft.';
							}
						} else {
							$ri = (int)$_POST['rights'];
							$pw = $_POST['new_password'];
							$message = "";
							if ($ri > 0) {
								if ($id == 1) {
									$message .= "Die Rechte des Primärusers können nicht" .
											" geändert werden.";
								} else {
									if (updateUserRights($id, $ri)) {
										$message .= "Userrechte geändert.";
									}
								}
							}
							if ($pw != "") {
								if (updateUserPw($id, $pw, $user['id'])) {
									if ($message != "") {$message .= "<br />";}
									$message .= "Userpasswort geändert.";
								}
							}
							if ($message == "") {
								$message = "Nichts geändert - Eingabe fehlerhaft.";
							}
						}
					}
				} else {
					$message = "Keine ID gegeben.";
				}
				break;
			case 'deluser':
				if ($_POST['id'] != "") {
					$userId = (int)$_POST['id'];
					if ($userId == 1) {
						$message = "Der Primäruser kann nicht gelöscht werden.";
					} else {
						if (isset($_POST['confirm'])) {
							$query = "DELETE FROM `users` WHERE id=$userId";
							DB::query($query);
	
							$message = "User wurde gelöscht.";
						} else {
							$confirm = "<div id=\"fadingcontent\" style=\"height: 7em; padding: 1em;\">\n";
							$confirm .= "<strong>User wirklich löschen?</strong>";
							$confirm .= "<br />\n";
							$confirm .= "<div class=\"confbut\">";
							$confirm .= "<form action=\"?page=$page&amp;action=deluser\"";
							$confirm .= " method=\"post\">\n";
							$confirm .= "\t<input type=\"hidden\" name=\"id\" value=\"";
							$confirm .= $userId . "\" />\n";
							$confirm .= "\t<input type=\"hidden\" name=\"confirm\" ";
							$confirm .= "value=\"x\" />\n";
							$confirm .= "\t<input type=\"submit\" value=\"Ja\" />\n";
							$confirm .= "</form></div>\n";
							$confirm .= "<div class=\"confbut\">";
							$confirm .= "<form action=\"\" method=\"get\">\n";
							$confirm .= "\t<input type=\"hidden\" name=\"page\" value=\"";
							$confirm .= $page . "\" />\n";
							$confirm .= "\t<input type=\"submit\" value=\"Nein\" />\n";
							$confirm .= "</form></div>\n</div>\n";
							return $confirm;
						}	
					}
				} else {
					$message = 'Fehler: Keine id gegeben.';
				}
				break;
		}
	}
?>