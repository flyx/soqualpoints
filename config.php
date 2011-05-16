<?php
	require_once("db.php");

	$pagetitle = "Punktetabelle Softwarequalität";

	$mysql_user = "root";
	$mysql_pass = "root";
	$mysql_db = "sqp";
	$mysql_host = "localhost";

	DB::init($mysql_host, $mysql_user, $mysql_pass, $mysql_db, false);
	$enable_debug = False;
?>