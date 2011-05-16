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
	<div style="position: fixed; bottom: 0; left: 0; right:0; max-height: 250px; overflow: auto;">
	<table style="border: 0px; font-family: 'Courier New', Courier, monospace; border-collapse: collapse; margin:0px; font-size: small; width: 100%;">
		<tr>
			<th colspan="2" style="background: #bbb; border: 0px;"><?php echo $title; ?></th>
		</tr>
<?php
		foreach($debugLog as $debugLine) {
			echo "\t\t<tr style=\"background: ";
			if ($debugLine['error']) {
				echo $varying_bg ? "#e73" : "#e95";
			} else {
				echo $varying_bg ? "#7e3" : "#9e5";
			}
			echo ";\">\n";
			echo "\t\t\t<td style=\"font-weight: bold; border: 0px; padding-right: 5px;\">" . $debugLine['module'] . "</td>\n";
			echo "\t\t\t<td style=\"border: 0px;\">" . $debugLine['text'] . "</td>\n";
			echo "\t\t</tr>\n";
			$varying_bg = !$varying_bg;
		}
?>	
	</table></div>
<?php } ?> 