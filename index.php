<?php
	// session is used for the filter parameters and login
	session_start();

	require_once('debuglog.php');
	
	// check if this site has been configured yet
	if ($handle = @fopen ('config.php', "rb")) {
		require_once('config.php');
		require_once('backend.php');
		require_once('user.php');
	
	// checks if a var is set, returns its value if yes, returns NULL otherwise
	// used for GET parameters
	function checkParam($getParam) {
		if (isset($_GET[$getParam])) {
			return $_GET[$getParam];
		} else {
			return NULL;
		}
	}
	
	// ensure valid page has been requested
	$page = $_GET['page'];
	if ($page != 'points' and $page != 'students' and $page != 'tasksets'
			and $page != 'revisions' and $page != 'admin' and
			$page != 'csvimport' and $page != 'csvexport' and
			$page != 'help') {
		$page = 'points';
	}
	
	$user = getUser();
	
	// handle login, logout and new user requests here.
	// forward all other action requests to the actions handler
	
	$action = checkParam('action');
	if ($action == 'login') {
		loginUser($_POST['username'], $_POST['password']);
		$user = getUser();
		if ($user['rights']) {
			$message = 'Login erfolgreich';
		} else {
			$message = 'Login fehlgeschlagen';
		}
	} else if ($action == 'logout') {
		logoutUser();
		$user = getUser();
		$message = "Logout erfolgreich";
	} else if ($action == 'request') {
		$un = $_POST['username'];
		$pw = $_POST['password'];
		if ($un != "" and $pw != "") {
			if (requestUser($un, $pw)) {
				$message = "Neuen Benutzer beantragt, bitte auf Freigabe " .
						"durch einen Administrator warten.";
			} else {
				$message = "Dieser Benutzer existiert bereits.";
			}
		} else {
			$message = "Benutzername und Passwort dürfen nicht leer sein.";
		}
	} else if ($action != "" and $user['rights'] > 1) {
		require_once('actions.php');
		$confirmForm = doAction($action);
	}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<title><?php echo $pagetitle; ?></title>
	
	<link href="style.css" rel="stylesheet" type="text/css" />
</head>

<body<?php if ($enable_debug) echo ' style="margin-bottom: 1.6em"'?>>
	<div id="control">
		<h1 id="heading"><?php echo $pagetitle; ?></h1>

		<table><tr>
			<td<?php if (isset($message)) echo '  id="message">' . $message; else echo ">"; ?></td>
			<td id="userinfo">
<?php if ($user['rights'] > 0) { ?>
				<span id="userspan">eingeloggt als <strong><?php echo $user['name']; ?></strong></span>
				<a href=<?php echo "\"?page=$page&amp;action=logout\""; ?> class="roundButton">logout</a>
<?php } ?>
			</td>
		</tr></table>
	

		

		
		<div id="tabs">
<?php
	function makeTab($link, $caption) {
		global $page;
		
		if ($page == $link) {
?>
		<span class="tab"><?php echo $caption; ?></span>
<?php
		} else {
?>
		<a class="tab" href="?page=<?php echo $link;?>"><?php echo $caption; ?></a>	
<?php
		}
	}
	if ($user['rights'] > 0) {
		makeTab('points', 'Punkte');
		makeTab('students', 'Studenten');
		makeTab('tasksets', 'Aufgabenblätter');
		makeTab('revisions', 'Revisionen');
	}
	if ($user['rights'] >= 3) {
		makeTab('admin', 'Admin');
	}
	if ($user['rights'] > 0) {
		makeTab('help', 'Hilfe');
	}
?>
		</div>
		
	</div>
<?php
	if (isset($confirmForm) && $confirmForm != "") {
		echo $confirmForm;
	} else if ($user['rights'] > 0) {
		$path="./mod/" . $page . ".php";
		if ($handle = @fopen ($path, "rb")) {
			require_once($path);
		} else {
			echo "<center><strong>404 - Page not found:</strong> $path</center>\n";
		}
	} else {?>
		<div id="fadingcontent" style="height: 8em; padding: 1em;">
			<form action=<?php echo "\"?page=$page&amp;action=login\"" ?> method="post">
				<strong>Login</strong><br />
				<span class="formlabel">Name:</span>
				<span class="formfield"><input name="username" type="text" size="10" /></span>
				<span class="formlabel">Passwort:</span>
				<span class="formfield"><input name="password" type="password" size="15" /></span>
				<span class="formsubmit"><input type="submit" value="Anmelden" /></span>
			</form>
			<br />
			<form action=<?php echo "\"?page=$page&amp;action=request\"" ?> method="post">
				<strong>Neuen Benutzer beantragen</strong><br />
				<span class="formlabel">Name:</span>
				<span class="formfield"><input name="username" type="text" size="10" /></span>
				<span class="formlabel">Passwort:</span>
				<span class="formfield"><input name="password" type="password" size="15" /></span>
				<span class="formsubmit"><input type="submit" value="Beantragen" /></span>
			</form>
		</div>
<?php 
	}
	if ($enable_debug) {
		debugOutput('Debug output');	
	 } ?>
</body>

</html>
<?php } else {
		require_once("setup.php");
	}
?>