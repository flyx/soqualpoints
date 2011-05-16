<?php
	require_once("db.php");
	
	// this class provides a list of points with arbitrary length
	class TaskSet extends DB {
		// store the number of tasks of the discussion and programming part of
		// the task set. Also store the maximal points reachable in the
		// programming task. (The maximal points for the discussion tasks is
		// equal to the number of discussion tasks.)
		
		private $maxProgrammingPoints;
		private $countProgrammingTasks;
		private $countDiscussionTasks;
		private $name;
		private $id;
		
		// if newEntry is true, the task set will be written as new entry
		// into the database. If newEntry is false, the row will be considered
		// a result from a db query
		function __construct($row, $newEntry = False) {			
			$this->name = $row['name'];
			$this->maxProgrammingPoints = (int) $row['max_prog_points'];
			$this->countProgrammingTasks = (int) $row['count_prog_tasks'];
			$this->countDiscussionTasks = (int) $row['count_disc_tasks'];
			if ($newEntry) {
				$this->id = DB::insert_taskSet($this->name,
						$this->maxProgrammingPoints,
						$this->countProgrammingTasks,
						$this->countDiscussionTasks);
			} else {
				$this->id = $row['id'];
			}
		}
		
		// updates the data fields of the task set
		function updateData($maxPoints, $countProg, $countDisc, $name) {
			global $soqualDB;
			
			$this->maxProgrammingPoints = (int) $maxPoints;
			$this->countProgrammingTasks = (int) $countProg;
			$this->countDiscussionTasks = (int) $countDisc;
			$this->name = $name;
			DB::update_taskSet($this->id, $this->name,
					$this->maxProgrammingPoints, $this->countProgrammingTasks,
					$this->countDiscussionTasks);
		}
		
		
		// getter for private variables
		
		function getMaxProgPoints() {
			return $this->maxProgrammingPoints;
		}
		
		function getNumProgTasks() {
			return $this->countProgrammingTasks;
		}
		
		function getNumDiscTasks() {
			return $this->countDiscussionTasks;
		}
		
		function getID() {
			return $this->id;
		}
		
		function getName() {
			return $this->name;
		}
	}
	
	// this class stores revision data for points
	class RevisionData extends DB {
		private $taskSetId;
		private $studentId;
		private $number;
		private $timestamp;
		private $userId;
		private $id;
		
		function __construct($row, $newEntry = False) {
			$this->taskSetId = $row['task_set_id'];
			$this->studentId = $row['student_id'];
			$this->number = $row['number'];
			$this->timestamp = $row['timestamp'];
			$this->userId = $row['user_id'];
			if ($newEntry) {
				$this->id = DB::add_revisionData($this->number, $this->studentId,
						$this->taskSetId, $this->userId, $this->timestamp);
			} else {
				$this->id = $row['id'];
			}
		}
		
		function getTaskSetId() {
			return $this->taskSetId;
		}
		
		function getStudentId() {
			return $this->studentId;
		}
		
		function getNumber() {
			return $this->number;
		}
		
		function getUserId() {
			return $this->userId;
		}
		
		function getTimestamp() {
			return $this->timestamp;
		}
		
		function getID() {
			return $this->id;
		}
	}
	
	
	// this class stores the points for all the tasks in one task set
	class TaskSetPoints extends DB {
		// Student id
		private $studentId;
		// TaskSet object
		private $taskSet;
		// array of points for discussion tasks
		private $discPoints;
		private $discPointIds;
		// array of points for programming tasks
		private $progPoints;
		private $progPointIds;
		// revision data of the loaded points
		private $revision;
		
		// constructor: set the TaskSet and the User for this object
		function __construct($myStudentId, $myTaskSet, $myRevision, $rows = NULL, $revData = NULL) {
			$this->studentId = $myStudentId;
			$this->taskSet = $myTaskSet;
			if ($myRevision >= 0) {
				if ($revData === NULL) {
					$this->revision = DB::get_revisionData($this->studentId,
							$this->taskSet->getID(), $myRevision);
				} else {
					$this->revision = new RevisionData($revData, False);
				}
			} else {
				$this->revision = new RevisionData(array('id' => -1, 'task_set_id' => $myTaskSet->getID(),
						'student_id' => $myStudentId, 'number' => 0, 'timestamp' => "", 'user_id' => -1));
			}
			if ($rows === NULL) {
				// construct object from database
				$this->fetchFromDatabase();
			} else {
				// database rows given, fill points with row data
				foreach ($rows as $row) {
					// only store points with the current revision
					if ($row['revision'] == $myRevision) {
						if ($row['kind'] == "disc") {
							$this->discPoints[$row['index']] = $row['points'];
							$this->discPointIds[$row['index']] = $row['id'];
						} else {
							$this->progPoints[$row['index']] = $row['points'];
							$this->progPointIds[$row['index']] = $row['id'];
						}
					} else {
						debugLog('TaskSetPoints', 'dumped data because revision number (' .
								$row['revision'] . ') didn\'t match current revision (' .
								$myRevision . ')');
					}
				}
			}
		}
		
		// read the data stored in the database for this object.
		function fetchFromDatabase() {
			if (isset($this->revision)) {
				$pointArray = DB::get_points(NULL, $this->studentId,
						$this->taskSet->getID(), $this->revision->getNumber());
				foreach ($pointArray as $row) {
					if ($row['kind'] == "disc") {
						$this->discPoints[$row['index']] = $row['points'];
						$this->discPointIds[$row['index']] = $row['id'];
					} else {
						$this->progPoints[$row['index']] = $row['points'];
						$this->progPointIds[$row['index']] = $row['id'];
					}
				}
			}
		}
		
		// write the currently stored point values to the database
		// YOU NEED TO INCREASE THE REVISION NUMBER MANUALLY BEFORE CALLING
		// THIS FUNCTION
		private function writeToDatabase() {
			
			for ($i = 0; $i < $this->taskSet->getNumDiscTasks(); $i++) {
				if (isset($this->discPoints[$i])) {
					$this->discPointIds[$i] = DB::add_points($this->studentId,
							$this->taskSet->getID(), "disc", $i,
							$this->discPoints[$i], $this->revision->getNumber());
				}
			}
			for ($i = 0; $i < $this->taskSet->getNumProgTasks(); $i++) {
				if (isset($this->progPoints[$i])) {
					$this->progPointIds[$i] = DB::add_points($this->studentId,
							$this->taskSet->getID(), "prog", $i,
							$this->progPoints[$i], $this->revision->getNumber());
				}
			}
		}
		
		// getter and setter for points
		
		function getDiscPoints($index) {
			if (isset($this->discPoints[$index])) {
				return $this->discPoints[$index];
			} else {
				return NULL;
			}
		}
		
		function setDiscPoints($index, $value) {
			if($value !== NULL) {
				$this->discPoints[$index] = $value;
			} else {
				unset($this->discPoints[$index]);
			}
		}
		
		function getProgPoints($index) {
			if (isset($this->progPoints[$index])) {
				return $this->progPoints[$index];
			} else {
				return NULL;
			}
		}
		
		function setProgPoints($index, $value) {
			if ($value !== NULL) {
				$this->progPoints[$index] = $value;
			} else {
				unset($this->progPoints[$index]);
			}
		}
		
		// test if this object has any points for discussion / programming tasks
		
		function hasProgPoints() {
			for ($i = 0; $i < $this->taskSet->getNumProgTasks(); $i++) {
				if (isset($this->progPoints[$i])) {
					return True;
				}
			}
			return False;
		}
		
		function hasDiscPoints() {
			for ($i = 0; $i < $this->taskSet->getNumDiscTasks(); $i++) {
				if (isset($this->discPoints[$i])) {
					return True;
				}
			}
			return False;
		}
		
		function countDiscPoints() {
			$result = 0;
			for ($i = 0; $i < $this->taskSet->getNumDiscTasks(); $i++) {
				$result += $this->discPoints[$i];
			}
			return $result;
		}
		
		function countProgPoints() {
			$result = 0;
			for ($i = 0; $i < $this->taskSet->getNumProgTasks(); $i++) {
				$result += $this->progPoints[$i];
			}
			return $result;
		}
		
		function getRevisionData() {
			return $this->revision;
		}
		
		function storeNewRevision($newNumber, $userId) {
			$row = array("number" => $newNumber,
						 "student_id" => $this->studentId,
						 "task_set_id" => $this->taskSet->getID(),
						 "user_id" => $userId);
			$this->revision = new RevisionData($row, True);
			$this->writeToDatabase();
		}
	}
	
	class Student extends DB {
		// student data
		private $id;
		private $forename;
		private $surname;
		private $studentNumber;
		private $teamNumber;
		// points stored in an array of TaskSetPoints indexed by the TaskSet IDs
		private $points;
		
		// if newEntry is true, the student will be written as new entry
		// into the database. If newEntry is false, the row will be considered
		// a result from a db query
		function __construct($row, $newEntry = False) {
			global $soqualDB;
			
			if (DB::isEncrypted() && !isset($row['forename'])) {
				// row came from encrypted database, decrypted strings have
				// different names.
				$this->forename = $row['plain_forename'];
				$this->surname = $row['plain_surname'];
				$this->studentNumber = (int)$row['plain_studnum'];
			} else {
				$this->forename = $row['forename'];
				$this->surname = $row['surname'];
				$this->studentNumber = (int)$row['student_number'];
			}
			$this->teamNumber = (int)$row['team_number'];
			if ($newEntry) {
				$this->id = DB::insert_student($this->forename, $this->surname,
						$this->studentNumber, $this->teamNumber);
			} else {
				$this->id = $row['id'];
			}
		}
		
		// getter for the student data
		
		function getID() {return $this->id;}
		function getForename() {return $this->forename;}
		function getSurname() {return $this->surname;}
		function getStudNum() {return $this->studentNumber;}
		function getTeamNum() {return $this->teamNumber;}
		function getPoints($taskSet) {return $this->points[$taskSet->getID()];}
		
		// setter for everything at once (immediately writes everything to the db)
		
		function updateData($newForename, $newSurname, $newStudNum, $newTeamNum) {
			global $soqualDB;
			
			$this->forename = $newForename;
			$this->surname = $newSurname;
			$this->studentNumber = $newStudNum;
			$this->teamNumber = $newTeamNum;
			DB::update_student($this->id, $this->forename, $this->surname,
					$this->studentNumber, $this->teamNumber);
		}
		
		function loadPoints($taskSet, $rows = NULL, $revNum = NULL, $revData = NULL) {
			if ($revNum === NULL) {
				$revNum = DB::recentRevisionNumber($this->id, $taskSet->getID());
			}
			$this->points[$taskSet->getID()] = new TaskSetPoints($this->id,
					$taskSet, $revNum, $rows, $revData);
		}
	}
?>