<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<title>Setup Punktetabelle Softwarequalität</title>
	
	<link href="style.css" rel="stylesheet" type="text/css" />
</head>

<body>
	<h2 style="text-align: center;">Einrichtung der Punktetabelle</h2>
	<?php
		if (!extension_loaded("mysql")) {
			echo "<p style=\"text-align: center;\">Diese Software benötigt die MySQL-Extension für PHP, welche auf diesem System nicht verfügbar ist.</p>";
			die("</body></html>");
		}
		if (file_exists('config.php')) {
			echo '<p style="text-align: center;">Fehler: Datei config.php existiert bereits. Lösche die Datei, um eine neue Datenbank anzulegen.</p>';
			die("</body></html>");
		}
		if (isset($_POST['setconfig'])) {
			$file = @fopen('config.php', 'wb');
			if ($file === FALSE) {
				echo 'Fehler: Datei config.php konnte nicht geschrieben werden.';
			} else {
				$mysql_user = $_POST['sql_user'];
				$mysql_pass = $_POST['sql_pass'];
				$mysql_db = $_POST['sql_db'];
				$mysql_host = $_POST['sql_host'];
				
				fwrite($file, "<?php\n\t" . 'require_once("db.php");' . "\n\n");
				fwrite($file, "\t" . '$pagetitle = "' . $_POST['pagetitle'] . '";' . "\n\n");
				fwrite($file, "\t" . '$mysql_user = "' . $_POST['sql_user'] . '";' ."\n");
				fwrite($file, "\t" . '$mysql_pass = "' . $_POST['sql_pass'] . '";' . "\n");
				fwrite($file, "\t" . '$mysql_db = "' . $_POST['sql_db'] . '";' . "\n");
				fwrite($file, "\t" . '$mysql_host = "' . $_POST['sql_host'] . '";' . "\n");
				fwrite($file, "\n\t" . 'DB::init($mysql_host, $mysql_user, $mysql_pass, $mysql_db, ');
				require_once('db.php');
				require_once('user.php');
				
				function createDataStructure() {
					DB::query('SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";');
					DB::query('DROP TABLE IF EXISTS `points`;');
					DB::query('CREATE TABLE `points` (
							   `id` int(10) unsigned NOT NULL auto_increment,
							   `student_id` int(10) unsigned NOT NULL,
							   `task_set_id` int(10) unsigned NOT NULL,
							   `kind` char(4) NOT NULL,
							   `index` int(10) unsigned NOT NULL,
							   `points` float unsigned NOT NULL,
							   `revision` int(10) unsigned NOT NULL,
							   PRIMARY KEY  (`id`)
							   ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;');
					DB::query('DROP TABLE IF EXISTS `revisions`;');
					DB::query('CREATE TABLE IF NOT EXISTS `revisions` (
							   `id` int(10) unsigned NOT NULL auto_increment,
							   `number` int(10) unsigned NOT NULL,
							   `student_id` int(10) unsigned NOT NULL,
							   `task_set_id` int(10) unsigned NOT NULL,
							   `user_id` int(10) unsigned NOT NULL,
							   `timestamp` datetime NOT NULL,
							   PRIMARY KEY  (`id`)
							   ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;');
					DB::query('DROP TABLE IF EXISTS `students`;');
					DB::query('CREATE TABLE IF NOT EXISTS `students` (
							   `id` int(10) unsigned NOT NULL auto_increment,
							   `forename` blob NOT NULL,
							   `surname` blob NOT NULL,
							   `student_number` blob NOT NULL,
							   `team_number` int(10) unsigned NOT NULL,
							   PRIMARY KEY  (`id`)
							   ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;');
					DB::query('DROP TABLE IF EXISTS `task_sets`;');
					DB::query('CREATE TABLE IF NOT EXISTS `task_sets` (
							   `id` int(10) unsigned NOT NULL auto_increment,
							   `name` varchar(50) character set latin1 collate latin1_german1_ci NOT NULL,
							   `max_prog_points` int(10) unsigned NOT NULL,
							   `count_prog_tasks` int(10) unsigned NOT NULL,
							   `count_disc_tasks` int(10) unsigned NOT NULL,
							   PRIMARY KEY  (`id`)
							   ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;');
					DB::query('DROP TABLE IF EXISTS `users`;');
					DB::query('CREATE TABLE IF NOT EXISTS `users` (
							  `id` int(10) unsigned NOT NULL auto_increment,
							  `name` text character set latin1 collate latin1_german1_ci NOT NULL,
							  `password` text character set latin1 collate latin1_german1_ci NOT NULL,
							  `rights` int(10) unsigned NOT NULL,
							  `mpw` blob NOT NULL,
							  PRIMARY KEY  (`id`)
							  ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;');
				}
				if (isset($_POST['sql_encrypt'])) {
					fwrite($file, 'true);' . "\n");
					$enable_debug = True;
					DB::init($mysql_host, $mysql_user, $mysql_pass, $mysql_db, true);
					$enable_debug = False;
					
					if (!DB::isAvailable()) {
						fclose($file);
						unlink('config.php');
						echo '<p style="text-align: center;">Fehler beim Zugriff auf die Datenbank, siehe Log für Details.</p>';
						debugOutput('Debug output (um dies dauerhaft zu aktivieren, in config.php \$enable_debug auf True setzen)');
						die ('</body></html>');
					}
					
					$un = mysql_real_escape_string($_POST['root_name']);
					$pwHash = hashPW($_POST['root_name'], $_POST['root_pass']);
					$pw = mysql_real_escape_string(hash("sha256", $pwHash, False));
					
					mt_srand();
					$num = mt_rand(10, 10042);
					$curHash = mt_rand();
					for ($i = 0; $i < $num; $i++) {
						$curHash = hash("sha256", $curHash, False);
					}
					$mpw = $curHash;
					
					$query = "INSERT INTO `users` (name, password, rights, mpw) " .
							"VALUES ('$un', '$pw', 3, AES_ENCRYPT(\"$mpw\", \"" .
							mysql_real_escape_string($pwHash) . "\"))";
					DB::setMasterPassword($mpw);
				} else {
					fwrite($file, 'false);' . "\n");
					$enable_debug = True;
					DB::init($mysql_host, $mysql_user, $mysql_pass, $mysql_db, false);
					$enable_debug = False;
					
					if (!DB::isAvailable()) {
						fclose($file);
						unlink('config.php');
						echo '<p style="text-align: center;">Fehler beim Zugriff auf die Datenbank, siehe Log für Details.</p>';
						debugOutput('Debug output (um dies dauerhaft zu aktivieren, in config.php \$enable_debug auf True setzen)');
						die ('</body></html>');
					}
					
					$un = mysql_real_escape_string($_POST['root_name']);
					$pwHash = hashPW($_POST['root_name'], $_POST['root_pass']);
					$pw = mysql_real_escape_string(hash("sha256", $pwHash, False));
					
					$query = "INSERT INTO `users` (name, password, rights) VALUES" .
						" ('$un', '$pw', 3)";
				}
				fwrite($file, "\t" . '$enable_debug = False;' . "\n?>");
				
				fclose($file);
				
				$enable_debug = True;
				createDataStructure();
				$enable_debug = False;
				DB::query($query);
				
				debugOutput('Debug output (um dies dauerhaft zu aktivieren, in config.php \$enable_debug auf True setzen)');
				
				echo '<p style="text-align: center">Punktetabelle wurde erfolgreich eingerichtet. <a href="' .
						'index.php" style="color: blue">Zur Punktetabelle</a>';
			}
		} else {
	?>
	<form action="" method="post">
	<table id="contenttable">
		<tr>
			<th colspan="2" class="lightbg">Allgemeine Einstellungen</th>
		</tr>
		<tr>
			<td class="formlabel">Seitentitel:</td>
			<td>
				<input name="pagetitle" type="text" size="50" value="Punktetabelle Softwarequalität" />
			</td>
		</tr>
		<tr>
			<th colspan="2" class="lightbg">
				MySQL-Server Login-Daten:
			</th>
		</tr>
		<tr>
			<td class="formlabel">User:</td>
			<td><input name="sql_user" type="text" size="20" /></td>
		</tr>
		<tr>
			<td class="formlabel">Passwort:</td>
			<td><input name="sql_pass" type="password" size="20" /></td>
		</tr>
		<tr>
			<td class="formlabel">Datenbankname:</td>
			<td><input name="sql_db" type="text" size="20" /></td>
		</tr>
		<tr>
			<td class="formlabel">Host:</td>
			<td><input name="sql_host" type="text" size="20" /></td>
		</tr>
		<tr>
			<td colspan="2"><input name="sql_encrypt" type="checkbox" />Datenbank verschlüsseln</td>
		</tr>
		<tr>
			<th colspan="2" class="lightbg">
				Root-Benutzer der Punktetabelle
			</th>
		</tr>
		<tr>
			<td class="formlabel">Name:</td>
			<td><input name="root_name" type="text" size="20" /></td>
		</tr>
		<tr>
			<td class="formlabel">Passwort:</td>
			<td><input name="root_pass" type="password" size="20" /></td>
		</tr>
	</table>
	<p style="text-align: center"><input type="submit" name="setconfig" /><br /><br /><strong>Achtung: Ist bereits eine Datenbank vorhanden, wird diese gelöscht!</strong></p>
	</form>
	<?php } ?>
</body>

</html>