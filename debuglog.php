<?php
	// debugging output
	$debugLog = array();
	function debugLog($module, $text, $error = False) {
		global $debugLog;
		global $enable_debug;
		if ($enable_debug) {
			$debugLog[] = array('module' => $module, 'text' => $text, 'error' => $error);
		}
	}
	
	function debugOutput($title) {
		global $debugLog;
		$varying_bg = False;
?>
	<div id="debug">
	<div id="dbheader">
		<p><?php echo $title; ?></p>
	</div>
	<table>
<?php
		foreach($debugLog as $debugLine) {
			echo "\t\t<tr style=\"background: ";
			if ($debugLine['error']) {
				echo $varying_bg ? "#e73" : "#e95";
			} else {
				echo $varying_bg ? "#7e3" : "#9e5";
			}
			echo ";\">\n";
			echo "\t\t\t<td>" . $debugLine['module'] . "</td>\n";
			echo "\t\t\t<td>" . $debugLine['text'] . "</td>\n";
			echo "\t\t</tr>\n";
			$varying_bg = !$varying_bg;
		}
?>	
	</table></div>
<?php } ?>