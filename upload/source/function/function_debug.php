/*<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_debug.php 24200 2011-08-31 02:13:03Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function debugmessage($ajax = 0) {
	$sqldebug = '';
	$n = 0;
	$sqlw = array();
	$db = & DB::object();
	$queries = count($db->sqldebug);
	foreach ($db->sqldebug as $string) {
		$n++;
		$sqldebug .= '<span style="cursor:pointer" onclick="document.getElementById(\'sql_'.$n.'\').style.display = document.getElementById(\'sql_'.$n.'\').style.display == \'\' ? \'none\' : \'\'">&bull; '.$string[1].'s &bull; '.nl2br(htmlspecialchars($string[0])).'</span><br />';
		$sqldebug .= '<div id="sql_'.$n.'" style="display:none">';
		if(preg_match('/^SELECT /', $string[0])) {
			$query = DB::query("EXPLAIN ".$string[0]);
			$i = 0;
			$sqldebug .= '<table>';
			while($row = DB::fetch($query)) {
				if(!$i) {
					$sqldebug .= '<tr style="border-bottom:1px dotted gray"><td>&nbsp;'.implode('&nbsp;</td><td>&nbsp;', array_keys($row)).'&nbsp;</td></tr>';
					$i++;
				}
				if(strexists($row['Extra'], 'Using filesort')) {
					$sqlw['Using filesort']++;
					$row['Extra'] = str_replace('Using filesort', '<font color=red>Using filesort</font>', $row['Extra']);
				}
				if(strexists($row['Extra'], 'Using temporary')) {
					$sqlw['Using temporary']++;
					$row['Extra'] = str_replace('Using temporary', '<font color=red>Using temporary</font>', $row['Extra']);
				}
				$sqldebug .= '<tr><td>&nbsp;'.implode('&nbsp;</td><td>&nbsp;', $row).'&nbsp;</td></tr>';
			}
			$sqldebug .= '</table>';
		}
		$sqldebug .= '<table><tr style="border-bottom:1px dotted gray"><td width="270">File</td><td width="80">Line</td><td>Function</td></tr>';
		foreach($string[2] as $error) {
			$error['file'] = str_replace(DISCUZ_ROOT, '', $error['file']);
			$error['class'] = isset($error['class']) ? $error['class'] : '';
			$error['type'] = isset($error['type']) ? $error['type'] : '';
			$error['function'] = isset($error['function']) ? $error['function'] : '';
			$sqldebug .= "<tr><td>$error[file]</td><td>$error[line]</td><td>$error[class]$error[type]$error[function]()</td></tr>";
		}
		$sqldebug .= '</table></div>';
	}
	echo '<div class="container" style="border-top:1px black solid">'.$sqldebug.'</div>';
}

?>