<?php
	require_once("debuglog.php");
	require_once("backend.php");
	require_once("user.php");

	class DB {
		private static $handle;
		private static $encrypted;
		private static $pw;
		private static $available = False;
		
		public static function init(
				$dbHost, $dbUser, $dbPW, $dbName, $isEncrypted) {
			if (!DB::$handle = @mysql_connect($dbHost, $dbUser, $dbPW)) {
				debugLog('DB init', 'The MySQL-server isn\'t available.', True);
			} else {
				DB::$available = True;
			}
			if (!mysql_query("SET NAMES 'utf8'")) {
				debugLog('DB init', 'Error for SET NAMES \'utf8\': ' . mysql_error(), True);
			}
			if (!mysql_select_db($dbName)) {
				debugLog('DB init', 'Error while connecting to database: ' . mysql_error(), True);
				DB::$available = False;
			}
			DB::$encrypted = $isEncrypted;
			
			// as the master password is stored in the database encrypted with
			// the user password, we can't set it now.
			DB::$pw = "";
		}
		
		public static function isAvailable() {
			return DB::$available;
		}
		
		public static function isEncrypted() {
			return DB::$encrypted;
		}
		
		public static function query($query) {
			if (!DB::$available) {
				debugLog('DB query', 'called query on uninitialized database driver', True);
				return NULL;
			}
			// debug log might expose the master key!
			// be sure to turn it off in a production environment.
			
			if (!$result = mysql_query($query, DB::$handle)) {
				debugLog('DB query', $query);
				debugLog('DB error', mysql_error(), True);
				return NULL;
			} else {
				if (substr($query, 0, 6) == 'SELECT') {
					debugLog('DB query', $query . " <strong>returned " . mysql_num_rows($result) . " rows</strong>");
				} else {
					debugLog('DB query', $query);
				}
			}
					
			return $result;
		}
		
		public static function setMasterPassword($mPW) {
			DB::$pw = $mPW;
		}	
		
		// add a bool expression to a string containing a where condition
		protected static function addCondition(&$whereCond, $newCond) {
			if ($whereCond != "") {
				$whereCond .= " AND";
			}
			$whereCond .= " $newCond";
		}
		
		protected static function addSet(&$setExpr, $newSet) {
			if ($setExpr != "") {
				$setExpr .= ", ";
			}
			$setExpr .= $newSet;
		}
		
		// generate a key/value pair for a where condition
		// depending on encryption settings. escapes the value.
		// only for strings.
		protected static function genPair($field, $value, $checkEncryption) {
			$mysql_str = mysql_real_escape_string($value);
			if ($checkEncryption && DB::$encrypted) {
				return "$field=AES_ENCRYPT(\"$mysql_str\", \"" . DB::$pw .
						"\")";
			} else {
				return "$field=\"$mysql_str\"";
			}
		}
		
		// decrypt a string with the master password
		protected static function decrypt($string) {
			$query = "SELECT AES_DECRYPT(\"" . mysql_real_escape_string($string) .
					"\", \"" . DB::$pw . "\") AS value;";
			$result = DB::query($query);
			$row = mysql_fetch_assoc($result);
			return $row['value'];
		}
		
		protected static function genFilter($name, $value) {
			$mysql_val = mysql_real_escape_string($value);
			if (DB::$encrypted) {
				return "CAST(AES_DECRYPT($name, \"" . DB::$pw . "\") AS CHAR) LIKE \"%$mysql_val%\"";
			} else {
				return "CAST($name AS CHAR) LIKE \"%$mysql_val%\"";
			}
		}
		
		protected static function genStudentSelect() {
			if (DB::$encrypted) {
				return "SELECT AES_DECRYPT(forename, \"" . DB::$pw . "\") AS plain_forename, " .
						"AES_DECRYPT(surname, \"" . DB::$pw . "\") AS plain_surname, " .
						"AES_DECRYPT(student_number, \"" . DB::$pw . "\") AS plain_studnum, " .
						"team_number, id FROM `students`";
			} else {
				return "SELECT * FROM `students`";
			}
		}
		
		public static function filter_students(
				$forename, $surname, $studNum, $teamNum) {
			$whereCond = "";
			
			if (isset($forename)) {
				DB::addCondition($whereCond,
						DB::genFilter("forename", $forename));
			}
			if (isset($surname)) {
				DB::addCondition($whereCond,
						DB::genFilter("surname", $surname));	
			}
			if (isset($studNum)) {
				DB::addCondition($whereCond,
						DB::genFilter("student_number", $studNum));	
			}
			if (isset($teamNum) && $teamNum != -1) {
				// team number has to match exactly.
				DB::addCondition($whereCond, "team_number=$teamNum");
			}
			$query = DB::genStudentSelect();
			if ($whereCond != "") {
				$query .= " WHERE $whereCond";
			}
			$query .= " ORDER BY team_number ASC;";
			$result = DB::query($query);
			$retArray = array();
			while ($row = mysql_fetch_assoc($result)) {
				$retArray[$row['id']] = new Student($row, False);
			}
			return $retArray;
		}
	
		public static function get_students($id, $forename, $surname, $studNum, $teamNum) {
			$whereCond = "";
			if (isset($id) && $id != -1) {
				DB::addCondition($whereCond, "id=$id");
			}
			if (isset($forename)) {
				DB::addCondition($whereCond,
						DB::genPair("forename", $forename, True));
			}
			if (isset($surname)) {
				DB::addCondition($whereCond,
						DB::genPair("surname", $surname, True));	
			}
			if (isset($studNum)) {
				DB::addCondition($whereCond,
						DB::genPair("student_number", $studNum, True));	
			}
			if (isset($teamNum) && $teamNum != -1) {
				DB::addCondition($whereCond, "team_number=$teamNum");
			}
			$query = DB::genStudentSelect();
			if ($whereCond != "") {
				$query .= " WHERE $whereCond";
			}
			$query .= " ORDER BY team_number ASC;";
			$result = DB::query($query);
			$retArray = array();
			while ($row = mysql_fetch_assoc($result)) {
				$retArray[$row['id']] = new Student($row, False);
			}
			return $retArray;
		}
					
		public static function insert_student(
				$forename, $surname, $studNum, $teamNum) {
			$query = "INSERT INTO `students` SET ";
			$setExpr = "";
			if (isset($forename)) {
				DB::addSet($setExpr, DB::genPair("forename", $forename, True));
			}
			if (isset($surname)) {
				DB::addSet($setExpr, DB::genPair("surname", $surname, True));
			}
			if (isset($studNum)) {
				DB::addSet($setExpr, DB::genPair("student_number", $studNum, True));
			}
			if (isset($teamNum)) {
				DB::addSet($setExpr, "team_number=$teamNum");
			}
			$query .= $setExpr;
			DB::query($query);
			return mysql_insert_id();
		}
		
		public static function update_student(
				$id, $forename, $surname, $studNum, $teamNum) {
			$query = "UPDATE `students` SET ";
			$setExpr = "";
			if (isset($forename)) {
				DB::addSet($setExpr, DB::genPair("forename", $forename, True));
			}
			if (isset($surname)) {
				DB::addSet($setExpr, DB::genPair("surname", $surname, True));
			}
			if (isset($studNum)) {
				DB::addSet($setExpr, DB::genPair("student_number", $studNum, True));
			}
			if (isset($teamNum)) {
				DB::addSet($setExpr, "team_number=$teamNum");
			}
			$query .= $setExpr;
			$query .= " WHERE id=$id;";
			DB::query($query);
		}
		
		public static function delete_student($id) {
			$query = "DELETE FROM `students` WHERE id=$id;";
			DB::query($query);
		}
		
		public static function get_tasks(
				$id, $name, $maxProgPoints, $countProgTasks, $countDiscTasks) {
			$query = "SELECT * FROM `task_sets`";
			$whereCond = "";
			if (isset($id)) {
				DB::addCondition($whereCond, "id=$id");
			}
			if (isset($name)) {
				DB::addCondition($whereCond, DB::genPair("name", $name, False));
			}
			if (isset($maxProgPoints)) {
				DB::addCondition($whereCond, "max_prog_points=$maxProgPoints");
			}
			if (isset($countProgTasks)) {
				DB::addCondition($whereCond, "count_prog_tasks=$countProgTasks");
			}
			if (isset($countDiscTasks)) {
				DB::addCondition($whereCond, "count_disc_tasks=$countDiscTasks");
			}
			if ($whereCond != "") {
				$query .= " WHERE $whereCond;";
			}
			$result = DB::query($query);
			$retArray = array();
			while ($row = mysql_fetch_assoc($result)) {
				$retArray[$row['id']] = new TaskSet($row, False);
			}
			return $retArray;
		}
		
		public static function insert_taskSet(
				$name, $maxProgPoints, $countProgTasks, $countDiscTasks) {
			$query = "INSERT INTO `task_sets` SET ";
			$setExpr = "";
			if (isset($name)) {
				DB::addSet($setExpr, DB::genPair("name", $name, False));
			}
			if (isset($maxProgPoints)) {
				DB::addSet($setExpr, "max_prog_points=$maxProgPoints");
			}
			if (isset($countProgTasks)) {
				DB::addSet($setExpr, "count_prog_tasks=$countProgTasks");
			}
			if (isset($countDiscTasks)) {
				DB::addSet($setExpr, "count_disc_tasks=$countDiscTasks");
			}
			$query .= $setExpr. ";";
			DB::query($query);
			return mysql_insert_id();
		}
		
		public static function update_taskSet(
				$id, $name, $maxProgPoints, $countProgTasks, $countDiscTasks) {
			$query = "UPDATE `task_sets` SET ";
			$setExpr = "";
			if (isset($name)) {
				DB::addSet($setExpr, DB::genPair("name", $name, False));
			}
			if (isset($name)) {
				DB::addSet($setExpr, "max_prog_points=$maxProgPoints");
			}
			if (isset($name)) {
				DB::addSet($setExpr, "count_prog_tasks=$countProgTasks");
			}
			if (isset($name)) {
				DB::addSet($setExpr, "count_disc_tasks=$countDiscTasks");
			}
			$query .= $setExpr . " WHERE id=$id;";
			DB::query($query);
		}
		
		public static function delete_taskSet($id) {
			$query = "DELETE FROM `task_sets` WHERE id=$id";
			DB::query($query);
		}
		
		public static function recentRevisionNumber($studentId, $taskSetId) {
			$query = "SELECT number FROM `revisions` WHERE student_id=" .
					"$studentId AND task_set_id=$taskSetId ORDER BY number" .
					" DESC LIMIT 1;";
			$result = DB::query($query);
			if ($row = mysql_fetch_assoc($result)) {
				$curRevision = $row['number'];
			} else {
				$curRevision = -1;
			}
			return $curRevision;
		}
		
		// this function returns the points matching all the given parameters
		// in an array.
		public static function get_points(
				$id, $studentId, $taskSetId, $revision) {
			$query = "SELECT * FROM `points`";
			$whereCond = ""; 
			if (isset($id)) {
				DB::addCondition($whereCond, "id=$id");
			}
			if (isset($studentId)) {
				DB::addCondition($whereCond, "student_id=$studentId");
			}
			if (isset($taskSetId)) {
				DB::addCondition($whereCond, "task_set_id=$taskSetId");
			}
			if (isset($revision) and $revision >= 0) {
				DB::addCondition($whereCond, "revision=$revision");
			}
			if ($whereCond != "") {
				$query .= " WHERE $whereCond";
			}
			
			$result = DB::query($query);
			$retArray = array();
			while ($row = mysql_fetch_assoc($result)) {
				$retArray[] = $row;
			}
			return $retArray;
		}
		
		// queries the most recent revision of all points for the given students
		// and task set (set $taskSetId = -1 to get points for all task sets)
		// the results are directly written into the student objects, no return value
		public static function get_current_points($students, $taskSets) {
			if (count($students) == 0 || count($taskSets) == 0)
				return;
			
			$query = "SELECT * FROM `points` p1 JOIN (";
			
			
			$query .= "SELECT MAX(number) AS revision, student_id, task_set_id FROM `revisions`";
			$studIds = array();
			foreach ($students as $student) {
				$studIds[] = $student->getID();
			}
			$query .= " WHERE student_id IN (" . implode(",", $studIds) . ")";
			$taskSetIds = array();
			foreach($taskSets as $taskSet) {
				$taskSetIds[] = $taskSet->getID();
			}
			$query .= " AND task_set_id IN (" . implode(",", $taskSetIds) . ")";
			$query .= " GROUP BY student_id, task_set_id";
			
			$query .= ") AS p2 ON p1.revision = p2.revision AND p1.student_id = p2.student_id AND p1.task_set_id = p2.task_set_id";
			$query .= " ORDER BY p1.student_id, p1.task_set_id;";
			
			$result = DB::query($query);
			
			$currentRevisions = DB::get_current_revisions($students, $taskSets);
			
			$curStudent = NULL;
			$curTaskSet = NULL;
			$curPointSet = array();
			
			while ($row = mysql_fetch_assoc($result)) {
				if ($curStudent === NULL) {
					$curStudent = $students[$row['student_id']];
				} else if ($curStudent->getID() != $row['student_id']) {
					$revision = $currentRevisions[$curStudent->getID()][$curTaskSet->getID()];
					$curStudent->loadPoints($curTaskSet, $curPointSet, $revision['number'], $revision);
					unset($currentRevisions[$curStudent->getID()][$curTaskSet->getID()]);
					if (count($currentRevisions[$curStudent->getID()]) == 0) {
						unset($currentRevisions[$curStudent->getID()]);
					}
					$curStudent = $students[$row['student_id']];
					$curPointSet = array();
					$curTaskSet = NULL;
				}
				if ($curTaskSet === NULL) {
					$curTaskSet = $taskSets[$row['task_set_id']];
				} else if ($curTaskSet->getID() != $row['task_set_id']) {
					$revision = $currentRevisions[$curStudent->getID()][$curTaskSet->getID()];
					$curStudent->loadPoints($curTaskSet, $curPointSet, $revision['number'], $revision);
					unset($currentRevisions[$curStudent->getID()][$curTaskSet->getID()]);
					if (count($currentRevisions[$curStudent->getID()]) == 0) {
						unset($currentRevisions[$curStudent->getID()]);
					}
					$curTaskSet = $taskSets[$row['task_set_id']];
					$curPointSet = array();
				}
				$curPointSet[] = $row;
			}
			$revision = $currentRevisions[$curStudent->getID()][$curTaskSet->getID()];
			$curStudent->loadPoints($curTaskSet, $curPointSet, $revision['number'], $revision);
			unset($currentRevisions[$curStudent->getID()][$curTaskSet->getID()]);
			if (count($currentRevisions[$curStudent->getID()]) == 0) {
				unset($currentRevisions[$curStudent->getID()]);
			}
			
			// initialize all points we didn't get any rows for
			foreach ($currentRevisions as $studentId => $studentRevisions) {
				foreach ($studentRevisions as $taskSetId => $taskSetRevision) {
					debugLog('DB points', 'data incomplete for student ' . $studentId . ", task set " . $taskSetId);
					if ($taskSetRevision === NULL) {
						$students[$studentId]->loadPoints($taskSets[$taskSetId], array(), -1, NULL);
					} else {
						$students[$studentId]->loadPoints($taskSets[$taskSetId], array(), $taskSetRevision['number'], $taskSetRevision);
					}
				}
			}
		}
		
		// this function will insert new points with the given revision number
		protected static function add_points(
				$studentId, $taskSetId, $kind, $index, $points, $revision) {
			$query = "INSERT INTO `points` SET student_id=$studentId, " .
					"task_set_id=$taskSetId, kind=\"$kind\", `index`=$index, " .
					"points=$points, revision=$revision";
			DB::query($query);
			return mysql_insert_id();
		}
		
		public static function add_revisionData(
				$number, $studentId, $taskSetId, $userId, &$timestamp) {
			$revision = DB::recentRevisionNumber($studentId, $taskSetId);
			$query = "SELECT NOW() AS timestamp;";
			$result = DB::query($query);
			$row = mysql_fetch_assoc($result);
			$timestamp = $row['timestamp'];
			
			$query = "INSERT INTO `revisions` SET number=$number, " .
					 "student_id=$studentId, task_set_id=$taskSetId, " .
					 "user_id=$userId, timestamp=\"$timestamp\";";
			DB::query($query);
			return mysql_insert_id();
		}
		
		public static function get_revisionData(
				$studentId, $taskSetId, $number) {
			$query = "SELECT * FROM `revisions` WHERE student_id=$studentId " .
					 "AND task_set_id=$taskSetId AND number=$number LIMIT 1;";
			$result = DB::query($query);
			if ($row = mysql_fetch_assoc($result)) {
				$revision = new RevisionData($row, False);
				return $revision;
			} else {
				return NULL;
			}
		}
		
		public static function get_all_revisions($studentId, $taskSets) {
			$query = "SELECT * FROM `revisions` WHERE student_id=$studentId ";
			$taskSetIds = array();
			foreach($taskSets as $taskSet) {
				$taskSetIds[] = $taskSet->getID();
			}
			$query .= " AND task_set_id IN (" . implode(",", $taskSetIds) . ")";
			$query .= " ORDER BY task_set_id, number ASC;";
			$result = DB::query($query);
			$revArray = array();
			foreach ($taskSets as $taskSet) {
				$revArray[$taskSet->getID()] = array();
			}
			while ($row = mysql_fetch_assoc($result)) {
				$revArray[$row['task_set_id']][$row['number']] = $row;
			}
			$query = "SELECT * FROM `points` WHERE student_id=$studentId AND task_set_id IN(" . implode(",", $taskSetIds) . ")";
			$query .= " ORDER BY task_set_id, revision ASC;";
			$result = DB::query($query);
			$retArray = array();
			foreach ($taskSets as $taskSet) {
				$retArray[$taskSet->getID()] = array();
			}
			$curTaskSet = NULL;
			$curPointSet = array();
			$curRevision = NULL;
			while ($row = mysql_fetch_assoc($result)) {
				if ($curTaskSet === NULL) {
					$curTaskSet = $taskSets[$row['task_set_id']];
				} else if ($curTaskSet->getID() != $row['task_set_id']) {
					$retArray[$curTaskSet->getID()][$curRevision] = new TaskSetPoints($studentId, $curTaskSet,
							$curRevision, $curPointSet, $revArray[$curTaskSet->getID()][$curRevision]);
					$curTaskSet = $taskSets[$row['task_set_id']];
					$curPointSet = array();
					$curRevision = NULL;
				}
				if ($curRevision === NULL) {
					$curRevision = $row['revision'];
				} else if ($curRevision != $row['revision']) {
					$retArray[$curTaskSet->getID()][$curRevision] = new TaskSetPoints($studentId, $curTaskSet,
							$curRevision, $curPointSet, $revArray[$curTaskSet->getID()][$curRevision]);
					$curRevision = $row['revision'];
					$curPointSet = array();
				}
				$curPointSet[] = $row;
			}
			if ($curTaskSet != NULL) {
				$retArray[$curTaskSet->getID()][$curRevision] = new TaskSetPoints($studentId, $curTaskSet,
							$curRevision, $curPointSet, $revArray[$curTaskSet->getID()][$curRevision]);
			}
			return $retArray;
		}
		
		// returns an array with student_id index containing arrays with task_set_id indexes
		// which hold the current revision number of this student and task set points
		public static function get_current_revisions($students, $taskSets) {
			if (count($students) == 0 || count($taskSets) == 0)
				return;
			$query = "SELECT * FROM revisions r1 JOIN (";
			$query .= "SELECT MAX(number) AS number, student_id, task_set_id FROM `revisions`";
			$studIds = array();
			foreach ($students as $student) {
				$studIds[] = $student->getID();
			}
			$query .= " WHERE student_id IN (" . implode(",", $studIds) . ")";
			$taskSetIds = array();
			foreach($taskSets as $taskSet) {
				$taskSetIds[] = $taskSet->getID();
			}
			$query .= " AND task_set_id IN (" . implode(",", $taskSetIds) . ")";
			$query .= " GROUP BY student_id, task_set_id) AS r2";
			$query .= " ON r1.number = r2.number AND r1.student_id = r2.student_id AND r1.task_set_id = r2.task_set_id";
			$query .= " ORDER BY r1.student_id, r1.task_set_id;";
			
			$result = DB::query($query);
			
			$retArray = array();
			foreach($students as $student) {
				$retArray[$student->getID()] = array();
				foreach ($taskSets as $taskSet) {
					$retArray[$student->getID()][$taskSet->getID()] = NULL;
				}
			}
			
			while ($row = mysql_fetch_assoc($result)) {
				$retArray[$row['student_id']][$row['task_set_id']] = $row;
			}
			
			return $retArray;
		}
		
		public static function get_users() {
			$query = "SELECT * FROM `users` ORDER BY id;";
			$result = DB::query($query);
			$retArray = array();
			while($row = mysql_fetch_assoc($result)) {
				$retArray[$row['id']] = $row;
			}
			return $retArray;
		}
	}
?>