<?php		
	require_once("db.php");
	
	function userLoginRow($username, $passwordhash) {
		
		$query = 'SELECT id, rights';
		if (DB::isEncrypted()) {
			$query .= ', AES_DECRYPT(mpw, "' .
					mysql_real_escape_string($_SESSION['password']). '") AS plain_mpw';
		}
		$query .= ' FROM `users` WHERE name="' .
				 mysql_real_escape_string($username) . '" AND password="' .
				 mysql_real_escape_string($passwordhash) . '" LIMIT 1;';
		$result = DB::query($query);
		
		if (mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			return $row;
		} else {
			return array("id" => -1);
		}
	}
	
	function userExists($username) {
		
		$query = 'SELECT * FROM `users` WHERE name="' .
				 mysql_real_escape_string($username) . '" LIMIT 1;';
		$result = DB::query($query);
		
		return (mysql_num_rows($result) > 0);
	}
	
	function getUser() {
		$user['name'] = $_SESSION['username'];
		$user['password'] = $_SESSION['password'];
		debugLog("user", "retrieving session data: username=" .
				htmlspecialchars($user['name'], ENT_QUOTES, "UTF-8") . 
				", password=" . $user['password']);
		
		$row = userLoginRow($user['name'], hash("sha256", $user['password'], False));
		$user['id'] = $row['id'];
		$user['displayname'] = htmlspecialchars($user['name'], ENT_QUOTES, "UTF-8");
		if ($user['id'] >= 0) {
			$user['rights'] = $row['rights'];
			if (DB::isEncrypted()) {
				DB::setMasterPassword($row['plain_mpw']);
			}
		} else {
			$user['rights'] = 0;
		}
		return $user;
	}
	
	function hashPW($username, $password) {
		$temp = strrev($username) . $password . $username;
		return hash("sha256", $temp, False);
	}
	
	function loginUser($username, $password) {
		debugLog("user", "logging in...");
	
		$pwHash = hashPW($username, $password);
		$_SESSION['username'] = $username;
		$_SESSION['password'] = $pwHash;
	}
	
	function logoutUser() {
		unset($_SESSION['username']);
		unset($_SESSION['password']);
	}	
	
	function createUser($username, $password, $adminId, $rights) {
		
		$un = mysql_real_escape_string($username);
		$pwHash = hashPW($username, $password);
		$pw = mysql_real_escape_string(hash("sha256", $pwHash, False));
		
		if (!userExists($un)) {
			if (DB::isEncrypted()) {
				$query = "SELECT mpw FROM `users` WHERE id=$adminId";
				$result = DB::query($query);
				if ($row = mysql_fetch_assoc($result)) {
					$query = "SELECT AES_DECRYPT(\"" . mysql_real_escape_string($row['mpw']) . "\", \"" .
							mysql_real_escape_string($_SESSION['password']) . "\") AS mpw";
					$result = DB::query($query);
					$row = mysql_fetch_assoc($result);
					$mpw = mysql_real_escape_string($row['mpw']);
				
					$query = "INSERT INTO `users` (name, password, rights, mpw) " .
							"VALUES ('$un', '$pw', $rights, AES_ENCRYPT(\"$mpw\", \"" .
							mysql_real_escape_string($pwHash) . "\"))";
					DB::query($query);
					return true;
				}
			} else {
				$query = "INSERT INTO `users` (name, password, rights) VALUES" .
						" ('$un', '$pw', $rights)";
				DB::query($query);
				return true;
			}
		}
		
		return false;
	}
	
	function updateUserPw($userId, $newPW, $adminId) {
		$query = "SELECT name FROM `users` WHERE id=$userId";
		$result = DB::query($query);
		if ($row = mysql_fetch_assoc($result)) {
			$un = $row['name'];
			$pwHash = hashPW($un, $newPW);
			$pw = mysql_real_escape_string(hash("sha256", $pwHash, False));
			
			if (DB::isEncrypted()) {
				$query = "SELECT mpw FROM `users` WHERE id=$adminId";
				$result = DB::query($query);
				if ($row = mysql_fetch_assoc($result)) {
					$query = "SELECT AES_DECRYPT(\"" . mysql_real_escape_string($row['mpw']) . "\", \"" .
							mysql_real_escape_string($_SESSION['password']) . "\") AS mpw";
					$result = DB::query($query);
					$row = mysql_fetch_assoc($result);
					$mpw = mysql_real_escape_string($row['mpw']);
					
					$query = "UPDATE `users` SET password=\"$pw\", mpw=AES_ENCRYPT(" .
							"\"$mpw\", \"" . mysql_real_escape_string($pwHash) . "\")" .
							" WHERE id=$userId";
					DB::query($query);
					return true;
				}
			} else {
				$query = "UPDATE `users` SET password=\"$pw\" WHERE id=$userId";
				DB::query($query);
				return true;
			}
		}
		return false;
	}
	
	function updateUserRights($userId, $rights) {
		$query = "SELECT rights FROM `users` WHERE id=$userId";
		$result = DB::query($query);
		if ($row = mysql_fetch_assoc($result)) {
			if ((int)$row['rights'] != $rights) {
				$query = "UPDATE `users` SET rights=$rights WHERE id=$userId";
				DB::query($query);
				return true;
			}
		}
		return false;
	}		
	
	function getUserName($id) {
		$query = "SELECT name FROM `users` WHERE id=$id LIMIT 1";
		$result = DB::query($query);
		if ($row = mysql_fetch_assoc($result)) {
			return $row['name'];
		} else {
			return "Unbekannt";
		}
	}
	
	function requestUser($username, $password) {
		$un = mysql_real_escape_string($username);
		$pwHash = hashPW($username, $password);
		
		if (!userExists($un)) {
			$query = "INSERT INTO `users` (name, password, rights) VALUES" .
						" ('$un', '$pwHash', 0)";
			DB::query($query);
			return true;
		}
		
		return false;
	}
	
	function approveUser($id, $adminId) {
		$query = "SELECT rights, password FROM `users` WHERE id=$id LIMIT 1";
		$result = DB::query($query);
		if ($row = mysql_fetch_assoc($result)) {
			if ($row['rights'] > 0) {
				return false;
			}
			
			$pwHash = $row['password'];
			$pw = mysql_real_escape_string(hash("sha256", $pwHash, False));
			
			if (DB::isEncrypted()) {
				$query = "SELECT mpw FROM `users` WHERE id=$adminId";
				$result = DB::query($query);
				if ($row = mysql_fetch_assoc($result)) {
					$query = "SELECT AES_DECRYPT(\"" . mysql_real_escape_string($row['mpw']) . "\", \"" .
							mysql_real_escape_string($_SESSION['password']) . "\") AS mpw";
					$result = DB::query($query);
					$row = mysql_fetch_assoc($result);
					$mpw = mysql_real_escape_string($row['mpw']);
				
					$query = "UPDATE `users` SET password='$pw', rights=1, " . 
							"mpw=AES_ENCRYPT(\"$mpw\", \"" .
							mysql_real_escape_string($pwHash) . "\") WHERE id=$id";
					DB::query($query);
					return true;
				}
			} else {
				$query = "UPDATE `users` SET password='$pw', rights=1 WHERE id=$id";
				DB::query($query);
				return true;
			}
		}
		
		return false;
	}
?>