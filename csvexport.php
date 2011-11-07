<?php
	// used for login
	session_start();

	require_once("config.php");
	require_once("backend.php");
	require_once("db.php");
	require_once("user.php");

$user = getUser();
if ($user['rights'] < 3) {
	header('Content-type: text/plain');
	
	echo "Diese User hat keine Administrationsberechtigung.";
} else {
	header('Content-type: application/x-csv');
	header('Content-Disposition: attachment; filename="export_' .
			date('Y-m-d--H-i') . '.csv"');
	
	$taskSets = DB::get_tasks(NULL, NULL, NULL, NULL, NULL);
	$students = DB::get_students(NULL, NULL, NULL, NULL, NULL);
	
	$rows = array();
	
	function toCsv($string) {
		return "\"" . str_replace("\"", "\\" . "\"", $string) . "\"";
	}
	
	foreach($students as $student) {
		$row = array();
		$row[] = $student->getTeamNum();
		$row[] = toCsv($student->getSurname());
		$row[] = toCsv($student->getForename());
		$row[] = $student->getStudNum();
		
		foreach($taskSets as $taskSet) {
			$student->loadPoints($taskSet);
			$points = $student->getPoints($taskSet);
			for ($i = 0; $i < $taskSet->getNumDiscTasks(); $i++) {
				$row[] = $points->getDiscPoints($i);
			}
			for ($i = 0; $i < $taskSet->getNumProgTasks(); $i++) {
				$s = (string)$points->getProgPoints($i);
				if (strpos($s, ".") !== false or strpos($s, ",") !== false) {
					$s = "\"" . strtr($s, ".", ",") . "\"";
				}
				$row[] = $s;
			}
		}
		$rows[] = implode(",", $row);
	}
	
	echo implode("\n", $rows);
}
?>